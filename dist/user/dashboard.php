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
        
        /* Sidebar styles */
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
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
        }
        
        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            margin-bottom: 30px;
        }
        
        .logo {
            font-size: 22px;
            font-weight: 600;
            color: #6e3b5c;
            display: flex;
            align-items: center;
        }
        
        .logo i {
            margin-right: 10px;
            font-size: 24px;
            color: #ff8fb1;
        }
        
        .toggle-sidebar {
            background: none;
            border: none;
            color: #6e3b5c;
            font-size: 18px;
            cursor: pointer;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            padding: 15px 5px;
            margin-bottom: 20px;
            border-radius: 10px;
            background-color: rgba(255, 255, 255, 0.3);
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
            font-weight: 600;
            margin-right: 10px;
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
        }
        
        .user-name {
            font-weight: 500;
            font-size: 14px;
            color: #4a3347;
        }
        
        .user-status {
            font-size: 12px;
            color: #7b6175;
        }
        
        .menu-items {
            list-style: none;
            margin-top: 20px;
        }
        
        .menu-item {
            position: relative;
            margin-bottom: 5px;
        }
        
        .menu-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-radius: 8px;
            text-decoration: none;
            color: #6e3b5c;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .menu-link i {
            margin-right: 10px;
            font-size: 18px;
            width: 20px;
            text-align: center;
        }
        
        .menu-link:hover, .menu-link.active {
            background-color: rgba(255, 143, 177, 0.2);
            color: #ff5c8a;
        }
        
        .menu-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background-color: #ff5c8a;
            border-radius: 0 4px 4px 0;
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
                transform: translateX(-100%);
                box-shadow: none;
            }
            
            .sidebar.active {
                transform: translateX(0);
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .toggle-sidebar {
                display: block;
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 11;
                background-color: white;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .header {
                margin-top: 50px;
            }
            
            .logo {
                justify-content: flex-start;
            }
            
            .logo i {
                margin-right: 10px;
            }
            
            .menu-link {
                justify-content: flex-start;
                padding: 12px 15px;
            }
            
            .menu-link i {
                margin-right: 10px;
            }
            
            .user-avatar {
                margin-right: 10px;
            }
            
            .main-content {
                margin-left: 0;
                padding: 15px;
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

    <!-- Mobile menu toggle -->
    <button class="toggle-sidebar" id="toggleSidebar">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-smile"></i>
                <span>Mood Tracker</span>
            </div>
        </div>
        
        <div class="user-profile">
            <div class="user-avatar">
                <span><?php echo substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1); ?></span>
            </div>
            <div class="user-info">
                <div class="user-name"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></div>
                <div class="user-status">Premium Member</div>
            </div>
        </div>
        
        <ul class="menu-items">
            <li class="menu-item">
                <a class="menu-link <?php echo $current_page == 'dashboard' ? 'active' : ''; ?>" data-page="dashboard">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="menu-item">
                <a class="menu-link <?php echo $current_page == 'mood-history' ? 'active' : ''; ?>" data-page="mood-history">
                    <i class="fas fa-chart-line"></i>
                    <span>Mood History</span>
                </a>
            </li>
            <li class="menu-item">
                <a class="menu-link <?php echo $current_page == 'calendar' ? 'active' : ''; ?>" data-page="calendar">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Calendar</span>
                </a>
            </li>
            <li class="menu-item">
                <a class="menu-link <?php echo $current_page == 'recommendations' ? 'active' : ''; ?>" data-page="recommendations">
                    <i class="fas fa-lightbulb"></i>
                    <span>Recommendations</span>
                </a>
            </li>
            <li class="menu-item">
                <a class="menu-link <?php echo $current_page == 'settings' ? 'active' : ''; ?>" data-page="settings">
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
    
    <!-- Main content -->
    <div class="main-content" id="mainContent">
        <!-- Content will be loaded here dynamically -->
    </div>
    
    <script>
        // DOM Elements
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleSidebar');
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
            menuLinks.forEach(link => {
                if (link.dataset.page === page) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
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
        
        // Load initial page
        document.addEventListener('DOMContentLoaded', function() {
            loadPage(currentPage);
        });
    </script>
</body>
</html> 