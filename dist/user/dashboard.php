<?php
// Initialize session
session_start();

// Check if user is logged in, if not redirect to login page
if(!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Get user data from session
$user = [
    'user_id' => $_SESSION['user_id'] ?? null,
    'first_name' => $_SESSION['first_name'] ?? 'User',
    'last_name' => $_SESSION['last_name'] ?? '',
    'email' => $_SESSION['email'] ?? '',
];

// Initialize current page
$current_page = 'dashboard';
if(isset($_GET['page'])) {
    $current_page = $_GET['page'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Mood Tracker</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f9f1ef;
            color: #333;
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }
        
        /* Enhanced Sidebar styles */
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #f5d7e3 0%, #f8dfeb 100%);
            height: 100vh;
            padding: 20px 15px;
            position: fixed;
            left: 0;
            top: 0;
            transition: all 0.3s ease;
            z-index: 10;
            box-shadow: 2px 0 15px rgba(0, 0, 0, 0.08);
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #ff8fb1 #f8dfeb;
        }
        
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: #f8dfeb;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background-color: #ff8fb1;
            border-radius: 6px;
        }
        
        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            margin-bottom: 30px;
            border-bottom: 1px solid rgba(110, 59, 92, 0.1);
            padding-bottom: 15px;
        }
        
        .logo {
            font-size: 22px;
            font-weight: 600;
            color: #6e3b5c;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
        }
        
        .logo:hover {
            transform: scale(1.02);
        }
        
        .logo i {
            margin-right: 10px;
            font-size: 24px;
            color: #ff8fb1;
            filter: drop-shadow(0 0 2px rgba(255, 143, 177, 0.3));
        }
        
        .toggle-sidebar {
            background: rgba(255, 255, 255, 0.3);
            border: none;
            color: #6e3b5c;
            font-size: 18px;
            cursor: pointer;
            height: 34px;
            width: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        
        .toggle-sidebar:hover {
            background: rgba(255, 255, 255, 0.5);
            transform: scale(1.05);
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 12px;
            background-color: rgba(255, 255, 255, 0.4);
            transition: all 0.2s ease;
            border: 1px solid rgba(255, 255, 255, 0.6);
        }
        
        .user-profile:hover {
            background-color: rgba(255, 255, 255, 0.6);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #ff8fb1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 500;
            font-size: 16px;
            margin-right: 12px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .user-info {
            flex: 1;
        }
        
        .user-name {
            font-weight: 500;
            color: #6e3b5c;
            margin-bottom: 2px;
            font-size: 15px;
        }
        
        .user-role {
            font-size: 12px;
            color: #9c7992;
        }
        
        .menu-title {
            font-size: 12px;
            text-transform: uppercase;
            color: #9c7992;
            font-weight: 500;
            margin-bottom: 8px;
            padding-left: 10px;
            letter-spacing: 0.5px;
        }
        
        .menu-list {
            list-style: none;
            margin-bottom: 20px;
        }
        
        .menu-item {
            position: relative;
            margin-bottom: 5px;
            transition: all 0.2s ease;
            border-radius: 10px;
        }
        
        .menu-item:hover {
            background-color: rgba(255, 255, 255, 0.5);
        }
        
        .menu-item.active {
            background-color: #ffffff;
            box-shadow: 0 2px 6px rgba(110, 59, 92, 0.1);
        }
        
        .menu-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background-color: #ff8fb1;
            border-radius: 10px 0 0 10px;
        }
        
        .menu-link {
            padding: 12px 15px;
            display: flex;
            align-items: center;
            color: #6e3b5c;
            text-decoration: none;
            font-weight: 400;
            transition: all 0.2s ease;
            border-radius: 10px;
        }
        
        .menu-item.active .menu-link {
            font-weight: 500;
            color: #4a3347;
        }
        
        .menu-link i {
            min-width: 25px;
            margin-right: 10px;
            font-size: 18px;
            color: #6e3b5c;
            transition: all 0.2s ease;
        }
        
        .menu-item.active .menu-link i {
            color: #ff8fb1;
        }
        
        .menu-link span {
            transition: all 0.2s ease;
        }
        
        .menu-divider {
            height: 1px;
            background-color: rgba(110, 59, 92, 0.1);
            margin: 20px 0;
        }
        
        .tooltip {
            position: relative;
        }
        
        .tooltip::after {
            content: attr(data-tooltip);
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            pointer-events: none;
            z-index: 100;
        }
        
        .tooltip:hover::after {
            opacity: 1;
            visibility: visible;
            left: calc(100% + 10px);
        }
        
        /* Main content area */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 25px;
            transition: all 0.3s ease;
        }
        
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: #4a3347;
        }
        
        .search-box {
            display: flex;
            align-items: center;
            background-color: white;
            border-radius: 30px;
            padding: 8px 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .search-box input {
            border: none;
            outline: none;
            background: none;
            padding: 5px;
            font-size: 14px;
            width: 200px;
        }
        
        .search-box i {
            color: #6e3b5c;
            margin-right: 8px;
        }
        
        /* Dashboard cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background-color: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 500;
            color: #4a3347;
        }
        
        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(255, 143, 177, 0.2);
            color: #ff5c8a;
            font-size: 18px;
        }
        
        .card-content {
            font-size: 28px;
            font-weight: 600;
            color: #4a3347;
            margin-bottom: 10px;
        }
        
        .card-footer {
            font-size: 13px;
            color: #7b6175;
        }
        
        /* Recent activities section */
        .recent-activities {
            background-color: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 500;
            color: #4a3347;
        }
        
        .view-all {
            font-size: 14px;
            color: #ff5c8a;
            text-decoration: none;
            font-weight: 500;
        }
        
        .activity-list {
            list-style: none;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(255, 143, 177, 0.15);
            color: #ff5c8a;
            font-size: 16px;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .activity-info {
            flex: 1;
        }
        
        .activity-title {
            font-size: 15px;
            font-weight: 500;
            color: #4a3347;
            margin-bottom: 4px;
        }
        
        .activity-time {
            font-size: 12px;
            color: #7b6175;
        }
        
        .activity-action {
            background: none;
            border: none;
            color: #6e3b5c;
            cursor: pointer;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s ease;
        }
        
        .activity-action:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        /* Mood chart section */
        .mood-chart {
            background-color: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .chart-container {
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #7b6175;
            background-color: rgba(240, 240, 240, 0.3);
            border-radius: 10px;
        }
        
        /* Loading overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }
        
        .loading-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f8dfeb;
            border-top: 5px solid #ff8fb1;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Mobile responsiveness */
        @media (max-width: 1024px) {
            .dashboard-cards {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
                box-shadow: none;
            }
            
            .sidebar.active {
                left: 0;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            }
            
            .tooltip::after {
                display: none;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .mobile-header {
                display: flex;
            }
        }
        
        @media (max-width: 480px) {
            .header {
                margin-bottom: 20px;
                margin-top: 30px;
            }
            
            .page-title {
                font-size: 20px;
            }
            
            .card {
                padding: 15px;
            }
            
            .card-content {
                font-size: 24px;
            }
            
            .activity-title {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <!-- Loading overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="spinner"></div>
    </div>

    <!-- Enhanced Sidebar structure -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-cloud-sun"></i>
                <span>Mood Tracker</span>
            </div>
            <button class="toggle-sidebar" id="toggleSidebar">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <div class="user-profile">
            <div class="user-avatar">
                <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
            </div>
            <div class="user-info">
                <div class="user-name"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></div>
                <div class="user-role">Member</div>
            </div>
        </div>
        
        <div class="menu-wrapper">
            <h3 class="menu-title">Main Menu</h3>
            <ul class="menu-list">
                <li class="menu-item <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                    <a href="#" class="menu-link" data-page="dashboard">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="menu-item <?php echo $current_page === 'mood-history' ? 'active' : ''; ?>">
                    <a href="#" class="menu-link" data-page="mood-history">
                        <i class="fas fa-history"></i>
                        <span>Mood History</span>
                    </a>
                </li>
                <li class="menu-item <?php echo $current_page === 'calendar' ? 'active' : ''; ?>">
                    <a href="#" class="menu-link" data-page="calendar">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Calendar</span>
                    </a>
                </li>
                <li class="menu-item <?php echo $current_page === 'recommendations' ? 'active' : ''; ?>">
                    <a href="#" class="menu-link" data-page="recommendations">
                        <i class="fas fa-lightbulb"></i>
                        <span>Recommendations</span>
                    </a>
                </li>
            </ul>
            
            <div class="menu-divider"></div>
            
            <h3 class="menu-title">My Account</h3>
            <ul class="menu-list">
                <li class="menu-item <?php echo $current_page === 'profile' ? 'active' : ''; ?>">
                    <a href="#" class="menu-link" data-page="profile">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                </li>
                <li class="menu-item <?php echo $current_page === 'settings' ? 'active' : ''; ?>">
                    <a href="#" class="menu-link" data-page="settings">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="../logout.php" class="menu-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    
    <!-- Mobile header -->
    <div class="mobile-header">
        <button class="mobile-toggle" id="mobileToggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="mobile-logo">Mood Tracker</div>
        <div class="mobile-actions">
            <a href="#" class="mobile-action tooltip" data-tooltip="Your profile">
                <i class="fas fa-user"></i>
            </a>
        </div>
    </div>
    
    <!-- Main content -->
    <div class="main-content" id="mainContent">
        <!-- Content will be loaded here dynamically -->
    </div>
    
    <script>
        // DOM Elements
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleSidebar');
        const mobileToggle = document.getElementById('mobileToggle');
        const mainContent = document.getElementById('mainContent');
        const menuLinks = document.querySelectorAll('.menu-link[data-page]');
        const loadingOverlay = document.getElementById('loadingOverlay');
        
        // Current active page
        let currentPage = '<?php echo $current_page; ?>';
        
        // Function to load page content
        async function loadPage(page) {
            // Don't reload if it's the current page
            if (page === currentPage && mainContent.innerHTML.trim() !== '') {
                return;
            }
            
            try {
                // Show loading overlay
                showLoading();
                
                // Fetch the page content
                const response = await fetch(`content/${page}.php?_=${Date.now()}`);
                
                if (!response.ok) {
                    throw new Error(`Failed to load page: ${response.status}`);
                }
                
                const html = await response.text();
                
                // Update content
                mainContent.innerHTML = html;
                
                // Update active state
                updateActiveMenu(page);
                
                // Update URL without reload
                window.history.pushState({page}, '', `?page=${page}`);
                
                // Update current page
                currentPage = page;
                
                // Initialize any scripts on the new page
                initPageScripts();
            } catch (error) {
                console.error('Error loading page:', error);
                mainContent.innerHTML = `
                    <div style="padding: 20px; text-align: center;">
                        <h2>Error Loading Content</h2>
                        <p>${error.message}</p>
                        <button onclick="loadPage('dashboard')" class="signup-btn" style="max-width: 200px; margin: 20px auto;">
                            Go to Dashboard
                        </button>
                    </div>
                `;
            } finally {
                // Hide loading overlay
                hideLoading();
            }
        }
        
        // Update active menu item
        function updateActiveMenu(page) {
            // First remove active class from all menu items
            document.querySelectorAll('.menu-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Then add active class to the selected menu item
            const activeLink = document.querySelector(`.menu-link[data-page="${page}"]`);
            if (activeLink) {
                activeLink.closest('.menu-item').classList.add('active');
            }
        }
        
        // Show loading overlay
        function showLoading() {
            loadingOverlay.classList.add('active');
        }
        
        // Hide loading overlay
        function hideLoading() {
            loadingOverlay.classList.remove('active');
        }
        
        // Initialize page-specific scripts
        function initPageScripts() {
            // Here you can add code to initialize specific functionality
            // for different pages, like charts, calendars, etc.
            console.log('Initializing scripts for:', currentPage);
        }
        
        // Handle menu click events
        menuLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = this.dataset.page;
                
                // Update active menu immediately for better responsiveness
                updateActiveMenu(page);
                
                // Then load the page content
                loadPage(page);
                
                // Close sidebar on mobile
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('active');
                }
            });
        });
        
        // Toggle sidebar on mobile
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target) && sidebar.classList.contains('active')) {
                    sidebar.classList.remove('active');
                }
            }
        });
        
        // Handle browser back/forward navigation
        window.addEventListener('popstate', function(event) {
            if (event.state && event.state.page) {
                loadPage(event.state.page);
            } else {
                loadPage('dashboard');
            }
        });
        
        // Make sure the initial page is marked as active when the document loads
        document.addEventListener('DOMContentLoaded', function() {
            updateActiveMenu(currentPage);
            loadPage(currentPage);
        });
    </script>
</body>
</html> 