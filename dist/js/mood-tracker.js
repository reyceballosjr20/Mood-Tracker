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
    const inspirationalMessageDiv = document.getElementById('inspirationalMessage');
    
    if (!moodCircles.length || !saveMoodBtn) {
        console.log('Required mood tracker elements not found');
        return; // Exit if elements aren't found
    }
    
    let selectedMood = null;
    
    // Collection of mood-specific inspirational messages
    const moodMessages = {
        'sad': [
            "It's okay to feel down. This feeling is temporary, and brighter days are ahead.",
            "Your strength is measured by how you rise after falling. Take your time.",
            "Even the darkest night will end and the sun will rise again."
        ],
        'unhappy': [
            "Every emotion has a purpose. Listen to what this feeling is telling you.",
            "This too shall pass. Tomorrow brings new opportunities.",
            "Small steps forward still move you in the right direction."
        ],
        'neutral': [
            "You are surrounded by peace, and everything is unfolding as it should.",
            "Balance is the key to harmony. You're in a good place to reflect.",
            "Sometimes the middle path is the wisest. Take time to center yourself."
        ],
        'good': [
            "Your positive energy is contagious. Share your light with others today!",
            "Happiness looks beautiful on you. Savor this feeling.",
            "You're doing great! Keep that positive momentum going."
        ],
        'energetic': [
            "Channel that amazing energy into something you love today!",
            "You're unstoppable when you're in this zone. Make the most of it!",
            "Your vibrant energy can move mountains. What will you accomplish today?"
        ],
        'excellent': [
            "Wonderful! Your joy radiates and inspires those around you.",
            "This feeling is what life is all about. Treasure these moments.",
            "You deserve this happiness. Celebrate yourself today!"
        ],
        'anxious': [
            "Breathe deeply. You are safe, and this feeling will pass.",
            "Focus on what you can control, and let go of what you cannot.",
            "Your anxiety means you care deeply. Be gentle with yourself."
        ],
        'tired': [
            "Rest is not a luxury, it's essential. Give yourself permission to recharge.",
            "Even the strongest need to rest. Your body is telling you something important.",
            "Tomorrow's strength comes from today's rest. Honor what your body needs."
        ],
        'focused': [
            "Your concentration is your superpower. Use it wisely today.",
            "Great things happen when you channel your focus like this.",
            "When mind and purpose align, amazing things can happen."
        ]
    };
    
    // Default message
    const defaultMessage = "Select a mood to see a personalized message";
    
    // Log debug info to help diagnose the issue
    console.log('Found mood circles:', moodCircles.length);
    console.log('Found inspirational message div:', !!inspirationalMessageDiv);
    
    // Set up click handlers for mood circles
    moodCircles.forEach(circle => {
        circle.addEventListener('click', function() {
            console.log('Mood circle clicked:', this.getAttribute('data-mood'));
            
            // Remove selected class from all circles
            moodCircles.forEach(c => c.classList.remove('selected'));
            
            // Add selected class to clicked circle
            this.classList.add('selected');
            
            // Store selected mood
            selectedMood = this.getAttribute('data-mood');
            
            // Update inspirational message based on selected mood
            if (inspirationalMessageDiv && moodMessages[selectedMood] && moodMessages[selectedMood].length > 0) {
                // Get random message for this mood
                const randomIndex = Math.floor(Math.random() * moodMessages[selectedMood].length);
                const message = moodMessages[selectedMood][randomIndex];
                
                console.log('Updating message to:', message);
                
                // Get the paragraph inside the div
                const messageParagraph = inspirationalMessageDiv.querySelector('p');
                
                // If paragraph exists, update it, otherwise create a new one
                if (messageParagraph) {
                    // Fade out, update text, fade in
                    inspirationalMessageDiv.style.opacity = '0';
                    setTimeout(() => {
                        messageParagraph.textContent = `"${message}"`;
                        inspirationalMessageDiv.style.transition = 'opacity 0.5s ease';
                        inspirationalMessageDiv.style.opacity = '1';
                    }, 300);
                } else {
                    // Create paragraph if it doesn't exist
                    inspirationalMessageDiv.innerHTML = `<p style="font-size: 1.1rem; line-height: 1.6; color: #8a5878; font-style: italic;">"${message}"</p>`;
                    inspirationalMessageDiv.style.opacity = '0';
                    setTimeout(() => {
                        inspirationalMessageDiv.style.transition = 'opacity 0.5s ease';
                        inspirationalMessageDiv.style.opacity = '1';
                    }, 100);
                }
            } else if (inspirationalMessageDiv) {
                // Fallback for any mood that might not have messages
                console.warn('No messages found for mood:', selectedMood);
                
                // Get the paragraph inside the div
                const messageParagraph = inspirationalMessageDiv.querySelector('p');
                
                if (messageParagraph) {
                    inspirationalMessageDiv.style.opacity = '0';
                    setTimeout(() => {
                        messageParagraph.textContent = `"Your ${selectedMood} feelings are valid and important."`;
                        inspirationalMessageDiv.style.transition = 'opacity 0.5s ease';
                        inspirationalMessageDiv.style.opacity = '1';
                    }, 300);
                } else {
                    inspirationalMessageDiv.innerHTML = `<p style="font-size: 1.1rem; line-height: 1.6; color: #8a5878; font-style: italic;">"Your ${selectedMood} feelings are valid and important."</p>`;
                }
            }
        });
    });
    
    // Handle save mood button click
    saveMoodBtn.addEventListener('click', function() {
        if (!selectedMood) {
            alert('Please select how you are feeling today');
            return;
        }
        
        const moodInfluence = document.getElementById('moodInfluence');
        if (!moodInfluence) {
            console.error('Mood influence textarea not found');
            return;
        }
        
        // Show loading indicator
        saveMoodBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        saveMoodBtn.disabled = true;
        
        // Create form data
        const formData = new FormData();
        formData.append('action', 'save_mood');
        formData.append('mood_type', selectedMood);
        formData.append('mood_text', moodInfluence.value);
        
        console.log('Saving mood:', selectedMood, 'Text:', moodInfluence.value);
        
        // Send to server - using the current URL to ensure proper path resolution
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Save response:', data);
            if (data.success) {
                // Success message
                alert('Your mood has been saved!');
                
                // Reset form
                moodCircles.forEach(c => c.classList.remove('selected'));
                moodInfluence.value = '';
                
                // Reset message to default
                if (inspirationalMessageDiv) {
                    const messageParagraph = inspirationalMessageDiv.querySelector('p');
                    if (messageParagraph) {
                        inspirationalMessageDiv.style.opacity = '0';
                        setTimeout(() => {
                            messageParagraph.textContent = `"${defaultMessage}"`;
                            inspirationalMessageDiv.style.opacity = '1';
                        }, 300);
                    }
                }
                
                selectedMood = null;
                
                // Use window.parent to access the parent window for navigation
                try {
                    if (window.parent && window.parent.loadPage) {
                        // If using the parent's loadPage function
                        window.parent.loadPage('dashboard');
                    } else {
                        // Alternative: find and click the dashboard menu link in the parent window
                        const dashboardLink = window.parent.document.querySelector('.menu-link[data-page="dashboard"]');
                        if (dashboardLink) {
                            dashboardLink.click();
                        } else {
                            console.log('Dashboard link not found, staying on current page');
                        }
                    }
                } catch (e) {
                    console.error('Error navigating to dashboard:', e);
                    // If we can't navigate, just stay on the current page
                }
            } else {
                alert('Error: ' + (data.message || 'Failed to save mood'));
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