/**
 * Calendar functionality for the mood tracker application
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Calendar script loaded');
    
    // Initialize calendar functionality
    initCalendar();
    
    // Also add a listener for dynamic content changes
    // This helps with single-page applications where content is loaded dynamically
    setupContentChangeDetection();
});

/**
 * Initialize the calendar functionality
 */
function initCalendar() {
    console.log('Attempting to initialize calendar');
    
    // Check if calendar elements exist before initializing
    const hasCalendarElements = document.querySelector('.month-calendar');
    
    if (!hasCalendarElements) {
        console.log('No calendar elements found, skipping initialization');
        return;
    }
    
    console.log('Calendar elements found, setting up functionality');
    
    // Removed month navigation setup since we're showing all 12 months
    
    // Add tooltip functionality for mood entries
    setupMoodTooltips();
    
    // Handle calendar view options
    setupViewOptions();
    
    // Add mood-specific styling
    setupMoodColors();
    
    console.log('Calendar initialization complete');
}

/**
 * Set up the month navigation functionality
 * This function is kept for backward compatibility but doesn't do anything in the all-months view
 */
function setupMonthNavigation() {
    console.log('Month navigation disabled in all-months view');
    // No navigation in all-months view
}

/**
 * Fetch new calendar content - using a different function name to avoid confusion
 * This function is kept for backward compatibility but redirects to the calendar page in the all-months view
 */
function fetchNewCalendar(month, year) {
    console.log('Redirecting to calendar page in all-months view');
    window.location.href = 'dashboard.php?page=calendar';
}

// Override the original function to use our new approach
function navigateToMonth(month, year) {
    window.location.href = 'dashboard.php?page=calendar';
}

/**
 * Set up tooltips for mood entries
 */
function setupMoodTooltips() {
    const moodEntries = document.querySelectorAll('.mood-entry');
    
    moodEntries.forEach(entry => {
        const moodType = entry.querySelector('.calendar-mood-icon').dataset.mood;
        
        entry.addEventListener('mouseenter', function() {
            const tooltip = this.querySelector('.mood-tooltip');
            if (tooltip) {
                tooltip.style.opacity = '1';
                tooltip.style.visibility = 'visible';
                
                // Add mood-specific styling to tooltip
                tooltip.style.borderLeft = `4px solid var(--mood-${moodType}-color, #d1789c)`;
            }
        });
        
        entry.addEventListener('mouseleave', function() {
            const tooltip = this.querySelector('.mood-tooltip');
            if (tooltip) {
                tooltip.style.opacity = '0';
                tooltip.style.visibility = 'hidden';
            }
        });
    });
}

/**
 * Set up calendar view options
 */
function setupViewOptions() {
    // Future enhancement: Add ability to toggle between different calendar views
    // (e.g., monthly, weekly, daily)
}

// Add new function to handle mood-specific styling
function setupMoodColors() {
    const moodColors = {
        happy: '#ffd6a5',
        sad: '#a5c4ff',
        angry: '#ffa5a5',
        anxious: '#a5e6ff',
        stressed: '#ffe6a5',
        calm: '#a5ffd6',
        tired: '#e6e6e6',
        energetic: '#ffd6e6',
        neutral: '#f0f0f0',
        excited: '#ffb366',
        frustrated: '#ff8080',
        grateful: '#b3ffb3'
    };

    // Apply hover effects for mood entries
    const moodEntries = document.querySelectorAll('.mood-entry');
    moodEntries.forEach(entry => {
        const moodIcon = entry.querySelector('.calendar-mood-icon');
        if (moodIcon && moodIcon.dataset && moodIcon.dataset.mood) {
            const moodType = moodIcon.dataset.mood;
            if (moodType && moodColors[moodType]) {
                entry.addEventListener('mouseenter', function() {
                    this.querySelector('.calendar-mood-icon').style.transform = 'scale(1.1)';
                    this.querySelector('.calendar-mood-icon').style.boxShadow = 
                        `0 4px 8px ${moodColors[moodType]}80`; // 80 is for 50% opacity
                });

                entry.addEventListener('mouseleave', function() {
                    this.querySelector('.calendar-mood-icon').style.transform = 'scale(1)';
                    this.querySelector('.calendar-mood-icon').style.boxShadow = 
                        '0 2px 5px rgba(209, 120, 156, 0.2)';
                });
            }
        }
    });
}

// Add CSS variables for mood colors
document.head.insertAdjacentHTML('beforeend', `
    <style>
        :root {
            --mood-happy-color: #ffd6a5;
            --mood-sad-color: #a5c4ff;
            --mood-angry-color: #ffa5a5;
            --mood-anxious-color: #a5e6ff;
            --mood-stressed-color: #ffe6a5;
            --mood-calm-color: #a5ffd6;
            --mood-tired-color: #e6e6e6;
            --mood-energetic-color: #ffd6e6;
            --mood-neutral-color: #f0f0f0;
            --mood-excited-color: #ffb366;
            --mood-frustrated-color: #ff8080;
            --mood-grateful-color: #b3ffb3;
        }
        
        .calendar-mood-icon {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .mood-tooltip {
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
        }
    </style>
`);

/**
 * Set up detection for dynamic content changes
 */
function setupContentChangeDetection() {
    // Create a mutation observer to watch for content container changes
    try {
        const contentContainer = document.getElementById('contentContainer') || 
                               document.getElementById('content') || 
                               document.querySelector('.content-container');
        
        if (contentContainer) {
            console.log('Setting up mutation observer for calendar content');
            
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                        // Check if calendar elements are present in the new content
                        const hasCalendarElements = contentContainer.querySelector('.month-calendar');
                        
                        if (hasCalendarElements) {
                            console.log('Calendar elements detected in new content, initializing calendar');
                            initCalendar();
                        }
                    }
                });
            });
            
            // Start observing the content container for DOM changes
            observer.observe(contentContainer, { 
                childList: true,
                subtree: true 
            });
        }
    } catch (error) {
        console.error('Error setting up content change detection:', error);
    }
} 