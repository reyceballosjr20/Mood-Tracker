<?php
require_once 'User.php';

class Auth {
    private $user;
    
    public function __construct() {
        $this->user = new User();
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Register a new user
     * 
     * @param array $userData User data from registration form
     * @param string $provider Authentication provider
     * @return array Result of registration
     */
    public function register($userData, $provider = 'local') {
        // For local registration, validate the input
        if ($provider === 'local') {
            $validationErrors = $this->user->validateInput($userData);
            
            if (!empty($validationErrors)) {
                return [
                    'status' => 'error',
                    'errors' => $validationErrors
                ];
            }
        }
        
        // Split name into first and last name
        $nameParts = explode(' ', $userData['name'], 2);
        $firstName = $nameParts[0];
        $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
        
        // For social providers, generate a random secure password
        // Users won't need this password since they'll authenticate via the provider
        if ($provider !== 'local' && (!isset($userData['password']) || empty($userData['password']))) {
            $userData['password'] = bin2hex(random_bytes(16)); // Generate secure random string
        }
        
        // Register user
        $result = $this->user->register(
            $firstName,
            $lastName,
            $userData['email'],
            $userData['password'] ?? '',
            $provider
        );
        
        // If registration is successful, log the user in
        if ($result['status'] === 'success') {
            $this->loginAfterRegistration(
                $result['user_id'],
                $firstName,
                $lastName,
                $userData['email'],
                $provider
            );
            
            // If it's a redirect after successful registration,
            // this will help identify the user is newly registered
            $_SESSION['just_registered'] = true;
        }
        
        return $result;
    }
    
    /**
     * Get user ID by email
     * 
     * @param string $email User email
     * @return int|false User ID or false if not found
     */
    private function getUserIdByEmail($email) {
        $sql = "SELECT id FROM users WHERE email = :email";
        $stmt = $this->user->db->conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['id'] : false;
    }
    
    /**
     * Handle social login (Google, etc.)
     * 
     * @param array $userData User data from social provider
     * @param string $provider Provider name (google, facebook, etc)
     * @return array Login result
     */
    public function socialLogin($userData, $provider) {
        // Check if user already exists with this email
        if ($this->user->emailExists($userData['email'])) {
            // If registered with the same provider, log them in
            if ($this->user->userExistsWithProvider($userData['email'], $provider)) {
                // Get user data directly
                $dbUser = $this->user->getUserByEmail($userData['email']);
                
                if (!$dbUser) {
                    return [
                        'status' => 'error',
                        'message' => 'Unable to retrieve user data'
                    ];
                }
                
                // Set up the session properly
                $_SESSION['user_id'] = $dbUser['id'];
                $_SESSION['first_name'] = $dbUser['first_name'];
                $_SESSION['last_name'] = $dbUser['last_name'];
                $_SESSION['email'] = $dbUser['email'];
                $_SESSION['auth_provider'] = $provider;
                $_SESSION['is_logged_in'] = true;
                
                return [
                    'status' => 'success',
                    'message' => 'Logged in successfully'
                ];
            } else {
                // User exists but used a different authentication method
                return [
                    'status' => 'error',
                    'message' => 'This email is already registered with a different authentication method'
                ];
            }
        } else {
            // New user - register them
            return $this->register($userData, $provider);
        }
    }
    
    /**
     * Check if user is logged in
     * 
     * @return bool True if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Get current user ID
     * 
     * @return int|null User ID if logged in, null otherwise
     */
    public function getUserId() {
        return $this->isLoggedIn() ? $_SESSION['user_id'] : null;
    }
    
    /**
     * Login a user after successful registration
     * 
     * @param int $userId User ID
     * @param string $firstName User's first name
     * @param string $lastName User's last name
     * @param string $email User's email
     * @param string $provider Auth provider used
     * @return void
     */
    public function loginAfterRegistration($userId, $firstName, $lastName, $email, $provider = 'local') {
        $_SESSION['user_id'] = $userId;
        $_SESSION['first_name'] = $firstName;
        $_SESSION['last_name'] = $lastName;
        $_SESSION['email'] = $email;
        $_SESSION['auth_provider'] = $provider;
        $_SESSION['is_logged_in'] = true;
    }
    
    /**
     * Login a user
     * 
     * @param string $email User's email
     * @param string $password User's password
     * @param bool $remember Whether to set a remember-me cookie
     * @return array Login result
     */
    public function login($email, $password, $remember = false) {
        try {
            // Check if email exists
            if (!$this->user->emailExists($email)) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid email or password'
                ];
            }
            
            // Get user details
            $userData = $this->user->getUserByEmail($email);
            
            // Check if the user is a local user
            if ($userData['auth_provider'] !== 'local') {
                return [
                    'status' => 'error',
                    'message' => 'Please login using ' . ucfirst($userData['auth_provider'])
                ];
            }
            
            // Verify password
            if (!password_verify($password, $userData['password'])) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid email or password'
                ];
            }
            
            // Login successful, set session variables
            $_SESSION['user_id'] = $userData['id'];
            $_SESSION['first_name'] = $userData['first_name'];
            $_SESSION['last_name'] = $userData['last_name'];
            $_SESSION['email'] = $userData['email'];
            $_SESSION['auth_provider'] = $userData['auth_provider'];
            $_SESSION['is_logged_in'] = true;
            
            // Handle remember me
            if ($remember) {
                $this->setRememberMeCookie($userData['id']);
            }
            
            return [
                'status' => 'success',
                'message' => 'Login successful'
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Login failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Set remember-me cookie
     * 
     * @param int $userId User ID
     * @return void
     */
    private function setRememberMeCookie($userId) {
        // Generate a token
        $token = bin2hex(random_bytes(32));
        
        // Store the token in the database (implementation depends on your database schema)
        // For now, we'll just set the cookie
        
        // Set cookie to expire in 30 days
        setcookie(
            'remember_me',
            $token,
            time() + (30 * 24 * 60 * 60),
            '/',
            '',
            true,
            true
        );
    }
    
    /**
     * Logout a user
     * 
     * @return void
     */
    public function logout() {
        // Unset all session variables
        $_SESSION = [];
        
        // Delete the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        // Clear remember-me cookie
        setcookie('remember_me', '', time() - 3600, '/');
        
        // Destroy the session
        session_destroy();
    }
}
?> 