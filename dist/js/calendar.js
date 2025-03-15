/**
 * Calendar functionality for the mood tracker application
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize calendar functionality
    initCalendar();
});

/**
 * Initialize the calendar functionality
 */
function initCalendar() {
    // Set up month navigation
    setupMonthNavigation();
    
    // Add tooltip functionality for mood entries
    setupMoodTooltips();
    
    // Handle calendar view options
    setupViewOptions();
    
    // Add mood-specific styling
    setupMoodColors();
}

/**
 * Set up the month navigation functionality
 */
function setupMonthNavigation() {
    // Get navigation buttons
    const prevMonthBtn = document.getElementById('prevMonthBtn');
    const nextMonthBtn = document.getElementById('nextMonthBtn');
    
    // Remove any existing event listeners
    if (prevMonthBtn) {
        prevMonthBtn.replaceWith(prevMonthBtn.cloneNode(true));
        const newPrevBtn = document.getElementById('prevMonthBtn');
        
        newPrevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const month = this.getAttribute('data-month');
            const year = this.getAttribute('data-year');
            fetchNewCalendar(month, year);
        });
    }
    
    if (nextMonthBtn) {
        nextMonthBtn.replaceWith(nextMonthBtn.cloneNode(true));
        const newNextBtn = document.getElementById('nextMonthBtn');
        
        newNextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const month = this.getAttribute('data-month');
            const year = this.getAttribute('data-year');
            fetchNewCalendar(month, year);
        });
    }
}

/**
 * Fetch new calendar content - using a different function name to avoid confusion
 */
function fetchNewCalendar(month, year) {
    // Get the ID of the main content element - use the correct one from dashboard.php
    const contentContainer = document.getElementById('mainContent');
    if (!contentContainer) {
        alert('Cannot update calendar - content container not found');
        return;
    }
    
    // Show loading indicator
    contentContainer.innerHTML = '<div style="text-align: center; padding: 30px;"><i class="fas fa-spinner fa-spin" style="font-size: 30px; color: #d1789c;"></i><p>Loading calendar...</p></div>';
    
    // Try multiple possible paths (the issue might be the path)
    let contentUrl = `content/calendar.php?month=${month}&year=${year}`;
    
    // Update browser history without reloading
    const newUrl = `dashboard.php?page=calendar&month=${month}&year=${year}`;
    window.history.pushState({}, '', newUrl);
    
   
    contentContainer.innerHTML += `<div style="display:none" id="debug-info">Trying to fetch: ${contentUrl}</div>`;
    
    // Fetch the updated calendar content via AJAX
    fetch(contentUrl)
        .then(response => {
            if (!response.ok) {
                // Try alternate path if first one fails
                return fetch(`../content/calendar.php?month=${month}&year=${year}`);
            }
            return response.text();
        })
        .then(html => {
            if (!html) {
                throw new Error('Empty response received');
            }
            
            // Update content
            contentContainer.innerHTML = html;
            
            // Directly call initCalendar without setTimeout
            initCalendar();
        })
        .catch(error => {
            // Try a final approach - direct page load
            window.location.href = newUrl;
        });
}

// Override the original function to use our new approach
function navigateToMonth(month, year) {
    fetchNewCalendar(month, year);
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