/**
 * Password Update Handler
 * Provides client-side validation for password update form
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log("Initializing password update form validation...");
    
    function initPasswordForm() {
        const passwordForm = document.getElementById('password-form');
        
        if (!passwordForm) {
            console.log("Password form not found");
            return;
        }
        
        console.log("Password form found, setting up validation");
        const currentPasswordInput = document.getElementById('current_password');
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        // Create error message element
        function createErrorElement(id, message) {
            const errorDiv = document.createElement('div');
            errorDiv.id = id;
            errorDiv.className = 'form-error';
            errorDiv.textContent = message;
            return errorDiv;
        }
        
        // Show error message for an input
        function showError(input, message) {
            const errorId = input.id + '-error';
            let errorElement = document.getElementById(errorId);
            
            if (!errorElement) {
                errorElement = createErrorElement(errorId, message);
                input.parentNode.appendChild(errorElement);
            } else {
                errorElement.textContent = message;
            }
            
            errorElement.classList.add('show');
            input.style.borderColor = '#ef5350';
            
            return false;
        }
        
        // Clear error for an input
        function clearError(input) {
            const errorId = input.id + '-error';
            const errorElement = document.getElementById(errorId);
            
            if (errorElement) {
                errorElement.classList.remove('show');
            }
            
            input.style.borderColor = '#f5d7e3';
        }
        
        // Password strength meter functionality
        function checkPasswordStrength(password) {
            // Initialize score
            let score = 0;
            
            // Length check
            if (password.length >= 8) score += 1;
            if (password.length >= 12) score += 1;
            
            // Complexity checks
            if (/[A-Z]/.test(password)) score += 1; // Has uppercase
            if (/[a-z]/.test(password)) score += 1; // Has lowercase
            if (/[0-9]/.test(password)) score += 1; // Has number
            if (/[^A-Za-z0-9]/.test(password)) score += 1; // Has special char
            
            return {
                score: Math.min(score, 5), // Cap at 5
                feedback: getStrengthFeedback(score)
            };
        }
        
        function getStrengthFeedback(score) {
            switch(score) {
                case 0:
                case 1:
                    return {text: "Very Weak", color: "#e53935"};
                case 2:
                    return {text: "Weak", color: "#ef6c00"};
                case 3:
                    return {text: "Moderate", color: "#fbc02d"};
                case 4:
                    return {text: "Strong", color: "#7cb342"};
                case 5:
                    return {text: "Very Strong", color: "#388e3c"};
                default:
                    return {text: "Invalid", color: "#e53935"};
            }
        }
        
        function updateStrengthMeter(password) {
            const strengthResult = checkPasswordStrength(password);
            
            // Create or get strength meter
            let meterContainer = document.getElementById('password-strength-container');
            if (!meterContainer) {
                meterContainer = document.createElement('div');
                meterContainer.id = 'password-strength-container';
                meterContainer.style.marginTop = '8px';
                newPasswordInput.parentNode.appendChild(meterContainer);
                
                // Create label
                const label = document.createElement('div');
                label.style.fontSize = '0.8rem';
                label.style.marginBottom = '4px';
                label.textContent = 'Password Strength:';
                meterContainer.appendChild(label);
                
                // Create meter bar background
                const meterBg = document.createElement('div');
                meterBg.style.height = '6px';
                meterBg.style.backgroundColor = '#e0e0e0';
                meterBg.style.borderRadius = '3px';
                meterBg.style.overflow = 'hidden';
                meterContainer.appendChild(meterBg);
                
                // Create meter bar fill
                const meterFill = document.createElement('div');
                meterFill.id = 'password-strength-meter';
                meterFill.style.height = '100%';
                meterFill.style.width = '0%';
                meterFill.style.backgroundColor = '#d1789c';
                meterFill.style.transition = 'width 0.3s, background-color 0.3s';
                meterBg.appendChild(meterFill);
                
                // Create feedback text
                const feedback = document.createElement('div');
                feedback.id = 'password-strength-text';
                feedback.style.fontSize = '0.8rem';
                feedback.style.marginTop = '4px';
                feedback.style.textAlign = 'right';
                meterContainer.appendChild(feedback);
            }
            
            // Update meter
            const meterFill = document.getElementById('password-strength-meter');
            const feedbackText = document.getElementById('password-strength-text');
            
            if (password.length === 0) {
                meterFill.style.width = '0%';
                feedbackText.textContent = '';
            } else {
                meterFill.style.width = (strengthResult.score * 20) + '%';
                meterFill.style.backgroundColor = strengthResult.feedback.color;
                feedbackText.textContent = strengthResult.feedback.text;
                feedbackText.style.color = strengthResult.feedback.color;
            }
        }
        
        // Add event listeners for input fields
        if (currentPasswordInput) {
            currentPasswordInput.addEventListener('input', function() {
                clearError(currentPasswordInput);
            });
        }
        
        if (newPasswordInput) {
            newPasswordInput.addEventListener('input', function() {
                clearError(newPasswordInput);
                updateStrengthMeter(this.value);
                
                // Also clear confirm password error if it matches now
                if (confirmPasswordInput && 
                    confirmPasswordInput.value === newPasswordInput.value) {
                    clearError(confirmPasswordInput);
                }
            });
        }
        
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                clearError(confirmPasswordInput);
            });
        }
        
        // Form submission handler
        passwordForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validate current password
            if (!currentPasswordInput.value) {
                isValid = showError(currentPasswordInput, 'Current password is required');
                e.preventDefault();
            } else {
                clearError(currentPasswordInput);
            }
            
            // Validate new password
            if (!newPasswordInput.value) {
                isValid = showError(newPasswordInput, 'New password is required');
                e.preventDefault();
            } else if (newPasswordInput.value.length < 8) {
                isValid = showError(newPasswordInput, 'Password must be at least 8 characters');
                e.preventDefault();
            } else {
                clearError(newPasswordInput);
            }
            
            // Validate password confirmation
            if (!confirmPasswordInput.value) {
                isValid = showError(confirmPasswordInput, 'Please confirm your password');
                e.preventDefault();
            } else if (confirmPasswordInput.value !== newPasswordInput.value) {
                isValid = showError(confirmPasswordInput, 'Passwords do not match');
                e.preventDefault();
            } else {
                clearError(confirmPasswordInput);
            }
            
            return isValid;
        });
    }
    
    // Initialize on page load
    initPasswordForm();
    
    // Initialize when content changes (for SPA navigation)
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && document.getElementById('password-form')) {
                setTimeout(initPasswordForm, 100);
            }
        });
    });
    
    // Observe content container for changes
    const contentContainer = document.getElementById('contentContainer');
    if (contentContainer) {
        observer.observe(contentContainer, { childList: true, subtree: true });
    }
}); 