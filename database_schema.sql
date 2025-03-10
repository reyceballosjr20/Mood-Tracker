-- Users table with authentication information
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(191) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- Stored as hash, never plaintext
    profile_image VARCHAR(255) DEFAULT NULL,
    auth_provider ENUM('local', 'google', 'facebook') DEFAULT 'local',
    auth_provider_id VARCHAR(255) DEFAULT NULL, -- External ID from provider
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Optional: Add an index for faster email lookups
    INDEX idx_email (email)
);

