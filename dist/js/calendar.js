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
            navigateToMonth(this.getAttribute('data-month'), this.getAttribute('data-year'));
        });
    }
    
    if (nextMonthBtn) {
        nextMonthBtn.addEventListener('click', function(e) {
            e.preventDefault();
            navigateToMonth(this.getAttribute('data-month'), this.getAttribute('data-year'));
        });
    }
    
    if (monthYearSelect) {
        monthYearSelect.addEventListener('change', function() {
            const [year, month] = this.value.split('-');
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
    // Get current URL and update query parameters
    const currentUrl = new URL(window.location.href);
    const params = currentUrl.searchParams;
    
    // Update or add month and year parameters
    params.set('month', month);
    params.set('year', year);
    
    // Keep the page parameter
    params.set('page', 'calendar');
    
    // Navigate to the new URL
    window.location.href = `dashboard.php?${params.toString()}`;
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