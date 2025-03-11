<?php
// Initialize session if needed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if(!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    echo "Authorization required";
    exit;
}

// Set timezone
date_default_timezone_set('Asia/Singapore');

// Load the Mood model
require_once '../../../models/Mood.php';
$mood = new Mood();
$userId = $_SESSION['user_id'] ?? 0;
$userName = $_SESSION['first_name'] ?? 'User';

// Get data for dashboard
$todaysMood = $mood->getTodaysMood($userId);
$moodLoggedToday = ($todaysMood !== false);
$streak = $mood->getUserLoggingStreak($userId);

// Get monthly entries using existing methods
$firstDayOfMonth = date('Y-m-01'); 
$lastDayOfMonth = date('Y-m-t');
$currentMonth = date('n');
$currentYear = date('Y');
$monthlyMoods = $mood->getUserMoodsByMonth($userId, $currentMonth, $currentYear);
$currentMonthEntries = is_array($monthlyMoods) ? count($monthlyMoods) : 0;

// Define mood emojis for display
$moodEmojis = [
    'sad' => '😢',
    'unhappy' => '😞',
    'neutral' => '😐',
    'good' => '😊',
    'energetic' => '💪',
    'excellent' => '🤩',
    'anxious' => '😰',
    'tired' => '😴',
    'focused' => '🎯'
];

// Get mood statistics for visualization
$moodStats = $mood->getMoodStatsByMonth($userId, $currentMonth, $currentYear);
?>

