<?php
require_once 'Database.php';

class User {
    // Database connection and table name
    private $conn;
    private $table_name = "users";
    
    // User properties
    public $user_id;
    public $first_name;
    public $last_name;
    public $email;
    public $password;
    public $profile_image;
    public $auth_provider;
    public $is_active;
    public $last_login;
    public $created_at;
    public $updated_at;
    
    // Constructor with database connection
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Check if email already exists
    public function emailExists() {
        $query = "SELECT email FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        
        // Sanitize and bind email parameter
        $this->email = htmlspecialchars(strip_tags($this->email));
        $stmt->bindParam(':email', $this->email);
        
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    // Create new user
    public function create() {
        // Check if email already exists
        if ($this->emailExists()) {
            return false;
        }
        
        // Insert query
        $query = "INSERT INTO " . $this->table_name . " 
                  (first_name, last_name, email, password, auth_provider, is_active) 
                  VALUES 
                  (:first_name, :last_name, :email, :password, :auth_provider, :is_active)";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize and bind parameters
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        
        // Hash the password
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        
        // Default values if not set
        $auth_provider = $this->auth_provider ?: 'local';
        $is_active = $this->is_active ?: true;
        
        $stmt->bindParam(':auth_provider', $auth_provider);
        $stmt->bindParam(':is_active', $is_active, PDO::PARAM_BOOL);
        
        // Execute the query
        if ($stmt->execute()) {
            $this->user_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Validate user input for registration
    public function validate() {
        $errors = [];
        
        if (empty($this->first_name)) {
            $errors[] = "First name is required";
        }
        
        if (empty($this->last_name)) {
            $errors[] = "Last name is required";
        }
        
        if (empty($this->email)) {
            $errors[] = "Email is required";
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        } elseif ($this->emailExists()) {
            $errors[] = "Email already exists";
        }
        
        if (empty($this->password)) {
            $errors[] = "Password is required";
        } elseif (strlen($this->password) < 8) {
            $errors[] = "Password must be at least 8 characters";
        }
        
        return $errors;
    }
    
    // Find user by email for login
    public function findByEmail() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        
        // Sanitize and bind email parameter
        $this->email = htmlspecialchars(strip_tags($this->email));
        $stmt->bindParam(':email', $this->email);
        
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            $this->user_id = $row['user_id'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->email = $row['email'];
            $this->password = $row['password'];
            $this->profile_image = $row['profile_image'];
            $this->auth_provider = $row['auth_provider'];
            $this->is_active = $row['is_active'];
            $this->last_login = $row['last_login'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }
    
    // Verify password for login
    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }
    
    // Update last login timestamp
    public function updateLastLogin() {
        $query = "UPDATE " . $this->table_name . " 
                  SET last_login = CURRENT_TIMESTAMP 
                  WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->user_id);
        
        return $stmt->execute();
    }
}
?> 