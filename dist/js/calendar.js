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
    const monthYearSelect = document.getElementById('monthYearSelect');
    
    if (prevMonthBtn) {
        prevMonthBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const month = this.getAttribute('data-month');
            const year = this.getAttribute('data-year');
            console.log(`Navigating back to: Month ${month}, Year ${year}`);
            navigateToMonth(month, year);
        });
    }
    
    if (nextMonthBtn) {
        nextMonthBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const month = this.getAttribute('data-month');
            const year = this.getAttribute('data-year');
            console.log(`Navigating forward to: Month ${month}, Year ${year}`);
            navigateToMonth(month, year);
        });
    }
    
    if (monthYearSelect) {
        monthYearSelect.addEventListener('change', function() {
            const [year, month] = this.value.split('-');
            console.log(`Select changed to: Month ${month}, Year ${year}`);
            navigateToMonth(month, year);
        });
    }
}

/**
 * Navigate to a specific month
 * 
 * @param {string} month - Month (1-12)
 * @param {string} year - Year (e.g., 2023)
 */
function navigateToMonth(month, year) {
    // Don't use window.location.href - it reloads the page
    // Instead, get the current dashboard page URL
    const dashboardUrl = window.location.pathname;
    
    // Build the content URL for the calendar with new month/year
    const contentUrl = `content/calendar.php?month=${month}&year=${year}`;
    
    // Update browser history without reloading the page
    const newUrl = `${dashboardUrl}?page=calendar&month=${month}&year=${year}`;
    window.history.pushState({}, '', newUrl);
    
    // Fetch the updated calendar content via AJAX
    fetch(contentUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to load calendar');
            }
            return response.text();
        })
        .then(html => {
            // Find the content container and update it
            const contentContainer = document.getElementById('content') || document.getElementById('mainContent');
            if (contentContainer) {
                contentContainer.innerHTML = html;
                
                // Reinitialize calendar after loading the new content
                initCalendar();
            } else {
                console.error('Content container not found');
            }
        })
        .catch(error => {
            console.error('Error navigating to new month:', error);
            alert('Error loading calendar. Please try again.');
        });
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