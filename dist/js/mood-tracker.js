/**
 * Mood Tracker JavaScript functionality
 * This file handles mood selection, inspirational messages, and mood data saving.
 */

// Main initialization function that will be called when content is loaded
function initMoodTracker() {
    console.log('Initializing Mood Tracker...');
    
    // Check if we're on the mood logging page
    const moodCircles = document.querySelectorAll('.mood-circle');
    const inspirationalMessage = document.getElementById('inspirationalMessage');
    
    if (!moodCircles.length || !inspirationalMessage) {
        console.log('Not on mood tracking page, skipping initialization');
        return; // Exit if we're not on the mood tracking page
    }
    
    let selectedMood = null;
    const moodInfluence = document.getElementById('moodInfluence');
    const saveMoodBtn = document.getElementById('saveMoodBtn');
    
    console.log('Mood tracker elements found:', {
        'moodCircles': moodCircles.length,
        'inspirationalMessage': !!inspirationalMessage,
        'moodInfluence': !!moodInfluence,
        'saveMoodBtn': !!saveMoodBtn
    });
    
    // Inspirational messages for each mood type
    const moodMessages = {
        sad: [
            "It's okay to feel sad. Every emotion is a valid part of your journey.",
            "Sometimes the darkest times can teach us the most about our own strength.",
            "Your feelings are valid. Take the time you need to process them.",
            "This sadness, too, shall pass. Be gentle with yourself today."
        ],
        unhappy: [
            "Even on cloudy days, the sun is still there behind the clouds.",
            "Every moment is temporary, including this feeling of unhappiness.",
            "Small steps forward are still progress. What's one tiny positive thing you can focus on?",
            "Your worth isn't determined by how you feel right now."
        ],
        neutral: [
            "Neutral moments are perfect for reflection and mindfulness.",
            "In this balanced state, you can choose your next direction with clarity.",
            "Sometimes 'okay' is perfectly fine. Not every day needs to be extraordinary.",
            "A neutral mood is like a clean canvas - what would you like to create today?"
        ],
        good: [
            "A good mood is something to savor. Take a moment to appreciate it.",
            "This positive feeling is a resource - how can you share it with others?",
            "Noticing what brings you to this good place helps you return here again.",
            "Good moments accumulate into a good life. You're on the right track."
        ],
        energetic: [
            "Channel this energy into something meaningful today!",
            "Your enthusiasm can move mountains. What will you accomplish?",
            "This vibrant energy is perfect for tackling challenges head-on.",
            "Ride this wave of motivation and see where it takes you!"
        ],
        excellent: [
            "Wonderful! Take a moment to appreciate feeling at your best.",
            "This excellent feeling is a reminder of what's possible in your life.",
            "Your positive energy right now can inspire others around you.",
            "Celebrate this peak feeling and remember it during challenging times."
        ],
        anxious: [
            "Take a deep breath. You've handled difficult feelings before, and you'll handle this too.",
            "Anxiety is your body's way of trying to protect you. Thank it, then let it know you're safe.",
            "Focus on what you can control right now, even if it's just your breath.",
            "This anxious feeling is temporary, not permanent. It will pass."
        ],
        tired: [
            "Rest is not lazy - it's essential. Give yourself permission to recharge.",
            "Your body is telling you something important. Listen with compassion.",
            "Even small moments of rest can help restore your energy.",
            "Honor your limits today. It's okay to slow down when needed."
        ],
        focused: [
            "Your concentration right now is a powerful tool. Use it wisely!",
            "This focused state is when great things happen. What will you accomplish?",
            "Being in the zone like this is one of life's most fulfilling experiences.",
            "Your clarity and concentration can move mountains today."
        ]
    };
    
    // Function to update the inspirational message
    function updateInspirationalMessage(mood) {
        console.log(`Updating message for mood: ${mood}`);
        
        if (!moodMessages[mood]) {
            console.error(`No messages found for mood: ${mood}`);
            return;
        }
        
        // Fade out current message
        inspirationalMessage.style.opacity = '0';
        
        // Wait for fade out to complete, then update and fade in
        setTimeout(() => {
            // Get a random message for the selected mood
            const messages = moodMessages[mood];
            const randomIndex = Math.floor(Math.random() * messages.length);
            const newMessage = messages[randomIndex];
            
            console.log(`Selected message index ${randomIndex} for mood ${mood}: "${newMessage}"`);
            
            // Update message content
            inspirationalMessage.innerHTML = `
                <p style="font-size: 1.1rem; line-height: 1.6; color: #8a5878; font-style: italic;">"${newMessage}"</p>
                <div style="margin-top: 15px; font-size: 0.9rem; color: #d1789c; font-weight: 600;">${mood.toUpperCase()}</div>
            `;
            
            // Add animation class
            inspirationalMessage.classList.add('message-animation');
            
            // Fade in new message
            inspirationalMessage.style.opacity = '1';
            console.log('New message displayed and faded in');
            
            // Remove animation class after animation completes
            setTimeout(() => {
                inspirationalMessage.classList.remove('message-animation');
                console.log('Animation class removed');
            }, 500);
        }, 300);
    }

    // Handle mood selection
    moodCircles.forEach(circle => {
        circle.addEventListener('click', function() {
            const selectedMoodType = this.dataset.mood;
            console.log(`Mood selected: ${selectedMoodType}`);
            
            // Remove selected class from all circles
            moodCircles.forEach(c => {
                if (c.classList.contains('selected')) {
                    console.log(`Removing selected class from: ${c.dataset.mood}`);
                }
                c.classList.remove('selected');
            });
            
            // Add selected class to clicked circle
            this.classList.add('selected');
            console.log(`Added selected class to: ${selectedMoodType}`);
            selectedMood = selectedMoodType;
            
            // Update inspirational message based on selected mood
            updateInspirationalMessage(selectedMood);
        });
    });

    // Handle save button click
    saveMoodBtn.addEventListener('click', async function() {
        if (!selectedMood) {
            alert('Please select a mood first');
            return;
        }

        try {
            const response = await fetch('save-mood.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    mood_type: selectedMood,
                    mood_text: moodInfluence.value
                })
            });

            const data = await response.json();
            
            if (data.success) {
                // Show success message
                const successMsg = document.createElement('div');
                successMsg.style.position = 'fixed';
                successMsg.style.top = '20px';
                successMsg.style.right = '20px';
                successMsg.style.backgroundColor = '#4CAF50';
                successMsg.style.color = 'white';
                successMsg.style.padding = '15px 20px';
                successMsg.style.borderRadius = '8px';
                successMsg.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
                successMsg.style.zIndex = '1000';
                successMsg.style.transition = 'opacity 0.5s ease-in-out';
                successMsg.innerHTML = '<strong>Success!</strong> Your mood has been logged.';
                
                document.body.appendChild(successMsg);
                
                // Remove the message after 3 seconds
                setTimeout(() => {
                    successMsg.style.opacity = '0';
                    setTimeout(() => {
                        successMsg.remove();
                    }, 500);
                }, 3000);
                
                // Reset form
                moodCircles.forEach(c => c.classList.remove('selected'));
                moodInfluence.value = '';
                selectedMood = null;
                
                // Reset inspirational message
                inspirationalMessage.style.opacity = '0';
                setTimeout(() => {
                    inspirationalMessage.innerHTML = `
                        <p style="font-size: 1.1rem; line-height: 1.6; color: #8a5878; font-style: italic;">"Select a mood to see a personalized message"</p>
                    `;
                    inspirationalMessage.style.opacity = '1';
                }, 300);
                
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            alert('Error saving mood: ' + error.message);
        }
    });
    
    console.log('Mood tracker initialization complete');
}

// Event listener for content loaded via AJAX
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded');
    // Initialize immediately if we're already on the page
    initMoodTracker();
});

// This function will be called when dynamic content is loaded in dashboard
function reinitMoodTracker() {
    console.log('Reinitializing mood tracker for dynamically loaded content');
    initMoodTracker();
} 