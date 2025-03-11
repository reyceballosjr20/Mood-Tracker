document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const content = document.getElementById('content');
    
    // Create overlay
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);
    
    // Toggle sidebar and adjust content/button position
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
    
    // Close sidebar when clicking overlay
    overlay.addEventListener('click', function() {
        if (sidebar.classList.contains('active')) {
            toggleSidebar();
        }
    });
    
    // Event listeners
    sidebarToggle.addEventListener('click', toggleSidebar);
    
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
}); 