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
}

/**
 * Set up the month navigation functionality
 */
function setupMonthNavigation() {
    // Get navigation buttons
    const prevMonthBtn = document.getElementById('prevMonthBtn');
    const nextMonthBtn = document.getElementById('nextMonthBtn');
    
    console.log('Setting up month navigation:');
    console.log('- Previous month button:', prevMonthBtn);
    console.log('- Next month button:', nextMonthBtn);
    
    // Remove any existing event listeners
    if (prevMonthBtn) {
        prevMonthBtn.replaceWith(prevMonthBtn.cloneNode(true));
        const newPrevBtn = document.getElementById('prevMonthBtn');
        
        newPrevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Previous month button clicked!');
            const month = this.getAttribute('data-month');
            const year = this.getAttribute('data-year');
            console.log(`Navigating back to: Month ${month}, Year ${year}`);
            fetchNewCalendar(month, year);
        });
    }
    
    if (nextMonthBtn) {
        nextMonthBtn.replaceWith(nextMonthBtn.cloneNode(true));
        const newNextBtn = document.getElementById('nextMonthBtn');
        
        newNextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Next month button clicked!');
            const month = this.getAttribute('data-month');
            const year = this.getAttribute('data-year');
            console.log(`Navigating forward to: Month ${month}, Year ${year}`);
            fetchNewCalendar(month, year);
        });
    }
}

/**
 * Fetch new calendar content - using a different function name to avoid confusion
 */
function fetchNewCalendar(month, year) {
    console.log(`Fetching calendar for: ${month}/${year}`);
    
    // Get the ID of the main content element - use the correct one from dashboard.php
    const contentContainer = document.getElementById('mainContent');
    if (!contentContainer) {
        console.error('Main content container not found - unable to update calendar');
        alert('Cannot update calendar - content container not found');
        return;
    }
    
    // Show loading indicator
    contentContainer.innerHTML = '<div style="text-align: center; padding: 30px;"><i class="fas fa-spinner fa-spin" style="font-size: 30px; color: #d1789c;"></i><p>Loading calendar...</p></div>';
    
    // Try multiple possible paths (the issue might be the path)
    let contentUrl = `content/calendar.php?month=${month}&year=${year}`;
    
    console.log(`Attempting to fetch from: ${contentUrl}`);
    
    // Update browser history without reloading
    const newUrl = `dashboard.php?page=calendar&month=${month}&year=${year}`;
    window.history.pushState({}, '', newUrl);
    
    // Add debug info directly to page before fetch
    contentContainer.innerHTML += `<div style="display:none" id="debug-info">Trying to fetch: ${contentUrl}</div>`;
    
    // Fetch the updated calendar content via AJAX
    fetch(contentUrl)
        .then(response => {
            console.log('Fetch response:', response);
            if (!response.ok) {
                // Try alternate path if first one fails
                console.log('First fetch failed, trying alternate path...');
                return fetch(`../content/calendar.php?month=${month}&year=${year}`);
            }
            return response.text();
        })
        .then(html => {
            if (!html) {
                throw new Error('Empty response received');
            }
            
            console.log('Successfully fetched calendar content');
            
            // Update content
            contentContainer.innerHTML = html;
            
            // Directly call initCalendar without setTimeout
            console.log('Reinitializing calendar...');
            initCalendar();
        })
        .catch(error => {
            console.error('Error navigating to new month:', error);
            
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
        // Add hover effect
        entry.addEventListener('mouseenter', function() {
            const tooltip = this.querySelector('.mood-tooltip');
            if (tooltip) {
                tooltip.style.opacity = '1';
                tooltip.style.visibility = 'visible';
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