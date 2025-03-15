/**
 * Profile Image Preview Functionality
 * Provides real-time preview of selected profile images before upload
 */
document.addEventListener('DOMContentLoaded', function() {
    // Function to initialize image preview on profile page
    function initProfileImagePreview() {
        console.log("Initializing profile image preview...");
        const fileInput = document.getElementById('profile_image');
        
        if (!fileInput) {
            console.log("Profile image input not found");
            return; // Exit if we're not on the profile page
        }
        
        console.log("Profile image input found, setting up listeners");
        
        // Find the avatar container
        let avatarContainer = document.querySelector('.user-avatar');
        if (document.querySelector('[style*="width: 160px; height: 160px; border-radius: 50%"]')) {
            avatarContainer = document.querySelector('[style*="width: 160px; height: 160px; border-radius: 50%"]');
        }
        
        // Create preview container if not exists
        let previewContainer = document.querySelector('.image-preview-container');
        if (!previewContainer) {
            previewContainer = document.createElement('div');
            previewContainer.className = 'image-preview-container';
            previewContainer.style.width = '160px';
            previewContainer.style.height = '160px';
            previewContainer.style.borderRadius = '50%';
            previewContainer.style.overflow = 'hidden';
            previewContainer.style.margin = '0 auto 10px auto';
            previewContainer.style.boxShadow = '0 10px 20px rgba(209, 120, 156, 0.2)';
            previewContainer.style.border = '4px solid #fff';
            previewContainer.style.position = 'relative';
            previewContainer.style.backgroundColor = '#f5d7e3';
            
            // Add label to the preview
            const previewLabel = document.createElement('div');
            previewLabel.textContent = 'Preview';
            previewLabel.style.textAlign = 'center';
            previewLabel.style.marginTop = '10px';
            previewLabel.style.fontSize = '0.9rem';
            previewLabel.style.color = '#d1789c';
            previewLabel.style.fontWeight = '500';
            
            // Insert the preview container
            if (avatarContainer && avatarContainer.parentNode) {
                avatarContainer.parentNode.insertBefore(previewContainer, avatarContainer.nextSibling);
                avatarContainer.parentNode.insertBefore(previewLabel, previewContainer.nextSibling);
                
                // Initially hide the preview
                previewContainer.style.display = 'none';
                previewLabel.style.display = 'none';
            }
        }
        
        // Handle file selection
        fileInput.addEventListener('change', function(e) {
            const file = this.files[0];
            
            if (file) {
                // Check file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    showError('Please select a valid image file (JPG, PNG, or GIF)');
                    return;
                }
                
                // Check file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    showError('Image file is too large. Maximum size is 5MB.');
                    return;
                }
                
                // Show the preview container and label
                previewContainer.style.display = 'block';
                if (previewContainer.nextSibling && previewContainer.nextSibling.textContent === 'Preview') {
                    previewContainer.nextSibling.style.display = 'block';
                }
                
                // Add loading state
                previewContainer.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 100%; color: #d1789c;"><i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i></div>';
                
                // Create FileReader to display image
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewContainer.innerHTML = '';
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.objectFit = 'cover';
                    previewContainer.appendChild(img);
                };
                
                // Read the file
                reader.readAsDataURL(file);
                
                // Clear any errors
                clearError();
            } else {
                // Hide preview if no file selected
                previewContainer.style.display = 'none';
                if (previewContainer.nextSibling && previewContainer.nextSibling.textContent === 'Preview') {
                    previewContainer.nextSibling.style.display = 'none';
                }
            }
        });
        
        // Error handling functions
        function showError(message) {
            let errorEl = document.getElementById('image-upload-error');
            
            if (!errorEl) {
                errorEl = document.createElement('div');
                errorEl.id = 'image-upload-error';
                errorEl.style.color = '#c62828';
                errorEl.style.fontSize = '0.9rem';
                errorEl.style.marginTop = '8px';
                errorEl.style.padding = '8px 12px';
                errorEl.style.backgroundColor = '#ffebee';
                errorEl.style.borderRadius = '8px';
                errorEl.style.borderLeft = '3px solid #ef5350';
                fileInput.parentNode.appendChild(errorEl);
            }
            
            errorEl.textContent = message;
            errorEl.style.display = 'block';
            
            // Reset the file input
            fileInput.value = '';
            
            // Hide preview
            previewContainer.style.display = 'none';
            if (previewContainer.nextSibling && previewContainer.nextSibling.textContent === 'Preview') {
                previewContainer.nextSibling.style.display = 'none';
            }
        }
        
        function clearError() {
            const errorEl = document.getElementById('image-upload-error');
            if (errorEl) {
                errorEl.style.display = 'none';
            }
        }
    }
    
    // Initialize when DOM is loaded
    initProfileImagePreview();
    
    // Re-initialize when content changes (for SPA navigation)
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && document.getElementById('profile_image')) {
                initProfileImagePreview();
            }
        });
    });
    
    // Observe the content container for changes
    const contentContainer = document.getElementById('contentContainer');
    if (contentContainer) {
        observer.observe(contentContainer, { childList: true, subtree: true });
    }
}); 