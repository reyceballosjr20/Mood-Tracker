document.addEventListener('DOMContentLoaded', function() {
    // Get form elements
    const profileForm = document.getElementById('profileForm');
    const saveButton = document.getElementById('saveChangesBtn');
    
    // Store initial form values
    const initialFormState = {
        firstName: document.getElementById('firstName').value,
        lastName: document.getElementById('lastName').value,
        email: document.getElementById('email').value,
        bio: document.getElementById('bio').value
    };

    // Function to check if form has changed
    function hasFormChanged() {
        return (
            document.getElementById('firstName').value !== initialFormState.firstName ||
            document.getElementById('lastName').value !== initialFormState.lastName ||
            document.getElementById('email').value !== initialFormState.email ||
            document.getElementById('bio').value !== initialFormState.bio
        );
    }

    // Function to update save button state
    function updateSaveButtonState() {
        const hasChanges = hasFormChanged();
        saveButton.disabled = !hasChanges;
        
        // Update button styles based on state
        if (hasChanges) {
            saveButton.style.opacity = '1';
            saveButton.style.cursor = 'pointer';
            saveButton.style.background = 'linear-gradient(135deg, #d1789c, #e896b8)';
        } else {
            saveButton.style.opacity = '0.6';
            saveButton.style.cursor = 'not-allowed';
            saveButton.style.background = 'linear-gradient(135deg, #d1789c99, #e896b899)';
        }
    }

    // Add input event listeners to all form fields
    ['firstName', 'lastName', 'email', 'bio'].forEach(fieldId => {
        document.getElementById(fieldId).addEventListener('input', updateSaveButtonState);
    });

    // Initialize button state
    updateSaveButtonState();

    // Reset form state after successful save
    function updateInitialFormState() {
        initialFormState.firstName = document.getElementById('firstName').value;
        initialFormState.lastName = document.getElementById('lastName').value;
        initialFormState.email = document.getElementById('email').value;
        initialFormState.bio = document.getElementById('bio').value;
        updateSaveButtonState();
    }

    // Handle form submission
    saveButton.addEventListener('click', async function(e) {
        e.preventDefault();
        
        if (!hasFormChanged()) {
            return; // Don't submit if no changes
        }

        // Show spinner
        const spinner = document.getElementById('saveSpinner');
        spinner.style.display = 'block';
        saveButton.disabled = true;

        try {
            // Get form data
            const formData = new FormData(profileForm);
            
            // Add action parameter
            formData.append('action', 'update_profile');

            // Send request to server
            const response = await fetch('../save-profile.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            // Show alert
            const alertDiv = document.getElementById('profileAlert');
            alertDiv.style.display = 'block';
            
            if (result.success) {
                alertDiv.className = 'alert alert-success';
                alertDiv.textContent = 'Profile updated successfully!';
                updateInitialFormState(); // Update initial state after successful save
            } else {
                alertDiv.className = 'alert alert-danger';
                alertDiv.textContent = result.message || 'An error occurred while saving changes.';
            }

            // Hide alert after 3 seconds
            setTimeout(() => {
                alertDiv.style.display = 'none';
            }, 3000);

        } catch (error) {
            console.error('Error:', error);
            const alertDiv = document.getElementById('profileAlert');
            alertDiv.style.display = 'block';
            alertDiv.className = 'alert alert-danger';
            alertDiv.textContent = 'An error occurred while saving changes.';
        } finally {
            // Hide spinner and re-enable button
            spinner.style.display = 'none';
            saveButton.disabled = false;
        }
    });
});
