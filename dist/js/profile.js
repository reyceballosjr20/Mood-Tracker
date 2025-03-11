document.addEventListener('DOMContentLoaded', function() {
    // Initial setup for profile functionality
    setupProfileFunctionality();
    
    // Also listen for content changes in the main content area
    const mainContent = document.getElementById('mainContent');
    if (mainContent) {
        // Use a MutationObserver to detect when profile content is loaded
        const observer = new MutationObserver(function(mutations) {
            // Check if profile elements exist after DOM changes
            if (document.getElementById('profileForm')) {
                console.log('Profile content detected in DOM, initializing functionality');
                setupProfileFunctionality();
            }
        });
        
        // Start observing the main content area for changes
        observer.observe(mainContent, { childList: true, subtree: true });
    }
    
    // Main function to set up all profile functionality
    function setupProfileFunctionality() {
        console.log('Setting up profile functionality');
        
        // Profile form submission
        const saveChangesBtn = document.getElementById('saveChangesBtn');
        const saveSpinner = document.getElementById('saveSpinner');
        const profileForm = document.getElementById('profileForm');
        const profileAlert = document.getElementById('profileAlert');
        
        if (saveChangesBtn && profileForm) {
            // Remove any existing event listeners
            saveChangesBtn.removeEventListener('click', handleSaveChanges);
            
            // Add new event listener
            saveChangesBtn.addEventListener('click', handleSaveChanges);
        }
        
        // Password form submission
        const updatePasswordBtn = document.getElementById('updatePasswordBtn');
        const passwordForm = document.getElementById('passwordForm');
        
        if (updatePasswordBtn && passwordForm) {
            // Remove any existing event listeners
            updatePasswordBtn.removeEventListener('click', handlePasswordUpdate);
            
            // Add new event listener
            updatePasswordBtn.addEventListener('click', handlePasswordUpdate);
        }
        
        // Profile image handling
        setupImageHandlers();
        
        // Save changes handler function
        function handleSaveChanges() {
            // Show loading state
            saveChangesBtn.classList.add('saving', 'pulse');
            if (saveSpinner) saveSpinner.style.display = 'block';
            if (saveChangesBtn.querySelector('span')) {
                saveChangesBtn.querySelector('span').textContent = 'Saving...';
            }
            
            const formData = new FormData(profileForm);
            
            // Fix the URL path
            fetch('../user/save-profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Reset button state
                setTimeout(() => {
                    saveChangesBtn.classList.remove('saving', 'pulse');
                    if (saveSpinner) saveSpinner.style.display = 'none';
                    if (saveChangesBtn.querySelector('span')) {
                        saveChangesBtn.querySelector('span').textContent = 'Save Changes';
                    }
                }, 800);
                
                showAlert(data.success ? 'success' : 'danger', data.message);
                
                // If successful, update the session data
                if (data.success) {
                    // Update any session-dependent elements if needed
                    const firstName = document.getElementById('firstName').value;
                    const lastName = document.getElementById('lastName').value;
                    
                    // If we need to update any other parts of the page that depend on this data
                    const userNameElements = document.querySelectorAll('.user-name');
                    userNameElements.forEach(el => {
                        el.textContent = firstName + ' ' + lastName;
                    });
                }
            })
            .catch(error => {
                // Reset button state
                saveChangesBtn.classList.remove('saving', 'pulse');
                if (saveSpinner) saveSpinner.style.display = 'none';
                if (saveChangesBtn.querySelector('span')) {
                    saveChangesBtn.querySelector('span').textContent = 'Save Changes';
                }
                
                showAlert('danger', 'An error occurred. Please try again.');
                console.error('Error:', error);
            });
        }
        
        // Password update handler function
        function handlePasswordUpdate() {
            const formData = new FormData(passwordForm);
            
            // Fix the URL path
            fetch('../user/save-profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showAlert(data.success ? 'success' : 'danger', data.message);
                if (data.success) {
                    // Clear password fields on success
                    document.getElementById('currentPassword').value = '';
                    document.getElementById('newPassword').value = '';
                    document.getElementById('confirmPassword').value = '';
                }
            })
            .catch(error => {
                showAlert('danger', 'An error occurred. Please try again.');
                console.error('Error:', error);
            });
        }
    }
    
    // Setup image upload and removal functionality
    function setupImageHandlers() {
        const changePhotoBtn = document.getElementById('changePhotoBtn');
        const removePhotoBtn = document.getElementById('removePhotoBtn');
        const profileImageInput = document.getElementById('profileImageInput');
        const directFileInput = document.getElementById('directFileInput');
        const fallbackFileInput = document.getElementById('fallbackFileInput');
        const profileImageContainer = document.getElementById('profileImageContainer');
        
        // Setup change photo button
        if (changePhotoBtn) {
            console.log('Change photo button found');
            
            // Remove existing click handlers
            changePhotoBtn.onclick = null;
            
            // Add new click handler
            changePhotoBtn.onclick = function(e) {
                e.preventDefault();
                console.log('Change photo button clicked');
                
                // Try multiple approaches to open file selector
                if (profileImageInput && typeof profileImageInput.click === 'function') {
                    console.log('Using existing file input');
                    profileImageInput.click();
                } else if (directFileInput && typeof directFileInput.click === 'function') {
                    console.log('Using direct file input');
                    directFileInput.click();
                } else if (fallbackFileInput && typeof fallbackFileInput.click === 'function') {
                    console.log('Using fallback file input');
                    fallbackFileInput.click();
                } else {
                    console.log('Creating new file input');
                    const newInput = document.createElement('input');
                    newInput.type = 'file';
                    newInput.accept = 'image/*';
                    newInput.style.display = 'none';
                    document.body.appendChild(newInput);
                    
                    newInput.onchange = function() {
                        handleImageUpload(this);
                    };
                    
                    newInput.click();
                }
                
                return false;
            };
        }
        
        // Setup file input change handlers
        [profileImageInput, directFileInput, fallbackFileInput].forEach(input => {
            if (input) {
                input.onchange = function() {
                    handleImageUpload(this);
                };
            }
        });
        
        // Setup remove photo button
        if (removePhotoBtn) {
            removePhotoBtn.onclick = function(e) {
                e.preventDefault();
                handleImageRemoval();
                return false;
            };
        }
        
        // Image upload handler
        function handleImageUpload(input) {
            if (input.files && input.files[0]) {
                console.log('File selected:', input.files[0].name);
                
                // Show image preview before uploading
                const file = input.files[0];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    // Show preview
                    if (profileImageContainer) {
                        profileImageContainer.innerHTML = `<img src="${e.target.result}" alt="Profile Preview" style="width: 100%; height: 100%; object-fit: cover;">`;
                        
                        // Add preview indicator
                        const previewBadge = document.createElement('div');
                        previewBadge.style.position = 'absolute';
                        previewBadge.style.bottom = '0';
                        previewBadge.style.right = '0';
                        previewBadge.style.background = 'rgba(0,0,0,0.6)';
                        previewBadge.style.color = 'white';
                        previewBadge.style.padding = '3px 8px';
                        previewBadge.style.fontSize = '10px';
                        previewBadge.style.borderRadius = '8px 0 0 0';
                        previewBadge.textContent = 'Preview';
                        profileImageContainer.appendChild(previewBadge);
                    }
                    
                    // Show confirmation dialog
                    const confirmUpload = confirm('Upload this image as your profile picture?');
                    
                    if (confirmUpload) {
                        // Show loading state
                        if (changePhotoBtn) {
                            changePhotoBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
                            changePhotoBtn.disabled = true;
                        }
                        
                        const formData = new FormData();
                        formData.append('profile_image', file);
                        
                        // Fix the URL path - use the correct path to save-profile.php
                        fetch('../user/save-profile.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            console.log('Response received');
                            return response.json();
                        })
                        .then(data => {
                            console.log('Upload result:', data);
                            
                            // Reset button state
                            if (changePhotoBtn) {
                                changePhotoBtn.innerHTML = '<i class="fas fa-camera"></i> Change Photo';
                                changePhotoBtn.disabled = false;
                            }
                            
                            if (data.success) {
                                // Update profile image display (remove preview badge)
                                if (profileImageContainer) {
                                    // Use the full path returned from the server
                                    profileImageContainer.innerHTML = `<img src="../../${data.image_path}" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">`;
                                }
                                
                                // Also update the user avatar in the sidebar if it exists
                                updateUserAvatar(data.image_path);
                                
                                showAlert('success', data.message);
                            } else {
                                showAlert('danger', data.message);
                                // Restore initials if upload failed
                                restoreInitialsImage();
                            }
                        })
                        .catch(error => {
                            console.error('Upload error:', error);
                            
                            // Reset button state
                            if (changePhotoBtn) {
                                changePhotoBtn.innerHTML = '<i class="fas fa-camera"></i> Change Photo';
                                changePhotoBtn.disabled = false;
                            }
                            
                            showAlert('danger', 'An error occurred while uploading the image.');
                            // Restore initials if upload failed
                            restoreInitialsImage();
                        });
                    } else {
                        // User canceled, restore original image or initials
                        restoreInitialsImage();
                    }
                };
                
                reader.readAsDataURL(file);
            }
        }
        
        // Function to update user avatar in sidebar
        function updateUserAvatar(imagePath) {
            const userAvatar = document.querySelector('.user-avatar');
            if (userAvatar) {
                const firstName = document.getElementById('firstName')?.value || '';
                const lastName = document.getElementById('lastName')?.value || '';
                const initials = (firstName.charAt(0) + lastName.charAt(0)).toUpperCase();
                
                if (imagePath) {
                    userAvatar.innerHTML = `<img src="../../${imagePath}" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
                } else {
                    userAvatar.textContent = initials;
                }
            }
        }
        
        // Helper function to restore initials image if upload is canceled or fails
        function restoreInitialsImage() {
            const firstName = document.getElementById('firstName')?.value || '';
            const lastName = document.getElementById('lastName')?.value || '';
            const initials = (firstName.charAt(0) + lastName.charAt(0)).toUpperCase();
            
            // Check if there's an existing profile image
            const existingImg = document.querySelector('#profileImageContainer img');
            if (!existingImg && profileImageContainer) {
                profileImageContainer.innerHTML = initials;
            } else if (existingImg && existingImg.src.includes('data:image')) {
                // If it's a preview image (data URL), replace with initials
                profileImageContainer.innerHTML = initials;
            }
            // Otherwise keep the existing image
        }
        
        // Image removal handler
        function handleImageRemoval() {
            // Show loading state
            if (removePhotoBtn) {
                removePhotoBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Removing...';
                removePhotoBtn.disabled = true;
            }
            
            const formData = new FormData();
            formData.append('action', 'remove_image');
            
            // Fix the URL path
            fetch('../user/save-profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Reset button state
                if (removePhotoBtn) {
                    removePhotoBtn.innerHTML = '<i class="fas fa-trash"></i> Remove';
                    removePhotoBtn.disabled = false;
                }
                
                if (data.success) {
                    // Reset profile image to initials
                    const firstName = document.getElementById('firstName')?.value || '';
                    const lastName = document.getElementById('lastName')?.value || '';
                    const initials = (firstName.charAt(0) + lastName.charAt(0)).toUpperCase();
                    
                    if (profileImageContainer) {
                        profileImageContainer.innerHTML = initials;
                    }
                    
                    showAlert('success', data.message);
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                // Reset button state
                if (removePhotoBtn) {
                    removePhotoBtn.innerHTML = '<i class="fas fa-trash"></i> Remove';
                    removePhotoBtn.disabled = false;
                }
                
                showAlert('danger', 'An error occurred while removing the image.');
                console.error('Error:', error);
            });
        }
    }
    
    // Alert display function
    function showAlert(type, message) {
        const profileAlert = document.getElementById('profileAlert');
        if (!profileAlert) {
            console.error('Profile alert element not found');
            return;
        }
        
        profileAlert.className = 'alert alert-' + type;
        profileAlert.textContent = message;
        profileAlert.style.display = 'block';
        profileAlert.style.opacity = '0';
        
        // Fade in animation
        setTimeout(() => {
            profileAlert.style.transition = 'opacity 0.3s ease';
            profileAlert.style.opacity = '1';
        }, 10);
        
        // Scroll to alert
        profileAlert.scrollIntoView({ behavior: 'smooth', block: 'start' });
        
        // Hide alert after 5 seconds with fade out
        setTimeout(() => {
            profileAlert.style.opacity = '0';
            setTimeout(() => {
                profileAlert.style.display = 'none';
            }, 300);
        }, 5000);
    }
});