<div>
    <!-- Welcome header -->
    <div class="welcome-section">
        <h1>Hello, <?php echo htmlspecialchars($userName); ?>!</h1>
        <p class="welcome-date"><?php echo date('l, F j, Y'); ?></p>
    </div>
    
    <!-- Dashboard Summary Cards -->
    <div class="dashboard-summary">
        <!-- Today's Status Card -->
        <div class="summary-card today-card">
            <div class="card-header">
                <h3>Today's Mood</h3>
                <div class="icon-circle">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
            <?php if ($moodLoggedToday): ?>
                <div class="mood-emoji">
                    <?php echo isset($moodEmojis[$todaysMood['mood_type']]) ? $moodEmojis[$todaysMood['mood_type']] : '📝'; ?>
                </div>
                <div class="mood-label">
                    <?php echo ucfirst($todaysMood['mood_type']); ?>
                </div>
                <p class="mood-text">
                    <?php echo !empty($todaysMood['mood_text']) ? 
                        (strlen($todaysMood['mood_text']) > 50 ? 
                            substr(htmlspecialchars($todaysMood['mood_text']), 0, 50) . '...' : 
                            htmlspecialchars($todaysMood['mood_text'])) : 
                        'No additional notes.'; ?>
                </p>
            <?php else: ?>
                <div class="mood-emoji unlogged">
                    📝
                </div>
                <div class="mood-label unlogged">
                    Not logged yet
                </div>
                <a href="dashboard.php?page=log-mood" class="log-mood-link">
                    Log your mood <i class="fas fa-arrow-right"></i>
                </a>
            <?php endif; ?>
        </div>
        
        <!-- Streak Card -->
        <div class="summary-card streak-card">
            <div class="card-header">
                <h3>Logging Streak</h3>
                <div class="icon-circle">
                    <i class="fas fa-fire"></i>
                </div>
            </div>
            <div class="streak-display <?php echo $streak > 0 ? 'active' : 'inactive'; ?>">
                <span class="streak-count"><?php echo $streak; ?></span>
                <span class="streak-text"><?php echo $streak == 1 ? 'day' : 'days'; ?></span>
            </div>
            <div class="streak-message">
                <?php 
                if ($streak >= 7) {
                    echo 'Amazing consistency!';
                } elseif ($streak >= 3) {
                    echo 'Keep it up!';
                } elseif ($streak > 0) {
                    echo 'Getting started!';
                } else {
                    echo 'Start your streak today!';
                }
                ?>
            </div>
        </div>
        
        <!-- Monthly Progress Card -->
        <div class="summary-card monthly-card">
            <div class="card-header">
                <h3>Monthly Progress</h3>
                <div class="icon-circle">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
            <div class="monthly-count">
                <span class="count-num"><?php echo $currentMonthEntries; ?></span>
                <span class="count-label">entries</span>
            </div>
            <div class="month-name">
                This month (<?php echo date('F'); ?>)
            </div>
            <?php if ($currentMonthEntries > 0): ?>
                <div class="progress-bar">
                    <?php 
                    $daysInMonth = date('t');
                    $percentComplete = min(100, round(($currentMonthEntries / $daysInMonth) * 100));
                    ?>
                    <div class="progress-fill" style="width: <?php echo $percentComplete; ?>%"></div>
                </div>
                <div class="progress-text">
                    <?php echo $percentComplete; ?>% of days logged
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Quick Actions Card -->
        <div class="summary-card actions-card">
            <div class="card-header">
                <h3>Quick Actions</h3>
                <div class="icon-circle">
                    <i class="fas fa-bolt"></i>
                </div>
            </div>
            <div class="action-buttons">
                <a href="dashboard.php?page=log-mood" class="action-btn primary-action">
                    <i class="fas fa-plus"></i> Log Mood
                </a>
                <a href="dashboard.php?page=calendar" class="action-btn">
                    <i class="fas fa-calendar"></i> View Calendar
                </a>
                <a href="dashboard.php?page=mood-history" class="action-btn">
                    <i class="fas fa-history"></i> View History
                </a>
            </div>
        </div>
    </div>
    
    <!-- Mood Summary Section -->
    <?php if (is_array($moodStats) && count($moodStats) > 0): ?>
    <div class="mood-summary-section">
        <h2>Your Mood Summary</h2>
        <div class="mood-distribution">
            <?php 
            $totalEntries = array_reduce($moodStats, function($carry, $item) {
                return $carry + $item['count'];
            }, 0);
            
            foreach ($moodStats as $stat): 
                $percentage = round(($stat['count'] / $totalEntries) * 100);
                $moodType = $stat['mood_type'];
                $emoji = isset($moodEmojis[$moodType]) ? $moodEmojis[$moodType] : '📝';
            ?>
            <div class="mood-item">
                <div class="mood-bar-container">
                    <div class="mood-bar" style="height: <?php echo max(5, $percentage); ?>%"></div>
                </div>
                <div class="mood-emoji"><?php echo $emoji; ?></div>
                <div class="mood-percent"><?php echo $percentage; ?>%</div>
                <div class="mood-name"><?php echo ucfirst($moodType); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Recent Activity Section -->
    <div class="recent-activity-section">
        <h2>Recent Activity</h2>
        <?php
        // Get recent moods (last 7 days)
        $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));
        $today = date('Y-m-d');
        $recentMoods = $mood->getMoodHistory($userId, 'week');
        
        if (is_array($recentMoods) && count($recentMoods) > 0):
        ?>
        <div class="activity-timeline">
            <?php foreach(array_slice($recentMoods, 0, 5) as $entry): ?>
            <div class="timeline-item">
                <div class="timeline-date">
                    <?php echo date('M j', strtotime($entry['created_at'])); ?>
                </div>
                <div class="timeline-emoji">
                    <?php echo isset($moodEmojis[$entry['mood_type']]) ? $moodEmojis[$entry['mood_type']] : '📝'; ?>
                </div>
                <div class="timeline-content">
                    <div class="timeline-mood"><?php echo ucfirst($entry['mood_type']); ?></div>
                    <?php if (!empty($entry['mood_text'])): ?>
                    <div class="timeline-text"><?php echo htmlspecialchars($entry['mood_text']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($recentMoods) > 5): ?>
        <div class="view-more">
            <a href="dashboard.php?page=mood-history" class="view-more-link">
                View all entries <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="no-activity">
            <div class="no-data-icon"><i class="fas fa-info-circle"></i></div>
            <p>No mood entries in the last 7 days.</p>
            <a href="dashboard.php?page=log-mood" class="log-mood-btn">Log your first mood</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Dashboard Styles */
.dashboard-container {
    padding: 10px 0;
    max-width: 1200px;
    margin: 0 auto;
}

.welcome-section {
    margin-bottom: 30px;
}

.welcome-section h1 {
    color: #6e3b5c;
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 5px;
}

.welcome-date {
    color: #888;
    font-size: 1rem;
}

/* Summary Cards */
.dashboard-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.summary-card {
    background-color: white;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    overflow: hidden;
}

.summary-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 20px rgba(0,0,0,0.1);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.card-header h3 {
    font-size: 1.1rem;
    color: #6e3b5c;
    margin: 0;
    font-weight: 500;
}

.icon-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background-color: #fff3f8;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d1789c;
}

