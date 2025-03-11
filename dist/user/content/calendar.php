<?php
// Initialize session if needed
session_start();

// Check if user is logged in
if(!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    echo "Authorization required";
    exit;
}

// Load the Mood model
require_once '../../../models/Mood.php';
$mood = new Mood();
$userId = $_SESSION['user_id'] ?? 0;

// Get selected month and year (default to current month)
$selectedMonth = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Validate month and year values
if ($selectedMonth < 1 || $selectedMonth > 12) {
    $selectedMonth = date('n');
}
if ($selectedYear < 2000 || $selectedYear > 2100) {
    $selectedYear = date('Y');
}

// Get calendar data
$daysInMonth = date('t', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear));
$firstDayOfMonth = date('N', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear));
$monthName = date('F', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear));

// Get moods for the selected month
$userMoods = $mood->getUserMoodsByMonth($userId, $selectedMonth, $selectedYear);

// Create associative array with day => mood data
$moodsByDay = [];
if ($userMoods) {
    foreach ($userMoods as $moodEntry) {
        $day = date('j', strtotime($moodEntry['created_at']));
        $moodsByDay[$day] = $moodEntry;
    }
}

// Define mood-to-emoji mapping
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

// Get previous and next month/year values for navigation
$prevMonth = $selectedMonth - 1;
$prevYear = $selectedYear;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}

$nextMonth = $selectedMonth + 1;
$nextYear = $selectedYear;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}

// Get mood stats for the selected month
$moodStats = $mood->getMoodStatsByMonth($userId, $selectedMonth, $selectedYear);
$moodCounts = [];
$totalEntries = 0;
$topMood = null;
$topMoodCount = 0;

if ($moodStats) {
    foreach ($moodStats as $stat) {
        $moodCounts[$stat['mood_type']] = $stat['count'];
        $totalEntries += $stat['count'];
        
        if ($stat['count'] > $topMoodCount) {
            $topMood = $stat['mood_type'];
            $topMoodCount = $stat['count'];
        }
    }
}

// Calculate logging streak (simplified approach)
$streak = $mood->getUserLoggingStreak($userId);
?>

<div class="header">
    <h1 class="page-title" style="color: #d1789c; font-size: 1.8rem; margin-bottom: 15px; position: relative; display: inline-block; font-weight: 600;">
        Mood Calendar
        <span style="position: absolute; bottom: -8px; left: 0; width: 40%; height: 3px; background: linear-gradient(90deg, #d1789c, #f5d7e3); border-radius: 3px;"></span>
    </h1>
</div>

<div class="card" style="padding: 0; overflow: hidden; margin-bottom: 30px; border-radius: 16px; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07);">
    <div style="padding: 20px; background-color: #f8dfeb; text-align: center; display: flex; justify-content: space-between; align-items: center;">
        <a href="?page=calendar&month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" 
           style="color: #d1789c; font-size: 1.2rem; text-decoration: none; display: flex; align-items: center;">
            <i class="fas fa-chevron-left"></i>
        </a>
        <h2 style="margin: 0; color: #4a3347; font-weight: 600;"><?php echo $monthName . ' ' . $selectedYear; ?></h2>
        <a href="?page=calendar&month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" 
           style="color: #d1789c; font-size: 1.2rem; text-decoration: none; display: flex; align-items: center;">
            <i class="fas fa-chevron-right"></i>
        </a>
    </div>
    
    <div style="padding: 20px;">
        <!-- Calendar grid -->
        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 10px; text-align: center;">
            <!-- Days of week headers -->
            <div style="font-weight: 500; color: #6e3b5c; padding: 10px 0;">Mon</div>
            <div style="font-weight: 500; color: #6e3b5c; padding: 10px 0;">Tue</div>
            <div style="font-weight: 500; color: #6e3b5c; padding: 10px 0;">Wed</div>
            <div style="font-weight: 500; color: #6e3b5c; padding: 10px 0;">Thu</div>
            <div style="font-weight: 500; color: #6e3b5c; padding: 10px 0;">Fri</div>
            <div style="font-weight: 500; color: #6e3b5c; padding: 10px 0;">Sat</div>
            <div style="font-weight: 500; color: #6e3b5c; padding: 10px 0;">Sun</div>
            
            <?php
            // Empty cells for days before the 1st of the month
            for ($i = 1; $i < $firstDayOfMonth; $i++) {
                echo '<div style="height: 80px;"></div>';
            }
            
            // Calendar days
            for ($day = 1; $day <= $daysInMonth; $day++) {
                // Check if we have a mood for this day
                $hasMood = isset($moodsByDay[$day]);
                $mood = $hasMood ? $moodsByDay[$day]['mood_type'] : null;
                $emoji = $hasMood ? ($moodEmojis[$mood] ?? '❓') : '';
                $moodText = $hasMood ? $moodsByDay[$day]['mood_text'] : '';
                
                // Today has special highlighting
                $isToday = ($day == date('j') && $selectedMonth == date('n') && $selectedYear == date('Y'));
                $bgColor = $isToday ? '#ffebf3' : ($hasMood ? '#fff9fb' : 'white');
                $borderColor = $isToday ? '#ff8fb1' : ($hasMood ? '#fde1ec' : '#e0e0e0');
                
                echo '<div style="height: 80px; border: 1px solid ' . $borderColor . '; border-radius: 10px; background-color: ' . $bgColor . '; display: flex; flex-direction: column; justify-content: space-between; padding: 5px; position: relative; overflow: hidden;">';
                
                // Show day number
                echo '<div style="text-align: right; font-size: 12px; font-weight: ' . ($isToday ? '600' : '400') . ';">' . $day . '</div>';
                
                // Show mood emoji if exists
                if ($hasMood) {
                    echo '<div style="font-size: 28px; display: flex; align-items: center; justify-content: center; height: 40px; cursor: pointer;" 
                              title="' . htmlspecialchars($mood) . (empty($moodText) ? '' : ': ' . htmlspecialchars($moodText)) . '">' 
                          . $emoji . 
                         '</div>';
                    
                    // Add a small indicator of the mood type
                    echo '<div style="font-size: 9px; text-align: center; color: #888; text-transform: capitalize; margin-top: -5px;">' . $mood . '</div>';
                } else {
                    echo '<div style="height: 40px;"></div>';
                }
                
                echo '</div>';
            }
            ?>
        </div>
    </div>
