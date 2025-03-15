/**
 * Profile Image Preview Functionality
 * Shows a preview of the selected image before uploading
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log("Initializing profile image preview...");
    
    function initProfileImagePreview() {
        const fileInput = document.getElementById('profile_image');
        
        if (!fileInput) {
            console.log("Profile image input not found");
            return;
        }
        
        console.log("Profile image input found, setting up preview");
        
        // Find the image container
        const imageContainer = document.querySelector('[style*="width: 160px; height: 160px; border-radius: 50%"]');
        if (!imageContainer) {
            console.log("Image container not found");
            return;
        }
        
        // Store original content
        const originalContent = imageContainer.innerHTML;
        
        // Add a hidden input field to track the original image path
        const form = fileInput.closest('form');
        if (form) {
            // Look for existing image
            const existingImg = imageContainer.querySelector('img');
            if (existingImg && existingImg.src) {
                // Add hidden input for old image if it doesn't exist
                if (!form.querySelector('input[name="old_image"]')) {
                    const oldImageInput = document.createElement('input');
                    oldImageInput.type = 'hidden';
                    oldImageInput.name = 'old_image';
                    oldImageInput.value = existingImg.src;
                    form.appendChild(oldImageInput);
                }
            }
        }
        
        // Handle file selection
        fileInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                
                // Validate file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPG, PNG, or GIF)');
                    this.value = '';
                    return;
                }
                
                // Validate file size (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB');
                    this.value = '';
                    return;
                }
                
                // Show loading state
                imageContainer.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 100%; width: 100%; background-color: #f5d7e3;"><i class="fas fa-spinner fa-spin" style="color: #d1789c; font-size: 24px;"></i></div>';
                
                // Create preview label if it doesn't exist
                let previewLabel = document.getElementById('preview-label');
                if (!previewLabel) {
                    previewLabel = document.createElement('div');
                    previewLabel.id = 'preview-label';
                    previewLabel.textContent = 'Preview';
                    previewLabel.style.textAlign = 'center';
                    previewLabel.style.marginTop = '10px';
                    previewLabel.style.color = '#d1789c';
                    previewLabel.style.fontSize = '0.9rem';
                    previewLabel.style.fontWeight = '500';
                    
                    if (imageContainer.parentNode) {
                        imageContainer.parentNode.insertBefore(previewLabel, imageContainer.nextSibling);
                    }
                } else {
                    previewLabel.style.display = 'block';
                }
                
                // Create FileReader to create preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    imageContainer.innerHTML = `<img src="${e.target.result}" alt="Profile Preview" style="width: 100%; height: 100%; object-fit: cover;">`;
                };
                reader.readAsDataURL(file);
            } else {
                // If no file is selected (or selection canceled), restore original
                imageContainer.innerHTML = originalContent;
                
                // Hide preview label
                const previewLabel = document.getElementById('preview-label');
                if (previewLabel) {
                    previewLabel.style.display = 'none';
                }
            }
        });
    }
    
    // Initialize when the DOM is loaded
    initProfileImagePreview();
    
    // Initialize when content is dynamically loaded (for SPA)
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && document.getElementById('profile_image')) {
                // Small delay to ensure all elements are loaded
                setTimeout(initProfileImagePreview, 100);
            }
        });
    });
    
    // Observe content container for changes
    const contentContainer = document.getElementById('contentContainer');
    if (contentContainer) {
        observer.observe(contentContainer, { childList: true, subtree: true });
    }
}); 