/* Today's Card */
.mood-emoji {
    font-size: 3rem;
    margin: 10px 0;
    text-align: center;
}

.mood-emoji.unlogged {
    color: #ccc;
}

.mood-label {
    font-size: 1.2rem;
    color: #4a3347;
    text-align: center;
    font-weight: 500;
    text-transform: capitalize;
    margin-bottom: 5px;
}

.mood-label.unlogged {
    color: #888;
}

.mood-text {
    font-size: 0.9rem;
    color: #777;
    text-align: center;
    margin-top: 10px;
    font-style: italic;
}

.log-mood-link {
    display: block;
    text-align: center;
    color: #d1789c;
    text-decoration: none;
    font-size: 0.9rem;
    margin-top: 15px;
    padding: 10px;
    border-radius: 8px;
    transition: background-color 0.2s ease;
}

.log-mood-link:hover {
    background-color: #fff3f8;
}

/* Streak Card */
.streak-display {
    text-align: center;
    margin: 15px 0;
}

.streak-count {
    font-size: 3rem;
    font-weight: 600;
}

.streak-text {
    font-size: 1.2rem;
    color: #888;
    margin-left: 5px;
}

.streak-display.inactive .streak-count {
    color: #ccc;
}

.streak-display.active .streak-count {
    color: #4a3347;
}

.streak-message {
    text-align: center;
    font-size: 0.9rem;
    color: #888;
    margin-top: 10px;
}

/* Monthly Card */
.monthly-count {
    text-align: center;
    margin: 15px 0 10px;
}

.count-num {
    font-size: 3rem;
    font-weight: 600;
    color: #4a3347;
}

.count-label {
    font-size: 1.2rem;
    color: #888;
    margin-left: 5px;
}

.month-name {
    text-align: center;
    font-size: 0.9rem;
    color: #888;
    margin-bottom: 15px;
}

.progress-bar {
    height: 8px;
    background-color: #f5f5f5;
    border-radius: 4px;
    overflow: hidden;
    margin: 10px 0;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(to right, #d1789c, #e896b8);
    border-radius: 4px;
}

.progress-text {
    text-align: right;
    font-size: 0.8rem;
    color: #888;
}

/* Actions Card */
.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.action-btn {
    padding: 12px 15px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    transition: all 0.2s ease;
}

.action-btn i {
    margin-right: 10px;
}

.action-btn.primary-action {
    background: linear-gradient(135deg, #d1789c, #e896b8);
    color: white;
}

.action-btn.primary-action:hover {
    box-shadow: 0 5px 15px rgba(209, 120, 156, 0.3);
}

.action-btn:not(.primary-action) {
    background-color: #f8f8f8;
    color: #6e3b5c;
}

.action-btn:not(.primary-action):hover {
    background-color: #f0f0f0;
}

/* Mood Summary Section */
.mood-summary-section {
    background-color: white;
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 40px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.05);
}

.mood-summary-section h2 {
    color: #6e3b5c;
    font-size: 1.4rem;
    margin-bottom: 25px;
    font-weight: 500;
}

.mood-distribution {
    display: flex;
    justify-content: space-evenly;
    align-items: flex-end;
    height: 200px;
    gap: 15px;
    padding-top: 30px;
}

.mood-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    max-width: 80px;
}

.mood-bar-container {
    width: 100%;
    height: 100px;
    display: flex;
    justify-content: center;
    align-items: flex-end;
}

.mood-bar {
    width: 70%;
    background: linear-gradient(to top, #d1789c, #f5d7e3);
    border-radius: 4px 4px 0 0;
    transition: height 0.5s ease;
}

.mood-emoji {
    margin: 10px 0 5px;
    font-size: 1.5rem;
}

.mood-percent {
    font-size: 0.9rem;
    font-weight: 600;
    color: #4a3347;
}

.mood-name {
    font-size: 0.8rem;
    color: #888;
    text-align: center;
    margin-top: 3px;
}

/* Recent Activity Section */
.recent-activity-section {
    background-color: white;
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.05);
}

.recent-activity-section h2 {
    color: #6e3b5c;
    font-size: 1.4rem;
    margin-bottom: 25px;
    font-weight: 500;
}

.activity-timeline {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.timeline-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f5f5f5;
}

.timeline-date {
    min-width: 50px;
    font-size: 0.85rem;
    color: #888;
    padding-top: 5px;
}

.timeline-emoji {
    font-size: 1.8rem;
}

