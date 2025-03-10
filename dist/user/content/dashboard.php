<?php
// Initialize session if needed
session_start();

// Check if user is logged in
if(!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    echo "Authorization required";
    exit;
}
?>

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