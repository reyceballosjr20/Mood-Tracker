/**
 * Mood Tracker JavaScript
 * Handles mood selection, inspirational messages, and saving to database
 */
 
document.addEventListener('DOMContentLoaded', function() {
    initMoodTracker();
});

/**
 * Initialize the mood tracker functionality
 * This can be called when the page loads or when content is dynamically loaded
 */
function initMoodTracker() {
    const moodCircles = document.querySelectorAll('.mood-circle');
    const saveMoodBtn = document.getElementById('saveMoodBtn');
    
    if (!moodCircles.length || !saveMoodBtn) return; // Exit if elements aren't found
    
    let selectedMood = null;
    
    // Define mood-specific inspirational messages
    const moodMessages = {
        'sad': "It's okay not to be okay. Remember that this feeling is temporary, and brighter days are ahead.",
        'unhappy': "Every cloud has a silver lining. Take a deep breath and remember your strength.",
        'neutral': "You are exactly where you need to be. Embrace the moment and find peace in balance.",
        'good': "You're doing great! Celebrate the positive energy you're feeling today.",
        'energetic': "Channel your energy into something meaningful today. You have the power to accomplish anything!",
        'excellent': "What a wonderful day to be alive! Your positive energy can inspire those around you.",
        'anxious': "Take a deep breath. Focus on what you can control, and let go of what you cannot.",
        'tired': "Rest is not a luxury, it's a necessity. Give yourself permission to recharge.",
        'focused': "Your concentration is your superpower today. You are in the perfect state to achieve your goals."
    };
    
    // Default message
    const defaultMessage = "You are surrounded by peace, and everything is unfolding as it should";
    
    // Log debug info to help diagnose the issue
    console.log('Found mood circles:', moodCircles.length);
    
    // Set up click handlers for mood circles
    moodCircles.forEach(circle => {
        circle.addEventListener('click', function() {
            console.log('Mood circle clicked:', this.dataset.mood);
            
            // Remove selected class from all circles
            moodCircles.forEach(c => c.classList.remove('selected'));
            
            // Add selected class to clicked circle
            this.classList.add('selected');
            
            // Store selected mood
            selectedMood = this.dataset.mood;
            
            // Get the message element at click time (more reliable)
            const inspirationalMessageDiv = getInspirationalMessageElement();
            console.log('Message div found:', !!inspirationalMessageDiv);
            
            // Update inspirational message based on selected mood
            if (inspirationalMessageDiv && moodMessages[selectedMood]) {
                console.log('Updating message to:', moodMessages[selectedMood]);
                
                inspirationalMessageDiv.innerHTML = `<p style="font-size: 1.1rem; line-height: 1.6; color: #8a5878; font-style: italic;">"${moodMessages[selectedMood]}"</p>`;
                
                // Add a subtle animation
                inspirationalMessageDiv.style.opacity = '0';
                setTimeout(() => {
                    inspirationalMessageDiv.style.transition = 'opacity 0.5s ease';
                    inspirationalMessageDiv.style.opacity = '1';
                }, 100);
            }
        });
    });
    
    // Handle save mood button click
    saveMoodBtn.addEventListener('click', function() {
        if (!selectedMood) {
            alert('Please select how you are feeling today');
            return;
        }
        
        const moodInfluence = document.getElementById('moodInfluence').value;
        
        // Show loading indicator
        saveMoodBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        saveMoodBtn.disabled = true;
        
        // Create form data
        const formData = new FormData();
        formData.append('action', 'save_mood');
        formData.append('mood_type', selectedMood);
        formData.append('mood_text', moodInfluence);
        
        // Send to server - using the correct path for dynamically loaded content
        fetch('content/log-mood.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Success message
                alert('Your mood has been saved!');
                
                // Update dashboard values if possible
                updateDashboardWithNewMood(selectedMood);
                
                // Reset form
                moodCircles.forEach(c => c.classList.remove('selected'));
                document.getElementById('moodInfluence').value = '';
                selectedMood = null;
                
                // Reset message to default
                if (inspirationalMessageDiv) {
                    inspirationalMessageDiv.innerHTML = `<p style="font-size: 1.1rem; line-height: 1.6; color: #8a5878; font-style: italic;">"${defaultMessage}"</p>`;
                }
                
                // Redirect to dashboard
                const dashboardLink = document.querySelector('.menu-link[data-page="dashboard"]');
                if (dashboardLink) {
                    dashboardLink.click();
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving your mood');
        })
        .finally(() => {
            // Reset button
            saveMoodBtn.innerHTML = 'SAVE';
            saveMoodBtn.disabled = false;
        });
    });
}

