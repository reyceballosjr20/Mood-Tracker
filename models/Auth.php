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
                $userId = $this->getUserIdByEmail($userData['email']);
                
                // Split name into first and last name
                $nameParts = explode(' ', $userData['name'], 2);
                $firstName = $nameParts[0];
                $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
                
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_first_name'] = $firstName;
                $_SESSION['user_last_name'] = $lastName;
                $_SESSION['user_email'] = $userData['email'];
                $_SESSION['auth_provider'] = $provider;
                
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
        $_SESSION['user_first_name'] = $firstName;
        $_SESSION['user_last_name'] = $lastName;
        $_SESSION['user_email'] = $email;
        $_SESSION['auth_provider'] = $provider;
    }
}
?> 