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
        console.log(`Loading page: ${page}`);
        
        // Show loading state
        contentContainer.innerHTML = '<div class="loading">Loading...</div>';
        
        // Fetch the page content
        fetch(`content/${page}.php`)
            .then(response => response.text())
            .then(html => {
                contentContainer.innerHTML = html;
                
                // Update page title
                const pageTitle = document.getElementById('pageTitle');
                pageTitle.textContent = ''; // Remove the duplicate title
                
                // Reinitialize mood tracker if we're on the log-mood page
                if (page === 'log-mood') {
                    if (typeof reinitMoodTracker === 'function') {
                        reinitMoodTracker();
                    }
                }
            })
            .catch(error => {
                console.error('Error loading page:', error);
                contentContainer.innerHTML = '<div class="error">Error loading content</div>';
            });
    }
    
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
    
    // Toggle sidebar function
    function toggleSidebar() {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
        
        // Adjust main content margin and toggle button position
        if (window.innerWidth >= 768) {
            if (sidebar.classList.contains('active')) {
                content.style.marginLeft = '0';
                sidebarToggle.style.left = '20px';
            } else {
                content.style.marginLeft = '250px';
                sidebarToggle.style.left = '270px';
            }
        }
    }
    
    // Event listeners
    sidebarToggle.addEventListener('click', toggleSidebar);
    
    overlay.addEventListener('click', function() {
        if (sidebar.classList.contains('active')) {
            toggleSidebar();
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            if (sidebar.classList.contains('active')) {
                content.style.marginLeft = '0';
                sidebarToggle.style.left = '20px';
            } else {
                content.style.marginLeft = '250px';
                sidebarToggle.style.left = '270px';
            }
            overlay.classList.remove('active');
        } else {
            content.style.marginLeft = '0';
            sidebarToggle.style.left = sidebar.classList.contains('active') ? '265px' : '15px';
        }
    });
    
    // Load initial page
    const initialPage = 'dashboard';
    loadPage(initialPage);
}); 