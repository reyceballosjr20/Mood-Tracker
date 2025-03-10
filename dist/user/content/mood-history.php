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
    <h1 class="page-title">Mood History</h1>
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Search moods...">
    </div>
</div>

<div class="mood-chart" style="margin-bottom: 30px;">
    <div class="section-header">
        <h2 class="section-title">Monthly Overview</h2>
        <div>
            <select style="padding: 5px 10px; border-radius: 5px; border: 1px solid #ddd;">
                <option>January 2023</option>
                <option>February 2023</option>
                <option>March 2023</option>
                <option selected>April 2023</option>
            </select>
        </div>
    </div>
    <div class="chart-container" style="height: 300px;">
        <p>Monthly mood trend chart will be displayed here</p>
    </div>
</div>

<div class="recent-activities">
    <div class="section-header">
        <h2 class="section-title">Mood Log</h2>
        <a href="#" class="view-all">Filter</a>
    </div>
    
    <ul class="activity-list">
        <li class="activity-item">
            <div class="activity-icon">
                <i class="fas fa-smile"></i>
            </div>
            <div class="activity-info">
                <div class="activity-title">Happy - "Had a great day at work"</div>
                <div class="activity-time">Today at 10:30 AM</div>
            </div>
            <button class="activity-action">
                <i class="fas fa-ellipsis-v"></i>
            </button>
        </li>
        
        <li class="activity-item">
            <div class="activity-icon">
                <i class="fas fa-meh"></i>
            </div>
            <div class="activity-info">
                <div class="activity-title">Neutral - "Regular day, nothing special"</div>
                <div class="activity-time">Yesterday at 9:15 PM</div>
            </div>
            <button class="activity-action">
                <i class="fas fa-ellipsis-v"></i>
            </button>
        </li>
        
        <li class="activity-item">
            <div class="activity-icon">
                <i class="fas fa-grin-stars"></i>
            </div>
            <div class="activity-info">
                <div class="activity-title">Excited - "Got promoted at work!"</div>
                <div class="activity-time">2 days ago</div>
            </div>
            <button class="activity-action">
                <i class="fas fa-ellipsis-v"></i>
            </button>
        </li>
        
        <li class="activity-item">
            <div class="activity-icon">
                <i class="fas fa-sad-tear"></i>
            </div>
            <div class="activity-info">
                <div class="activity-title">Sad - "Missed an important deadline"</div>
                <div class="activity-time">3 days ago</div>
            </div>
            <button class="activity-action">
                <i class="fas fa-ellipsis-v"></i>
            </button>
        </li>
        
        <li class="activity-item">
            <div class="activity-icon">
                <i class="fas fa-grin-beam"></i>
            </div>
            <div class="activity-info">
                <div class="activity-title">Happy - "Weekend getaway with friends"</div>
                <div class="activity-time">5 days ago</div>
            </div>
            <button class="activity-action">
                <i class="fas fa-ellipsis-v"></i>
            </button>
        </li>
    </ul>
</div> 