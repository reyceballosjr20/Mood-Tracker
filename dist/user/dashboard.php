<?php
// Initialize session
session_start();
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
            border-radius: 50%;
            background-color: rgba(255, 143, 177, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ff5c8a;
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
            margin-bottom: 3px;
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
            margin-left: 10px;
            font-size: 16px;
        }
        
        /* Mood chart section */
        .mood-chart {
            background-color: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-top: 30px;
            height: 300px;
        }
        
        .chart-container {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #7b6175;
            font-size: 14px;
        }
        
        /* Mobile responsiveness */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                padding: 15px 10px;
            }
            
            .logo span, .user-info, .menu-link span {
                display: none;
            }
            
            .logo {
                justify-content: center;
            }
            
            .logo i {
                margin-right: 0;
            }
            
            .menu-link {
                justify-content: center;
                padding: 12px;
            }
            
            .menu-link i {
                margin-right: 0;
                font-size: 20px;
            }
            
            .user-profile {
                justify-content: center;
                padding: 10px;
            }
            
            .user-avatar {
                margin-right: 0;
            }
            
            .main-content {
                margin-left: 80px;
            }
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .search-box {
                width: 100%;
                margin-top: 15px;
            }
            
            .search-box input {
                width: 100%;
            }
            
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
            
            .toggle-sidebar {
                display: block;
                position: fixed;
                top: 15px;
                left: 20px;
                z-index: 20;
                background-color: white;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }
            
            .sidebar {
                transform: translateX(-100%);
                width: 250px;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .logo span, .user-info, .menu-link span {
                display: block;
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
                <span>JD</span>
            </div>
            <div class="user-info">
                <div class="user-name">John Doe</div>
                <div class="user-status">Premium Member</div>
            </div>
        </div>
        
        <ul class="menu-items">
            <li class="menu-item">
                <a href="#" class="menu-link active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <i class="fas fa-chart-line"></i>
                    <span>Mood History</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Calendar</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <i class="fas fa-lightbulb"></i>
                    <span>Recommendations</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="../login.php" class="menu-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Main content -->
    <div class="main-content">
        <div class="header">
            <h1 class="page-title">Dashboard</h1>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search...">
            </div>
        </div>
        
        <!-- Dashboard cards -->
        <div class="dashboard-cards">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Current Mood</h2>
                    <div class="card-icon">
                        <i class="fas fa-smile"></i>
                    </div>
                </div>
                <div class="card-content">Happy</div>
                <div class="card-footer">Last updated today at 10:30 AM</div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Mood Streaks</h2>
                    <div class="card-icon">
                        <i class="fas fa-fire"></i>
                    </div>
                </div>
                <div class="card-content">7 Days</div>
                <div class="card-footer">Keep going to build your streak!</div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Mood Entries</h2>
                    <div class="card-icon">
                        <i class="fas fa-book"></i>
                    </div>
                </div>
                <div class="card-content">42</div>
                <div class="card-footer">Total entries recorded</div>
            </div>
        </div>
        
        <!-- Recent activities -->
        <div class="recent-activities">
            <div class="section-header">
                <h2 class="section-title">Recent Activities</h2>
                <a href="#" class="view-all">View All</a>
            </div>
            
            <ul class="activity-list">
                <li class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-pen"></i>
                    </div>
                    <div class="activity-info">
                        <div class="activity-title">You logged a mood entry</div>
                        <div class="activity-time">Today at 10:30 AM</div>
                    </div>
                    <button class="activity-action">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </li>
                
                <li class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="activity-info">
                        <div class="activity-title">You completed a weekly reflection</div>
                        <div class="activity-time">Yesterday at 8:15 PM</div>
                    </div>
                    <button class="activity-action">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </li>
                
                <li class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="activity-info">
                        <div class="activity-title">You earned a new badge</div>
                        <div class="activity-time">2 days ago</div>
                    </div>
                    <button class="activity-action">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </li>
            </ul>
        </div>
        
        <!-- Mood chart -->
        <div class="mood-chart">
            <div class="section-header">
                <h2 class="section-title">Mood History</h2>
                <a href="#" class="view-all">View Details</a>
            </div>
            <div class="chart-container">
                <!-- Chart would be rendered here with a JS library -->
                <p>Mood chart visualization will be displayed here</p>
            </div>
        </div>
    </div>
    
    <script>
        // Toggle sidebar on mobile
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('toggleSidebar');
            
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target) && sidebar.classList.contains('active')) {
                    sidebar.classList.remove('active');
                }
            }
        });
        
        // Adjust layout on window resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
            }
        });
    </script>
</body>
</html> 