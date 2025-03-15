document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const content = document.getElementById('content');
    const contentContainer = document.getElementById('contentContainer');
    const menuLinks = document.querySelectorAll('.menu-link[data-page]');
    
    // Create overlay
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);
    
    // Function to load page content
    function loadPage(page) {
        // Show loading state
        contentContainer.innerHTML = '<div class="loading">Loading...</div>';
        
        // Fetch the page content
        fetch(`content/${page}.php`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Page not found');
                }
                return response.text();
            })
            .then(html => {
                contentContainer.innerHTML = html;
                
                // Update page title
                const pageTitle = document.getElementById('pageTitle');
                if (pageTitle) {
                    pageTitle.textContent = ''; // Remove the duplicate title
                }
                
                // Initialize page-specific functionality
                switch(page) {
                    case 'log-mood':
                        if (typeof reinitMoodTracker === 'function') {
                            reinitMoodTracker();
                        }
                        break;
                        
                    case 'calendar':
                        if (typeof initCalendar === 'function') {
                            initCalendar();
                        }
                        break;
                        
                    case 'profile':
                        if (typeof setupProfileFunctionality === 'function') {
                            setupProfileFunctionality();
                        }
                        break;
                }
            })
            .catch(error => {
                contentContainer.innerHTML = '<div class="error">Error loading content</div>';
            });
    }
    
    // Toggle sidebar function - improved for mobile
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        sidebar.classList.toggle('active');
        
        if (window.innerWidth <= 768) {
            if (sidebar.classList.contains('active')) {
                overlay.classList.add('active');
                document.body.classList.add('sidebar-open');
                document.body.style.overflow = 'hidden';
            } else {
                overlay.classList.remove('active');
                document.body.classList.remove('sidebar-open');
                document.body.style.overflow = '';
            }
        }
        
        handleMobileLayout();
    }
    
    // Add click event listeners
    sidebarToggle.addEventListener('click', function(e) {
        e.preventDefault();
        toggleSidebar();
    });
    
    overlay.addEventListener('click', function() {
        if (sidebar.classList.contains('active')) {
            toggleSidebar();
        }
    });
    
    // Add click event listeners to menu links
    menuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const page = this.dataset.page;
            
            // Update active state
            menuLinks.forEach(l => l.parentElement.classList.remove('active'));
            this.parentElement.classList.add('active');
            
            // Load the page content
            loadPage(page);
            
            // Close sidebar on mobile
            if (window.innerWidth < 768) {
                toggleSidebar();
            }
        });
    });
    
    // Handle window resize
    window.addEventListener('resize', handleMobileLayout);
    window.addEventListener('load', handleMobileLayout);
    
    // Load initial page
    const initialPage = 'dashboard';
    loadPage(initialPage);
});

function handleMobileLayout() {
    const isMobile = window.innerWidth <= 768;
    const content = document.getElementById('content');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.sidebar-overlay');

    if (isMobile) {
        // Mobile layout
        content.style.marginLeft = '0';
        sidebarToggle.style.left = '15px';
        
        if (sidebar.classList.contains('active')) {
            overlay.classList.add('active');
            document.body.classList.add('sidebar-open');
        } else {
            overlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        }
    } else {
        // Desktop layout
        document.body.classList.remove('sidebar-open');
        document.body.style.overflow = '';
        content.style.marginLeft = sidebar.classList.contains('active') ? '0' : '250px';
        sidebarToggle.style.left = sidebar.classList.contains('active') ? '20px' : '270px';
        overlay.classList.remove('active');
    }
} 