</div>

<div class="mood-chart">
    <div class="section-header" style="margin-bottom: 20px;">
        <h2 class="section-title" style="color: #d1789c; font-size: 1.4rem; font-weight: 500; position: relative; display: inline-block;">
            <?php echo $monthName; ?> Summary
            <span style="position: absolute; bottom: -5px; left: 0; width: 50%; height: 2px; background: linear-gradient(90deg, #d1789c, transparent); border-radius: 2px;"></span>
        </h2>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; margin-top: 20px;">
        <?php if ($totalEntries > 0): ?>
            <div class="card" style="margin-bottom: 0; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border-radius: 16px; padding: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h2 style="font-size: 1.1rem; color: #6e3b5c; margin: 0; font-weight: 500;">Dominant Mood</h2>
                    <div style="color: #d1789c; background-color: #fff3f8; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                </div>
                <div style="font-size: 2rem; margin: 15px 0; display: flex; align-items: center;">
                    <?php echo isset($moodEmojis[$topMood]) ? $moodEmojis[$topMood] : '❓'; ?> 
                    <span style="font-size: 1.2rem; margin-left: 10px; text-transform: capitalize; color: #4a3347;"><?php echo $topMood; ?></span>
                </div>
                <div style="font-size: 0.9rem; color: #888;">
                    <?php echo round(($topMoodCount / $totalEntries) * 100); ?>% of your entries
                </div>
            </div>
        <?php endif; ?>
        
        <div class="card" style="margin-bottom: 0; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border-radius: 16px; padding: 25px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2 style="font-size: 1.1rem; color: #6e3b5c; margin: 0; font-weight: 500;">Tracking Score</h2>
                <div style="color: #d1789c; background-color: #fff3f8; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
            <div style="font-size: 2rem; margin: 15px 0; color: #4a3347;">
                <?php echo $totalEntries; ?><span style="font-size: 1rem; color: #888;">/<?php echo $daysInMonth; ?></span>
            </div>
            <div style="font-size: 0.9rem; color: #888;">
                <?php echo round(($totalEntries / $daysInMonth) * 100); ?>% of days tracked
            </div>
        </div>
        
        <div class="card" style="margin-bottom: 0; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border-radius: 16px; padding: 25px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2 style="font-size: 1.1rem; color: #6e3b5c; margin: 0; font-weight: 500;">Logging Streak</h2>
                <div style="color: #d1789c; background-color: #fff3f8; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                    <i class="fas fa-fire"></i>
                </div>
            </div>
            <div style="font-size: 2rem; margin: 15px 0; color: #4a3347;">
                <?php echo $streak; ?> <span style="font-size: 1.2rem;">Days</span>
            </div>
            <div style="font-size: 0.9rem; color: #888;">
                <?php 
                if ($streak >= 7) {
                    echo 'Excellent consistency!';
                } elseif ($streak >= 3) {
                    echo 'Good progress!';
                } else {
                    echo 'Keep going!';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .card {
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
        
        h1.page-title {
            font-size: 1.6rem !important;
            text-align: center;
            display: block !important;
            margin-left: auto;
            margin-right: auto;
        }
        
        h1.page-title span {
            left: 50% !important;
            transform: translateX(-50%) !important;
        }
    }
    
    @media (max-width: 576px) {
        div[style*="grid-template-columns: repeat(7, 1fr)"] > div {
            font-size: 12px !important;
        }
        
        div[style*="grid-template-columns: repeat(7, 1fr)"] {
            gap: 5px !important;
        }
        
        div[style*="height: 80px"] {
            height: 65px !important;
        }
        
        div[style*="font-size: 28px"] {
            font-size: 22px !important;
        }
    }
</style> 