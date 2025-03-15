/**
 * Profile Information Update Handler
 * Provides AJAX form submission for personal information updates
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the form handler
    function initProfileInfoForm() {
        const profileForm = document.getElementById('profile-form');
        
        if (!profileForm) {
            console.log("Profile form not found");
            return;
        }
        
        // Form validation
        function validateForm() {
            const firstNameInput = document.getElementById('first_name');
            const lastNameInput = document.getElementById('last_name');
            let isValid = true;
            
            // Clear existing errors
            clearError(firstNameInput);
            clearError(lastNameInput);
            
            // Validate first name
            if (firstNameInput.value.trim() === '') {
                showError(firstNameInput, 'First name is required');
                isValid = false;
            }
            
            // Validate last name
            if (lastNameInput.value.trim() === '') {
                showError(lastNameInput, 'Last name is required');
                isValid = false;
            }
            
            return isValid;
        }
        
        // Show error message
        function showError(input, message) {
            const errorId = input.id + '-error';
            let errorElement = document.getElementById(errorId);
            
            if (!errorElement) {
                errorElement = document.createElement('div');
                errorElement.id = errorId;
                errorElement.className = 'form-error';
                errorElement.textContent = message;
                input.parentNode.appendChild(errorElement);
            } else {
                errorElement.textContent = message;
            }
            
            errorElement.classList.add('show');
            input.style.borderColor = '#ef5350';
            
            return false;
        }
        
        // Clear error message
        function clearError(input) {
            const errorId = input.id + '-error';
            const errorElement = document.getElementById(errorId);
            
            if (errorElement) {
                errorElement.classList.remove('show');
            }
            
            input.style.borderColor = '#f5d7e3';
        }
        
        // Show success message
        function showSuccess(message) {
            // Create or get success message container
            let successElement = document.getElementById('profile-success-message');
            if (!successElement) {
                successElement = document.createElement('div');
                successElement.id = 'profile-success-message';
                successElement.className = 'alert-custom success';
                successElement.style.backgroundColor = '#e8f5e9';
                successElement.style.color = '#2e7d32';
                successElement.style.padding = '12px 18px';
                successElement.style.borderRadius = '12px';
                successElement.style.marginBottom = '20px';
                successElement.style.fontSize = '14px';
                successElement.style.borderLeft = '4px solid #4caf50';
                successElement.style.boxShadow = '0 4px 15px rgba(0,0,0,0.04)';
                
                // Insert at the top of the form
                profileForm.parentNode.insertBefore(successElement, profileForm);
            }
            
            // Set message content with icon
            successElement.innerHTML = `<i class="fas fa-check-circle" style="margin-right: 8px;"></i> ${message}`;
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                successElement.style.opacity = '0';
                successElement.style.transition = 'opacity 0.5s ease';
                
                setTimeout(() => {
                    if (successElement.parentNode) {
                        successElement.parentNode.removeChild(successElement);
                    }
                }, 500);
            }, 5000);
        }
        
        // Show error alert
        function showErrorAlert(message) {
            // Create or get error message container
            let errorElement = document.getElementById('profile-error-message');
            if (!errorElement) {
                errorElement = document.createElement('div');
                errorElement.id = 'profile-error-message';
                errorElement.className = 'alert-custom error';
                errorElement.style.backgroundColor = '#ffebee';
                errorElement.style.color = '#c62828';
                errorElement.style.padding = '12px 18px';
                errorElement.style.borderRadius = '12px';
                errorElement.style.marginBottom = '20px';
                errorElement.style.fontSize = '14px';
                errorElement.style.borderLeft = '4px solid #ef5350';
                errorElement.style.boxShadow = '0 4px 15px rgba(0,0,0,0.04)';
                
                // Insert at the top of the form
                profileForm.parentNode.insertBefore(errorElement, profileForm);
            }
            
            // Set message content with icon
            errorElement.innerHTML = `<i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i> ${message}`;
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                errorElement.style.opacity = '0';
                errorElement.style.transition = 'opacity 0.5s ease';
                
                setTimeout(() => {
                    if (errorElement.parentNode) {
                        errorElement.parentNode.removeChild(errorElement);
                    }
                }, 500);
            }, 5000);
        }
        
        // Handle form submission
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!validateForm()) {
                return false;
            }
            
            // Prepare form data
            const formData = new FormData(profileForm);
            
            // Change submit button to loading state
            const submitBtn = profileForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
        ;
            submitBtn.disabled = true;
            
            // Send AJAX request
            fetch('api/update_profile_info.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess(data.message);
                    
                    // Update user name in the sidebar if it exists
                    const sidebarUserName = document.querySelector('.user-name');
                    if (sidebarUserName) {
                        const firstName = formData.get('first_name');
                        if (firstName) {
                            sidebarUserName.textContent = firstName;
                        }
                    }
                } else {
                    showErrorAlert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorAlert('An error occurred while updating your profile.');
            })
            .finally(() => {
                // Restore button state - ensure "Saving..." is removed
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            });
            
            return false;
        });
        
        // Add input handlers for validation
        const firstNameInput = document.getElementById('first_name');
        const lastNameInput = document.getElementById('last_name');
        
        if (firstNameInput) {
            firstNameInput.addEventListener('input', function() {
                clearError(firstNameInput);
            });
        }
        
        if (lastNameInput) {
            lastNameInput.addEventListener('input', function() {
                clearError(lastNameInput);
            });
        }
    }
    
    // Initialize on page load
    initProfileInfoForm();
    
    // Initialize when content changes (for SPA navigation)
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && document.getElementById('profile-form')) {
                initProfileInfoForm();
            }
        });
    });
    
    // Observe content container for changes
    const contentContainer = document.getElementById('contentContainer');
    if (contentContainer) {
        observer.observe(contentContainer, { childList: true, subtree: true });
    }
}); 