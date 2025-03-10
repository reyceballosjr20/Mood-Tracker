<?php
// Initialize session if needed
session_start();

// Check if user is logged in
if(!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    echo "Authorization required";
    exit;
}

// Add this near the top of dashboard.php
require_once '../../../models/Mood.php';

// Get the user's latest mood
$mood = new Mood();
$latest_mood = $mood->getLatestMood($_SESSION['user_id']);

// Get mood for last 7 days to calculate streak
$today = date('Y-m-d');
$week_ago = date('Y-m-d', strtotime('-7 days'));
$recent_moods = $mood->getMoodsByDateRange($_SESSION['user_id'], $week_ago, $today);

// Calculate streak (consecutive days with mood entries)
$streak = 0;
$dates = [];
foreach ($recent_moods as $entry) {
    $date = date('Y-m-d', strtotime($entry['created_at']));
    $dates[$date] = true;
}

// Count consecutive days from today backwards
for ($i = 0; $i < 7; $i++) {
    $check_date = date('Y-m-d', strtotime("-$i days"));
    if (isset($dates[$check_date])) {
        $streak++;
    } else if ($i > 0) { // Allow for today not being logged yet
        break;
    }
}
?>

<div class="header">
    <h1 class="page-title">Dashboard</h1>
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Search...">
    </div>
</div>

<!-- New Mood Logging Section -->
<div class="card" style="margin-bottom: 30px;">
    <div class="card-header">
        <h2 class="card-title">How are you feeling today?</h2>
        <div class="card-icon">
            <i class="fas fa-heart-pulse"></i>
        </div>
    </div>
    <div class="card-content">
        <div id="moodSelector" style="display: flex; justify-content: space-between; flex-wrap: wrap; margin-bottom: 15px;">
            <button class="mood-btn" data-mood="very-happy">
                <span style="font-size: 32px;">😄</span>
                <span>Very Happy</span>
            </button>
            <button class="mood-btn" data-mood="happy">
                <span style="font-size: 32px;">🙂</span>
                <span>Happy</span>
            </button>
            <button class="mood-btn" data-mood="neutral">
                <span style="font-size: 32px;">😐</span>
                <span>Neutral</span>
            </button>
            <button class="mood-btn" data-mood="sad">
                <span style="font-size: 32px;">🙁</span>
                <span>Sad</span>
            </button>
            <button class="mood-btn" data-mood="very-sad">
                <span style="font-size: 32px;">😢</span>
                <span>Very Sad</span>
            </button>
        </div>
        <div id="moodNoteContainer" style="display: none; margin-top: 15px;">
            <textarea id="moodNote" placeholder="What's making you feel this way? (optional)" style="width: 100%; padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; resize: none; height: 80px;"></textarea>
            <button id="saveMood" style="background: #ff8fb1; border: none; color: white; padding: 10px 15px; border-radius: 5px; cursor: pointer; margin-top: 10px;">
                Save Mood
            </button>
        </div>
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
        <div class="card-content">
            <?php if ($latest_mood): ?>
                <?php 
                // Map mood type to emoji
                $mood_icons = [
                    'sad' => '<i class="fas fa-sad-tear"></i>',
                    'unhappy' => '<i class="fas fa-frown"></i>',
                    'neutral' => '<i class="fas fa-meh"></i>',
                    'good' => '<i class="fas fa-smile"></i>',
                    'energetic' => '<i class="fas fa-dumbbell"></i>',
                    'excellent' => '<i class="fas fa-laugh-beam"></i>',
                    'anxious' => '<i class="fas fa-bolt"></i>',
                    'tired' => '<i class="fas fa-bed"></i>',
                    'focused' => '<i class="fas fa-bullseye"></i>'
                ];
                $icon = $mood_icons[$latest_mood['mood_type']] ?? '<i class="fas fa-smile"></i>';
                ?>
                <span style="font-size: 24px; margin-right: 10px;"><?php echo $icon; ?></span>
                <?php echo ucfirst($latest_mood['mood_type']); ?>
            <?php else: ?>
                No mood logged yet
            <?php endif; ?>
        </div>
        <div class="card-footer">
            <?php if ($latest_mood): ?>
                Last updated <?php echo date('M d, h:i A', strtotime($latest_mood['created_at'])); ?>
            <?php else: ?>
                Try logging your mood today!
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Mood Streaks</h2>
            <div class="card-icon">
                <i class="fas fa-fire"></i>
            </div>
        </div>
        <div class="card-content"><?php echo $streak; ?> Days</div>
        <div class="card-footer">Keep your streak going!</div>
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

<!-- Add styles and JavaScript for the mood selector -->
<style>
    .mood-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 18%;
        min-width: 75px;
        padding: 12px 5px;
        border: 2px solid #f0f0f0;
        background: white;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-bottom: 10px;
    }
    
    .mood-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .mood-btn.selected {
        border-color: #ff8fb1;
        background-color: #fff5f8;
        box-shadow: 0 4px 8px rgba(255, 143, 177, 0.2);
    }
    
    .mood-btn span:last-child {
        margin-top: 8px;
        font-size: 12px;
    }
    
    @media (max-width: 768px) {
        .mood-btn {
            width: 30%;
        }
    }
    
    @media (max-width: 480px) {
        .mood-btn {
            width: 45%;
        }
    }
</style>

<script>
    // Mood selection functionality
    document.addEventListener('DOMContentLoaded', function() {
        const moodButtons = document.querySelectorAll('.mood-btn');
        const moodNoteContainer = document.getElementById('moodNoteContainer');
        const moodNote = document.getElementById('moodNote');
        const saveMoodBtn = document.getElementById('saveMood');
        let selectedMood = null;
        
        // Add click handlers to mood buttons
        moodButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove selected class from all buttons
                moodButtons.forEach(b => b.classList.remove('selected'));
                
                // Add selected class to clicked button
                this.classList.add('selected');
                
                // Store selected mood
                selectedMood = this.dataset.mood;
                
                // Show note container
                moodNoteContainer.style.display = 'block';
            });
        });
        
        // Handle save mood
        saveMoodBtn.addEventListener('click', function() {
            if (!selectedMood) return;
            
            // Here you would normally make an AJAX call to save the mood
            // For demo purposes, we'll just show a success message
            
            // Get the emoji from the selected button
            const emojiElement = document.querySelector('.mood-btn.selected span:first-child');
            const emoji = emojiElement ? emojiElement.textContent : '';
            
            // Update the current mood card
            const currentMoodContent = document.querySelector('.dashboard-cards .card:first-child .card-content');
            if (currentMoodContent) {
                currentMoodContent.innerHTML = emoji + ' ' + selectedMood.replace('-', ' ');
            }
            
            const currentMoodFooter = document.querySelector('.dashboard-cards .card:first-child .card-footer');
            if (currentMoodFooter) {
                const now = new Date();
                const timeString = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                currentMoodFooter.textContent = 'Last updated today at ' + timeString;
            }
            
            // Show success message
            alert('Mood saved successfully!');
            
            // Reset the form
            moodButtons.forEach(b => b.classList.remove('selected'));
            moodNoteContainer.style.display = 'none';
            moodNote.value = '';
            selectedMood = null;
        });
    });
</script> 

