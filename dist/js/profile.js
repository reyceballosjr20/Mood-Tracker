/**
 * Profile Image Preview Functionality
 * Replaces the current profile image with a preview when an image is selected
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
        
        // Find the existing image container - this will be the one we'll update
        let imageContainer = document.querySelector('[style*="width: 160px; height: 160px; border-radius: 50%"]');
        
        // Store the original content to restore if needed
        let originalContent = null;
        if (imageContainer) {
            originalContent = imageContainer.innerHTML;
        }
        
        // Handle file selection
        fileInput.addEventListener('change', function(e) {
            const file = this.files[0];
            
            // If we don't have an image container, try to find it again
            // (in case it was loaded after initial page load)
            if (!imageContainer) {
                imageContainer = document.querySelector('[style*="width: 160px; height: 160px; border-radius: 50%"]');
                if (imageContainer) {
                    originalContent = imageContainer.innerHTML;
                }
            }
            
            // If we still don't have an image container, we can't proceed
            if (!imageContainer) {
                console.error("Cannot find image container to update");
                return;
            }
            
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
                
                // Add loading state
                imageContainer.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 100%; color: #d1789c;"><i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i></div>';
                
                // Add "Preview" label if it doesn't exist
                let previewLabel = document.getElementById('image-preview-label');
                if (!previewLabel) {
                    previewLabel = document.createElement('div');
                    previewLabel.id = 'image-preview-label';
                    previewLabel.textContent = 'Preview';
                    previewLabel.style.textAlign = 'center';
                    previewLabel.style.marginTop = '10px';
                    previewLabel.style.fontSize = '0.9rem';
                    previewLabel.style.color = '#d1789c';
                    previewLabel.style.fontWeight = '500';
                    
                    // Insert the label after the image container
                    if (imageContainer.parentNode) {
                        imageContainer.parentNode.insertBefore(previewLabel, imageContainer.nextSibling);
                    }
                } else {
                    previewLabel.style.display = 'block';
                }
                
                // Create FileReader to display image
                const reader = new FileReader();
                reader.onload = function(e) {
                    imageContainer.innerHTML = '';
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.objectFit = 'cover';
                    imageContainer.appendChild(img);
                };
                
                // Read the file
                reader.readAsDataURL(file);
                
                // Clear any errors
                clearError();
            } else {
                // If no file is selected, restore original content
                if (originalContent) {
                    imageContainer.innerHTML = originalContent;
                }
                
                // Hide preview label
                const previewLabel = document.getElementById('image-preview-label');
                if (previewLabel) {
                    previewLabel.style.display = 'none';
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
            
            // Restore original image
            if (imageContainer && originalContent) {
                imageContainer.innerHTML = originalContent;
            }
            
            // Hide preview label
            const previewLabel = document.getElementById('image-preview-label');
            if (previewLabel) {
                previewLabel.style.display = 'none';
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