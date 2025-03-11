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
                            // Hide the confirmation buttons
                            document.getElementById('modalConfirm').style.display = 'none';
                            document.getElementById('modalCancel').style.display = 'none';
                            document.getElementById('modalMessage').style.display = 'none';
                            document.getElementById('modalImagePreview').style.display = 'none';
                            
                            // Show loading state in modal
                            updateModalStatus('loading', 'Uploading image...');
                            
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
                                    
                                    // Show success in modal with confetti
                                    updateModalStatus('success', 'Profile image updated successfully!');
                                    startConfetti();
                                    
                                    // Reload the page after a short delay
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 2000);
                                } else {
                                    // Show error in modal
                                    updateModalStatus('error', data.message || 'Failed to upload image');
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
                    // Hide the confirmation buttons
                    document.getElementById('modalConfirm').style.display = 'none';
                    document.getElementById('modalCancel').style.display = 'none';
                    document.getElementById('modalMessage').style.display = 'none';
                    
                    // Show loading state in modal
                    updateModalStatus('loading', 'Removing image...');
                    
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
                            // Show success in modal with confetti
                            updateModalStatus('success', 'Profile image removed successfully!');
                            startConfetti();
                            
                            // Reload the page after a short delay
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        } else {
                            // Show error in modal
                            updateModalStatus('error', data.message || 'Failed to remove image');
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
        const modalContent = modal.querySelector('.modal-content');
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
        
        // Reset any animations
        const statusIcon = document.getElementById('statusIcon');
        const statusMessage = document.getElementById('statusMessage');
        statusIcon.style.transform = 'scale(0.5)';
        statusIcon.style.opacity = '0';
        statusMessage.style.opacity = '0';
        statusMessage.style.transform = 'translateY(10px)';
        
        // Hide progress bar
        const statusProgress = document.getElementById('statusProgress');
        const progressBar = document.getElementById('progressBar');
        statusProgress.style.display = 'none';
        progressBar.style.width = '0%';
        
        // Remove animation classes
        statusIcon.classList.remove('pulse-animation', 'success-animation', 'error-animation');
        
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
        
        // Show the modal with animation
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.style.opacity = '1';
            modalContent.style.transform = 'translateY(0)';
        }, 10);
        
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
        const modalContent = modal.querySelector('.modal-content');
        
        // Animate out
        modal.style.opacity = '0';
        modalContent.style.transform = 'translateY(20px)';
        
        // Hide after animation completes
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
    
    function updateModalStatus(type, message) {
        const modalStatus = document.getElementById('modalStatus');
        const statusIcon = document.getElementById('statusIcon');
        const statusMessage = document.getElementById('statusMessage');
        const statusProgress = document.getElementById('statusProgress');
        const progressBar = document.getElementById('progressBar');
        
        // Show status section
        modalStatus.style.display = 'block';
        
        // Set icon and color based on type
        if (type === 'success') {
            statusIcon.className = 'fas fa-check-circle';
            statusIcon.style.color = '#4CAF50';
            
            // Show progress bar for success (for page reload countdown)
            statusProgress.style.display = 'block';
            setTimeout(() => {
                progressBar.style.width = '100%';
            }, 50);
            
            // Add success animation class
            setTimeout(() => {
                statusIcon.classList.add('success-animation');
            }, 100);
        } else if (type === 'error') {
            statusIcon.className = 'fas fa-times-circle';
            statusIcon.style.color = '#F44336';
            
            // Add error animation class
            setTimeout(() => {
                statusIcon.classList.add('error-animation');
            }, 100);
        } else if (type === 'loading') {
            statusIcon.className = 'fas fa-spinner';
            statusIcon.style.color = '#d1789c';
            
            // Add loading animation
            setTimeout(() => {
                statusIcon.classList.add('pulse-animation');
            }, 100);
        }
        
        // Set message
        statusMessage.textContent = message;
        
        // Animate in the icon and message
        setTimeout(() => {
            statusIcon.style.opacity = '1';
            statusIcon.style.transform = 'scale(1)';
            
            setTimeout(() => {
                statusMessage.style.opacity = '1';
                statusMessage.style.transform = 'translateY(0)';
            }, 100);
        }, 50);
    }
    
    // Confetti animation
    function startConfetti() {
        const canvas = document.getElementById('confettiCanvas');
        if (!canvas) return;
        
        canvas.style.display = 'block';
        const ctx = canvas.getContext('2d');
        canvas.width = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;
        
        // Confetti colors
        const colors = ['#d1789c', '#e896b8', '#f5d7e3', '#6e3b5c', '#4CAF50', '#FFC107'];
        
        // Create confetti pieces
        const confetti = [];
        const confettiCount = 100;
        const gravity = 0.5;
        const terminalVelocity = 5;
        const drag = 0.075;
        
        // Initialize confetti
        for (let i = 0; i < confettiCount; i++) {
            confetti.push({
                color: colors[Math.floor(Math.random() * colors.length)],
                dimensions: {
                    x: Math.random() * 10 + 5,
                    y: Math.random() * 10 + 5
                },
                position: {
                    x: Math.random() * canvas.width,
                    y: -20 - Math.random() * 100
                },
                rotation: Math.random() * 2 * Math.PI,
                scale: {
                    x: 1,
                    y: 1
                },
                velocity: {
                    x: Math.random() * 25 - 12.5,
                    y: Math.random() * 15 + 5
                }
            });
        }
        
        // Render loop
        let animationFrame = null;
        const render = () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            confetti.forEach((confetto, index) => {
                let width = confetto.dimensions.x * confetto.scale.x;
                let height = confetto.dimensions.y * confetto.scale.y;
                
                // Move confetto
                confetto.velocity.x -= confetto.velocity.x * drag;
                confetto.velocity.y = Math.min(confetto.velocity.y + gravity, terminalVelocity);
                confetto.velocity.x += Math.random() > 0.5 ? Math.random() : -Math.random();
                
                confetto.position.x += confetto.velocity.x;
                confetto.position.y += confetto.velocity.y;
                
                // Spin confetto
                confetto.rotation += 0.01;
                
                // Draw confetto
                ctx.save();
                ctx.translate(confetto.position.x, confetto.position.y);
                ctx.rotate(confetto.rotation);
                
                ctx.fillStyle = confetto.color;
                ctx.fillRect(-width / 2, -height / 2, width, height);
                
                ctx.restore();
                
                // Remove confetti that fall off the screen
                if (confetto.position.y >= canvas.height) {
                    confetti.splice(index, 1);
                }
            });
            
            // Stop animation when all confetti are gone
            if (confetti.length > 0) {
                animationFrame = requestAnimationFrame(render);
            } else {
                cancelAnimationFrame(animationFrame);
                canvas.style.display = 'none';
            }
        };
        
        // Start animation
        render();
        
        // Stop animation after 3 seconds
        setTimeout(() => {
            if (animationFrame) {
                cancelAnimationFrame(animationFrame);
                canvas.style.display = 'none';
            }
        }, 3000);
    }
});
