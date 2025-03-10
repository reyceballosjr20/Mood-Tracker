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
    <h1 class="page-title">Recommendations</h1>
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Search recommendations...">
    </div>
</div>

<div class="card" style="margin-bottom: 30px; background-color: #f8dfeb;">
    <div class="card-header">
        <h2 class="card-title">Your Mood Today</h2>
        <div class="card-icon">
            <i class="fas fa-lightbulb"></i>
        </div>
    </div>
    <div class="card-content" style="font-size: 18px;">Based on your mood data, here are some personalized recommendations</div>
</div>

<!-- Recommendations grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <h2 class="card-title">Meditation</h2>
            <div class="card-icon">
                <i class="fas fa-spa"></i>
            </div>
        </div>
        <div class="card-content" style="height: 100px; overflow: hidden;">
            <p>A 10-minute guided meditation can help improve your emotional balance and reduce stress levels.</p>
        </div>
        <div class="card-footer">
            <button style="background: #ff8fb1; border: none; color: white; padding: 8px 15px; border-radius: 5px; cursor: pointer;">
                Start Now
            </button>
        </div>
    </div>
    
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <h2 class="card-title">Physical Activity</h2>
            <div class="card-icon">
                <i class="fas fa-running"></i>
            </div>
        </div>
        <div class="card-content" style="height: 100px; overflow: hidden;">
            <p>A quick 20-minute walk can boost your endorphins and improve your mood immediately.</p>
        </div>
        <div class="card-footer">
            <button style="background: #ff8fb1; border: none; color: white; padding: 8px 15px; border-radius: 5px; cursor: pointer;">
                View Suggestions
            </button>
        </div>
    </div>
    
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <h2 class="card-title">Gratitude Practice</h2>
            <div class="card-icon">
                <i class="fas fa-heart"></i>
            </div>
        </div>
        <div class="card-content" style="height: 100px; overflow: hidden;">
            <p>Writing down three things you're grateful for can shift your perspective and improve your mood.</p>
        </div>
        <div class="card-footer">
            <button style="background: #ff8fb1; border: none; color: white; padding: 8px 15px; border-radius: 5px; cursor: pointer;">
                Start Journal
            </button>
        </div>
    </div>
</div>

<div class="section-header">
    <h2 class="section-title">Resources for You</h2>
</div>

<div class="recent-activities">
    <ul class="activity-list">
        <li class="activity-item">
            <div class="activity-icon">
                <i class="fas fa-book"></i>
            </div>
            <div class="activity-info">
                <div class="activity-title">Article: "5 Ways to Improve Your Mood Naturally"</div>
                <div class="activity-time">5 min read</div>
            </div>
            <button class="activity-action">
                <i class="fas fa-external-link-alt"></i>
            </button>
        </li>
        
        <li class="activity-item">
            <div class="activity-icon">
                <i class="fas fa-video"></i>
            </div>
            <div class="activity-info">
                <div class="activity-title">Video: "Understanding Mood Patterns"</div>
                <div class="activity-time">12 min watch</div>
            </div>
            <button class="activity-action">
                <i class="fas fa-external-link-alt"></i>
            </button>
        </li>
        
        <li class="activity-item">
            <div class="activity-icon">
                <i class="fas fa-headphones"></i>
            </div>
            <div class="activity-info">
                <div class="activity-title">Podcast: "The Science of Happiness"</div>
                <div class="activity-time">30 min listen</div>
            </div>
            <button class="activity-action">
                <i class="fas fa-external-link-alt"></i>
            </button>
        </li>
    </ul>
</div> 