<?php
require_once 'User.php';

class Auth {
    private $user;
    
    public function __construct() {
        $this->user = new User();
        
        // Start session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    // Register a new user
    public function register($userData) {
        // Set user properties
        $this->user->first_name = $userData['first_name'] ?? null;
        $this->user->last_name = $userData['last_name'] ?? null;
        $this->user->email = $userData['email'] ?? null;
        $this->user->password = $userData['password'] ?? null;
        $this->user->auth_provider = $userData['auth_provider'] ?? 'local';
        
        // Validate user input
        $errors = $this->user->validate();
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }
        
        // Create user
        if ($this->user->create()) {
            // Set session data
            $this->setUserSession();
            
            return [
                'success' => true,
                'message' => 'Registration successful',
                'user_id' => $this->user->user_id
            ];
        } else {
            return [
                'success' => false,
                'errors' => ['Failed to create account. Please try again.']
            ];
        }
    }
    
    // Log in a user
    public function login($email, $password) {
        // Set email property
        $this->user->email = $email;
        
        // Find user by email
        if ($this->user->findByEmail()) {
            // Check if user is active
            if (!$this->user->is_active) {
                return [
                    'success' => false,
                    'errors' => ['Your account is not active. Please contact support.']
                ];
            }
            
            // Verify password
            if ($this->user->verifyPassword($password)) {
                // Update last login timestamp
                $this->user->updateLastLogin();
                
                // Set session data
                $this->setUserSession();
                
                return [
                    'success' => true,
                    'message' => 'Login successful',
                    'user_id' => $this->user->user_id
                ];
            } else {
                return [
                    'success' => false,
                    'errors' => ['Invalid email or password']
                ];
            }
        } else {
            return [
                'success' => false,
                'errors' => ['Invalid email or password']
            ];
        }
    }
    
    // Set user session data
    private function setUserSession() {
        $_SESSION['user_id'] = $this->user->user_id;
        $_SESSION['first_name'] = $this->user->first_name;
        $_SESSION['last_name'] = $this->user->last_name;
        $_SESSION['email'] = $this->user->email;
        $_SESSION['is_logged_in'] = true;
    }
    
    // Log out user
    public function logout() {
        // Unset all session variables
        $_SESSION = [];
        
        // Delete the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy the session
        session_destroy();
        
        return true;
    }
    
    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
    }
    
    // Get current user data
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'user_id' => $_SESSION['user_id'],
                'first_name' => $_SESSION['first_name'],
                'last_name' => $_SESSION['last_name'],
                'email' => $_SESSION['email']
            ];
        }
        
        return null;
    }
}
?> 