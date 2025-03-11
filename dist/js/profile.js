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
                    // Show preview in modal
                    showModal({
                        title: 'Upload Profile Picture',
                        message: 'Do you want to use this image as your profile picture?',
                        showPreview: true,
                        previewSrc: e.target.result,
                        onConfirm: () => {
                            // Show loading state in modal
                            updateModalStatus('loading', 'Uploading image...');
                            
                            // Disable buttons during upload
                            document.getElementById('modalConfirm').disabled = true;
                            document.getElementById('modalCancel').disabled = true;
                            
                            // Also update button state
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
                                    // Update profile image display
                                    if (profileImageContainer) {
                                        // Use the full path returned from the server
                                        profileImageContainer.innerHTML = `<img src="../${data.image_path}" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">`;
                                        console.log('Updated image path:', data.image_path);
                                    }
                                    
                                    // Also update the user avatar in the sidebar if it exists
                                    updateUserAvatar(data.image_path);
                                    
                                    // Show success in modal
                                    updateModalStatus('success', 'Profile image updated successfully!');
                                    
                                    // Reload the page after a short delay
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1500);
                                } else {
                                    // Show error in modal
                                    updateModalStatus('error', data.message || 'Failed to upload image');
                                    
                                    // Restore initials if upload failed
                                    restoreInitialsImage();
                                    
                                    // Re-enable buttons
                                    document.getElementById('modalConfirm').disabled = false;
                                    document.getElementById('modalCancel').disabled = false;
                                }
                            })
                            .catch(error => {
                                console.error('Upload error:', error);
                                
                                // Reset button state
                                if (changePhotoBtn) {
                                    changePhotoBtn.innerHTML = '<i class="fas fa-camera"></i> Change Photo';
                                    changePhotoBtn.disabled = false;
                                }
                                
                                // Show error in modal
                                updateModalStatus('error', 'An error occurred while uploading the image.');
                                
                                // Restore initials if upload failed
                                restoreInitialsImage();
                                
                                // Re-enable buttons
                                document.getElementById('modalConfirm').disabled = false;
                                document.getElementById('modalCancel').disabled = false;
                            });
                        },
                        onCancel: () => {
                            // User canceled, restore original image or initials
                            restoreInitialsImage();
                        }
                    });
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
                    // Update the avatar with the new image - use the correct path
                    userAvatar.innerHTML = `<img src="../${imagePath}" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
                    
                    // Also update any other instances of the user avatar on the page
                    const otherAvatars = document.querySelectorAll('.user-avatar:not(:first-child)');
                    otherAvatars.forEach(avatar => {
                        avatar.innerHTML = `<img src="../${imagePath}" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
                    });
                } else {
                    userAvatar.textContent = initials;
                    
                    // Also update any other instances of the user avatar on the page
                    const otherAvatars = document.querySelectorAll('.user-avatar:not(:first-child)');
                    otherAvatars.forEach(avatar => {
                        avatar.textContent = initials;
                    });
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
            showModal({
                title: 'Remove Profile Picture',
                message: 'Are you sure you want to remove your profile picture?',
                onConfirm: () => {
                    // Show loading state in modal
                    updateModalStatus('loading', 'Removing image...');
                    
                    // Disable buttons during removal
                    document.getElementById('modalConfirm').disabled = true;
                    document.getElementById('modalCancel').disabled = true;
                    
                    // Also update button state
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
                            // Show success in modal
                            updateModalStatus('success', 'Profile image removed successfully!');
                            
                            // Reload the page after a short delay
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            // Show error in modal
                            updateModalStatus('error', data.message || 'Failed to remove image');
                            
                            // Re-enable buttons
                            document.getElementById('modalConfirm').disabled = false;
                            document.getElementById('modalCancel').disabled = false;
                        }
                    })
                    .catch(error => {
                        // Reset button state
                        if (removePhotoBtn) {
                            removePhotoBtn.innerHTML = '<i class="fas fa-trash"></i> Remove';
                            removePhotoBtn.disabled = false;
                        }
                        
                        // Show error in modal
                        updateModalStatus('error', 'An error occurred while removing the image.');
                        
                        // Re-enable buttons
                        document.getElementById('modalConfirm').disabled = false;
                        document.getElementById('modalCancel').disabled = false;
                        
                        console.error('Error:', error);
                    });
                }
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
    
    // Modal functions
    function showModal(options) {
        const modal = document.getElementById('profileModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');
        const modalImagePreview = document.getElementById('modalImagePreview');
        const previewImage = document.getElementById('previewImage');
        const modalConfirm = document.getElementById('modalConfirm');
        const modalCancel = document.getElementById('modalCancel');
        const modalClose = document.getElementById('modalClose');
        const modalStatus = document.getElementById('modalStatus');
        
        // Reset modal state
        modalStatus.style.display = 'none';
        modalImagePreview.style.display = 'none';
        modalConfirm.disabled = false;
        modalCancel.disabled = false;
        
        // Set modal content
        modalTitle.textContent = options.title || 'Confirmation';
        modalMessage.textContent = options.message || 'Are you sure you want to proceed?';
        
        // Show image preview if needed
        if (options.showPreview && options.previewSrc) {
            previewImage.src = options.previewSrc;
            modalImagePreview.style.display = 'block';
        }
        
        // Set up event handlers
        modalConfirm.onclick = () => {
            if (typeof options.onConfirm === 'function') {
                options.onConfirm();
            } else {
                hideModal();
            }
        };
        
        modalCancel.onclick = () => {
            if (typeof options.onCancel === 'function') {
                options.onCancel();
            }
            hideModal();
        };
        
        modalClose.onclick = () => {
            if (typeof options.onCancel === 'function') {
                options.onCancel();
            }
            hideModal();
        };
        
        // Show the modal
        modal.style.display = 'flex';
        
        // Add event listener to close modal when clicking outside
        modal.onclick = (e) => {
            if (e.target === modal) {
                if (typeof options.onCancel === 'function') {
                    options.onCancel();
                }
                hideModal();
            }
        };
    }
    
    function hideModal() {
        const modal = document.getElementById('profileModal');
        modal.style.display = 'none';
    }
    
    function updateModalStatus(type, message) {
        const modalStatus = document.getElementById('modalStatus');
        const statusIcon = document.getElementById('statusIcon');
        const statusMessage = document.getElementById('statusMessage');
        
        // Set icon and color based on type
        if (type === 'success') {
            statusIcon.className = 'fas fa-check-circle';
            statusIcon.style.color = '#4CAF50';
        } else if (type === 'error') {
            statusIcon.className = 'fas fa-times-circle';
            statusIcon.style.color = '#F44336';
        } else if (type === 'loading') {
            statusIcon.className = 'fas fa-spinner fa-spin';
            statusIcon.style.color = '#d1789c';
        }
        
        // Set message
        statusMessage.textContent = message;
        
        // Show status section
        modalStatus.style.display = 'block';
    }
});