.timeline-content {
    flex: 1;
}

.timeline-mood {
    font-size: 1rem;
    font-weight: 500;
    color: #4a3347;
    margin-bottom: 5px;
    text-transform: capitalize;
}

.timeline-text {
    font-size: 0.9rem;
    color: #666;
}

.view-more {
    margin-top: 20px;
    text-align: center;
}

.view-more-link {
    color: #d1789c;
    text-decoration: none;
    font-size: 0.95rem;
    display: inline-flex;
    align-items: center;
    padding: 8px 15px;
    border-radius: 8px;
    transition: background-color 0.2s ease;
}

.view-more-link i {
    margin-left: 5px;
    font-size: 0.8rem;
}

.view-more-link:hover {
    background-color: #fff3f8;
}

.no-activity {
    text-align: center;
    padding: 30px 0;
}

.no-data-icon {
    font-size: 2.5rem;
    color: #ddd;
    margin-bottom: 15px;
}

.no-activity p {
    color: #888;
    margin-bottom: 20px;
}

.log-mood-btn {
    display: inline-block;
    background: linear-gradient(135deg, #d1789c, #e896b8);
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    transition: box-shadow 0.2s ease;
}

.log-mood-btn:hover {
    box-shadow: 0 5px 15px rgba(209, 120, 156, 0.3);
}

/* Responsive Design - Improved */
@media (max-width: 768px) {
    /* Fix the gap between sidebar and content */
    body {
        margin: 0;
        padding: 0;
        overflow-x: hidden;
    }
    
    .dashboard-container {
        padding: 10px;
        margin: 0;
        width: 100%;
    }
    
    /* Target the parent container */
    .container-fluid,
    .row,
    main,
    .content-area {
        padding-left: 0 !important;
        padding-right: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        width: 100% !important;
    }
    
    /* Adjust sidebar and content */
    .sidebar {
        margin-right: 0;
        padding-right: 0;
    }
    
    /* Content sections responsiveness */
    .welcome-section, 
    .dashboard-summary,
    .mood-summary-section,
    .recent-activity-section {
        width: 100%;
        margin-left: 0;
        margin-right: 0;
        padding-left: 15px;
        padding-right: 15px;
        box-sizing: border-box;
    }
    
    .dashboard-summary {
        grid-template-columns: 1fr;
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .summary-card {
        margin-left: 0;
        margin-right: 0;
        width: 100%;
    }
}

@media (max-width: 480px) {
    .welcome-section h1 {
        font-size: 1.5rem;
    }
    
    .welcome-date {
        font-size: 0.9rem;
    }
    
    .mood-item {
        width: calc(50% - 10px);
        max-width: none;
    }
    
    .card-header h3 {
        font-size: 1rem;
    }
    
    .icon-circle {
        width: 30px;
        height: 30px;
        font-size: 0.8rem;
    }
    
    .mood-emoji {
        font-size: 2.5rem;
    }
    
    .mood-label {
        font-size: 1.1rem;
    }
    
    .streak-count {
        font-size: 2.5rem;
    }
    
    .streak-text {
        font-size: 1rem;
    }
    
    .count-num {
        font-size: 2.5rem;
    }
    
    .count-label {
        font-size: 1rem;
    }
    
    .action-btn {
        padding: 10px 12px;
        font-size: 0.9rem;
    }
    
    .mood-summary-section h2,
    .recent-activity-section h2 {
        font-size: 1.2rem;
        margin-bottom: 20px;
    }
    
    .timeline-mood {
        font-size: 0.95rem;
    }
    
    .timeline-text {
        font-size: 0.85rem;
    }
    
    .dashboard-summary {
        gap: 8px;
        margin-bottom: 10px;
    }
    
    .mood-summary-section,
    .recent-activity-section {
        margin-bottom: 10px;
        padding: 12px;
    }
}

@media (max-width: 360px) {
    .mood-item {
        min-width: 60px;
    }
    
    .mood-emoji {
        font-size: 2rem;
    }
    
    .mood-percent {
        font-size: 0.8rem;
    }
    
    .mood-name {
        font-size: 0.7rem;
    }
}
</style>

<script>
// Initialize any client-side dashboard features here
document.addEventListener('DOMContentLoaded', function() {
    // Animated count-up for statistics if needed
    // Add any interactive features
    
    // Make cards have staggered animation
    const cards = document.querySelectorAll('.summary-card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '1';
        }, 100 * index);
    });
});
</script>