/**
 * Update dashboard elements with the newly saved mood
 * @param {string} mood - The selected mood type
 */
function updateDashboardWithNewMood(mood) {
    try {
        // Get mood icon
        let icon = '';
        const moodIcons = {
            'sad': '<i class="fas fa-sad-tear"></i>',
            'unhappy': '<i class="fas fa-frown"></i>',
            'neutral': '<i class="fas fa-meh"></i>',
            'good': '<i class="fas fa-smile"></i>',
            'energetic': '<i class="fas fa-dumbbell"></i>',
            'excellent': '<i class="fas fa-laugh-beam"></i>',
            'anxious': '<i class="fas fa-bolt"></i>',
            'tired': '<i class="fas fa-bed"></i>',
            'focused': '<i class="fas fa-bullseye"></i>'
        };
        
        icon = moodIcons[mood] || '<i class="fas fa-smile"></i>';
        
        // Update current mood card if it exists on the dashboard
        const currentMoodContent = document.querySelector('.dashboard-cards .card:first-child .card-content');
        if (currentMoodContent) {
            currentMoodContent.innerHTML = `<span style="font-size: 24px; margin-right: 10px;">${icon}</span> ${mood.charAt(0).toUpperCase() + mood.slice(1)}`;
        }
        
        // Update timestamp
        const currentMoodFooter = document.querySelector('.dashboard-cards .card:first-child .card-footer');
        if (currentMoodFooter) {
            const now = new Date();
            const timeString = now.toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: 'numeric',
                hour12: true
            });
            currentMoodFooter.textContent = `Last updated ${timeString}`;
        }
        
        // Increment streak if it exists
        const streakElement = document.querySelector('.dashboard-cards .card:nth-child(2) .card-content');
        if (streakElement) {
            const currentStreak = parseInt(streakElement.textContent);
            if (!isNaN(currentStreak)) {
                streakElement.textContent = (currentStreak + 1) + ' Days';
            }
        }
    } catch (error) {
        console.error('Error updating dashboard:', error);
    }
}

// Update the getInspirationalMessageElement function to be more robust
function getInspirationalMessageElement() {
    // Try the ID first (most reliable)
    const messageEl = document.getElementById('inspirationalMessage');
    
    if (messageEl) return messageEl;
    
    // Fall back to the previous selectors if ID isn't found
    // Try multiple selector strategies to find the message container
    messageEl = document.querySelector('.card[style*="background-color: #f5d7e3"] div div');
    
    if (!messageEl) {
        // Fallback to other potential selectors
        messageEl = document.querySelector('.card[style*="background-color: #f5d7e3"] div');
    }
    
    if (!messageEl) {
        // Try a different approach - get all cards and find the pink one
        const cards = document.querySelectorAll('.card');
        for (const card of cards) {
            if (card.style.backgroundColor === '#f5d7e3' || 
                window.getComputedStyle(card).backgroundColor === 'rgb(245, 215, 227)') {
                // Found the right card, now get its inner message div
                messageEl = card.querySelector('div div') || card.querySelector('div');
                break;
            }
        }
    }
    
    return messageEl;
}

// Make the initialization function available globally
window.initMoodTracker = initMoodTracker; 