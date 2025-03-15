/**
 * Profile Image Preview and Upload Functionality
 * Handles both image preview and AJAX upload
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
        
        // Find the form that contains the file input
        const form = fileInput.closest('form');
        
        // Find the existing image container
        let imageContainer = document.querySelector('[style*="width: 160px; height: 160px; border-radius: 50%"]');
        
        // Store the original content to restore if needed
        let originalContent = null;
        let originalImagePath = null;
        if (imageContainer) {
            originalContent = imageContainer.innerHTML;
            // Try to get the original image path
            const originalImg = imageContainer.querySelector('img');
            if (originalImg) {
                originalImagePath = originalImg.getAttribute('src');
            }
        }
        
        // Handle file selection for preview
        fileInput.addEventListener('change', function(e) {
            const file = this.files[0];
            
            // If we don't have an image container, try to find it again
            if (!imageContainer) {
                imageContainer = document.querySelector('[style*="width: 160px; height: 160px; border-radius: 50%"]');
                if (imageContainer) {
                    originalContent = imageContainer.innerHTML;
                    const originalImg = imageContainer.querySelector('img');
                    if (originalImg) {
                        originalImagePath = originalImg.getAttribute('src');
                    }
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
        
        // Modify form submission to use AJAX
        if (form) {
            form.addEventListener('submit', function(e) {
                // Only intercept if a file is selected
                if (fileInput.files.length > 0) {
                    e.preventDefault();
                    
                    // Create FormData object
                    const formData = new FormData();
                    formData.append('profile_image', fileInput.files[0]);
                    
                    // Add original image path if available
                    if (originalImagePath) {
                        formData.append('old_image', originalImagePath);
                    }
                    
                    // Show loading state
                    const submitBtn = form.querySelector('button[type="submit"]');
                    const originalBtnText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
                    submitBtn.disabled = true;
                    
                    // Send AJAX request
                    fetch('api/upload_profile_image.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            showSuccess(data.message);
                            
                            // Update image container with the new image
                            if (imageContainer) {
                                // Remove preview label
                                const previewLabel = document.getElementById('image-preview-label');
                                if (previewLabel) {
                                    previewLabel.style.display = 'none';
                                }
                                
                                // Update the image source if it's already an img element
                                const img = imageContainer.querySelector('img');
                                if (img) {
                                    img.src = '../' + data.file_path;
                                } else {
                                    // Create a new img element
                                    imageContainer.innerHTML = '';
                                    const newImg = document.createElement('img');
                                    newImg.src = '../' + data.file_path;
                                    newImg.alt = 'Profile Image';
                                    newImg.style.width = '100%';
                                    newImg.style.height = '100%';
                                    newImg.style.objectFit = 'cover';
                                    imageContainer.appendChild(newImg);
                                }
                                
                                // Update originalContent and originalImagePath
                                originalContent = imageContainer.innerHTML;
                                originalImagePath = '../' + data.file_path;
                            }
                            
                            // Update sidebar profile image
                            const sidebarAvatar = document.querySelector('.user-avatar');
                            if (sidebarAvatar) {
                                sidebarAvatar.innerHTML = `<img src="../${data.file_path}" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
                            }
                        } else {
                            // Show error message
                            showError(data.message);
                        }
                    })
                    .catch(error => {
                        showError('An error occurred while uploading the image');
                        console.error('Upload error:', error);
                    })
                    .finally(() => {
                        // Restore button state
                        submitBtn.innerHTML = originalBtnText;
                        submitBtn.disabled = false;
                    });
                }
            });
        }
        
        // Error and success message functions
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
            
            // Hide success message if visible
            const successEl = document.getElementById('image-upload-success');
            if (successEl) {
                successEl.style.display = 'none';
            }
            
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
        
        function showSuccess(message) {
            let successEl = document.getElementById('image-upload-success');
            
            if (!successEl) {
                successEl = document.createElement('div');
                successEl.id = 'image-upload-success';
                successEl.style.color = '#2e7d32';
                successEl.style.fontSize = '0.9rem';
                successEl.style.marginTop = '8px';
                successEl.style.padding = '8px 12px';
                successEl.style.backgroundColor = '#e8f5e9';
                successEl.style.borderRadius = '8px';
                successEl.style.borderLeft = '3px solid #4caf50';
                fileInput.parentNode.appendChild(successEl);
            }
            
            successEl.textContent = message;
            successEl.style.display = 'block';
            
            // Hide error message if visible
            const errorEl = document.getElementById('image-upload-error');
            if (errorEl) {
                errorEl.style.display = 'none';
            }
            
            // Reset the file input
            fileInput.value = '';
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