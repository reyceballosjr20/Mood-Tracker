document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarCollapse = document.getElementById('sidebarCollapse');
    const content = document.getElementById('content');
    
    // Create overlay
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);
    
    // Toggle sidebar
    function toggleSidebar() {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
        
        // Adjust main content margin on desktop
        if (window.innerWidth >= 768) {
            content.style.marginLeft = sidebar.classList.contains('active') ? '0' : '250px';
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
    if (sidebarCollapse) {
        sidebarCollapse.addEventListener('click', toggleSidebar);
    }
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            content.style.marginLeft = sidebar.classList.contains('active') ? '0' : '250px';
            overlay.classList.remove('active');
        } else {
            content.style.marginLeft = '0';
        }
    });
}); 