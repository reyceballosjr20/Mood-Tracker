<?php

class Auth {
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
        
        try {
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
                error_log('Failed to create user: ' . print_r($userData, true));
                return [
                    'success' => false,
                    'errors' => ['Failed to create account. Please try again.']
                ];
            }
        } catch (Exception $e) {
            error_log('Exception during user registration: ' . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['An unexpected error occurred. Please try again.']
            ];
        }
    }
} 