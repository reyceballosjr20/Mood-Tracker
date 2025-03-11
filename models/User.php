<?php
require_once 'Database.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Create a new user in the database
     * 
     * @param string $first_name User's first name
     * @param string $last_name User's last name
     * @param string $email User's email
     * @param string $password User's password (will be hashed)
     * @param string $auth_provider Authentication provider (default 'local')
     * @return array Status and message
     */
    public function register($first_name, $last_name, $email, $password, $auth_provider = 'local') {
        try {
            // Check if email already exists
            if ($this->emailExists($email)) {
                // If the user exists with the same auth provider, it's a duplicate
                if ($this->userExistsWithProvider($email, $auth_provider)) {
                    return [
                        'status' => 'error',
                        'message' => 'Email is already registered with this authentication method'
                    ];
                }
                
                // For social logins, we might want to link accounts instead
                if ($auth_provider !== 'local') {
                    // Logic to link accounts could go here
                    // For now, just return an error
                    return [
                        'status' => 'error',
                        'message' => 'Email already exists. Please log in with your existing account'
                    ];
                }
                
                return [
                    'status' => 'error',
                    'message' => 'Email is already registered'
                ];
            }
            
            // Hash password (only for local auth)
            $hashedPassword = $auth_provider === 'local' ? password_hash($password, PASSWORD_DEFAULT) : null;
            
            // Create user
            $sql = "INSERT INTO users (first_name, last_name, email, password, auth_provider, created_at) 
                    VALUES (:first_name, :last_name, :email, :password, :auth_provider, NOW())";
            
            $stmt = $this->db->conn->prepare($sql);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':auth_provider', $auth_provider);
            
            $stmt->execute();
            
            // Get the newly created user ID
            $userId = $this->db->conn->lastInsertId();
            
            return [
                'status' => 'success',
                'message' => 'User registered successfully',
                'user_id' => $userId
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'error',
                'message' => 'Registration failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Check if email exists in the database
     * 
     * @param string $email Email to check
     * @return bool True if email exists
     */
    public function emailExists($email) {
        $sql = "SELECT email FROM users WHERE email = :email";
        $stmt = $this->db->conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return ($stmt->rowCount() > 0);
    }
    
    /**
     * Check if user exists with specific authentication provider
     * 
     * @param string $email User email
     * @param string $provider Auth provider
     * @return bool True if user exists with that provider
     */
    public function userExistsWithProvider($email, $provider) {
        $sql = "SELECT id FROM users WHERE email = :email AND auth_provider = :provider";
        $stmt = $this->db->conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':provider', $provider);
        $stmt->execute();
        
        return ($stmt->rowCount() > 0);
    }
    
    /**
     * Validate user input
     * 
     * @param array $data User input data
     * @return array Validation errors
     */
    public function validateInput($data) {
        $errors = [];
        
        // Validate name (first and last name)
        if (empty($data['name'])) {
            $errors[] = 'Full name is required';
        } elseif (!preg_match('/^[A-Za-z]+(?: [A-Za-z]+)+$/', $data['name'])) {
            $errors[] = 'Please enter your first and last name';
        }
        
        // Validate email
        if (empty($data['email'])) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address';
        }
        
        // Validate password
        if (empty($data['password'])) {
            $errors[] = 'Password is required';
        } elseif (strlen($data['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }
        
        // Validate password confirmation
        if (empty($data['confirm_password'])) {
            $errors[] = 'Please confirm your password';
        } elseif ($data['password'] !== $data['confirm_password']) {
            $errors[] = 'Passwords must match';
        }
        
        // Validate terms agreement
        if (!isset($data['terms'])) {
            $errors[] = 'You must agree to the Terms of Service and Privacy Policy';
        }
        
        return $errors;
    }
    
    /**
     * Get user by email
     * 
     * @param string $email User email
     * @return array|false User data or false if not found
     */
    public function getUserByEmail($email) {
        $sql = "SELECT id, first_name, last_name, email, password, auth_provider 
                FROM users WHERE email = :email";
        $stmt = $this->db->conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?> 