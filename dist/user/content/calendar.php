<?php
// Initialize session if needed
session_start();

// Check if user is logged in
if(!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    echo "Authorization required";
    exit;
}

// Set timezone to UTC+8
date_default_timezone_set('Asia/Singapore');

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

// Get the first and last day of the month for date-based queries
$firstDayOfSelectedMonth = $selectedYear . '-' . str_pad($selectedMonth, 2, '0', STR_PAD_LEFT) . '-01';
$lastDayOfSelectedMonth = date('Y-m-t', strtotime($firstDayOfSelectedMonth));

// Get previous month data for comparison
$prevAnalysisMonth = $selectedMonth - 1;
$prevAnalysisYear = $selectedYear;
if ($prevAnalysisMonth < 1) {
    $prevAnalysisMonth = 12;
    $prevAnalysisYear--;
}

// Get mood trends - comparison with previous month
$currentMonthAvg = $mood->getAverageMoodScore($userId, $selectedMonth, $selectedYear);
$prevMonthAvg = $mood->getAverageMoodScore($userId, $prevAnalysisMonth, $prevAnalysisYear);

// Calculate trend
$trendPercentage = 0;
$trendDirection = 'stable';
if ($prevMonthAvg > 0 && $currentMonthAvg > 0) {
    $trendPercentage = round((($currentMonthAvg - $prevMonthAvg) / $prevMonthAvg) * 100);
    $trendDirection = $trendPercentage > 0 ? 'improving' : ($trendPercentage < 0 ? 'declining' : 'stable');
}

// Get best and worst days
$bestDay = $mood->getBestDay($userId, $selectedMonth, $selectedYear);
$worstDay = $mood->getWorstDay($userId, $selectedMonth, $selectedYear);

// Get day of week patterns
$dayOfWeekPatterns = $mood->getMoodPatternsByDayOfWeek($userId, $selectedMonth, $selectedYear);
$bestDayOfWeek = '';
$bestDayOfWeekScore = 0;
$dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

if ($dayOfWeekPatterns) {
    foreach ($dayOfWeekPatterns as $pattern) {
        if ($pattern['avg_score'] > $bestDayOfWeekScore) {
            $bestDayOfWeekScore = $pattern['avg_score'];
            $bestDayOfWeek = $dayNames[$pattern['day_of_week'] - 1];
        }
    }
}
?>

<div class="header">
    <h1 class="page-title" style="color: #d1789c; font-size: 1.8rem; margin-bottom: 15px; position: relative; display: inline-block; font-weight: 600;">
        Mood Calendar
        <span style="position: absolute; bottom: -8px; left: 0; width: 40%; height: 3px; background: linear-gradient(90deg, #d1789c, #f5d7e3); border-radius: 3px;"></span>
    </h1>
</div>

<div class="card" style="padding: 0; overflow: hidden; margin-bottom: 30px; border-radius: 16px; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07);">
    <div style="padding: 20px; background-color: #f8dfeb; text-align: center; display: flex; justify-content: space-between; align-items: center;">
        <a href="dashboard.php?page=calendar&month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" 
           style="color: #d1789c; font-size: 1.2rem; text-decoration: none; display: flex; align-items: center; padding: 5px 10px; border-radius: 20px; transition: background-color 0.2s ease;">
            <i class="fas fa-chevron-left"></i>
        </a>
        <h2 style="margin: 0; color: #4a3347; font-weight: 600;"><?php echo $monthName . ' ' . $selectedYear; ?></h2>
        <a href="dashboard.php?page=calendar&month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" 
           style="color: #d1789c; font-size: 1.2rem; text-decoration: none; display: flex; align-items: center; padding: 5px 10px; border-radius: 20px; transition: background-color 0.2s ease;">
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
    
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; margin-top: 20px; margin-bottom: 20px;">
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
            
            <!-- New Card: Mood Trend -->
            <?php if ($currentMonthAvg > 0 && $prevMonthAvg > 0): ?>
            <div class="card" style="margin-bottom: 0; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border-radius: 16px; padding: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h2 style="font-size: 1.1rem; color: #6e3b5c; margin: 0; font-weight: 500;">Mood Trend</h2>
                    <div style="color: #d1789c; background-color: #fff3f8; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div style="font-size: 1.5rem; margin: 15px 0; color: #4a3347; display: flex; align-items: center;">
                    <?php if ($trendPercentage > 0): ?>
                        <i class="fas fa-arrow-up" style="color: #4CAF50; margin-right: 10px;"></i>
                    <?php elseif ($trendPercentage < 0): ?>
                        <i class="fas fa-arrow-down" style="color: #F44336; margin-right: 10px;"></i>
                    <?php else: ?>
                        <i class="fas fa-arrows-alt-h" style="color: #FFC107; margin-right: 10px;"></i>
                    <?php endif; ?>
                    <span style="text-transform: capitalize;"><?php echo $trendDirection; ?></span>
                </div>
                <div style="font-size: 0.9rem; color: #888;">
                    <?php echo abs($trendPercentage); ?>% <?php echo $trendPercentage >= 0 ? 'better' : 'lower'; ?> than last month
                </div>
            </div>
            <?php endif; ?>
            
            <!-- New Card: Best Day -->
            <?php if ($bestDay): ?>
            <div class="card" style="margin-bottom: 0; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border-radius: 16px; padding: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h2 style="font-size: 1.1rem; color: #6e3b5c; margin: 0; font-weight: 500;">Happiest Day</h2>
                    <div style="color: #d1789c; background-color: #fff3f8; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
                <div style="font-size: 1.5rem; margin: 15px 0; color: #4a3347; display: flex; align-items: center;">
                    <?php 
                    $happyDay = date('j', strtotime($bestDay['created_at']));
                    $happyEmoji = isset($moodEmojis[$bestDay['mood_type']]) ? $moodEmojis[$bestDay['mood_type']] : '🤩';
                    echo $happyEmoji . ' ' . $monthName . ' ' . $happyDay;
                    ?>
                </div>
                <div style="font-size: 0.9rem; color: #888; text-transform: capitalize;">
                    Mood: <?php echo $bestDay['mood_type']; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- New Card: Weekly Pattern -->
            <?php if ($bestDayOfWeek): ?>
            <div class="card" style="margin-bottom: 0; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border-radius: 16px; padding: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h2 style="font-size: 1.1rem; color: #6e3b5c; margin: 0; font-weight: 500;">Weekly Pattern</h2>
                    <div style="color: #d1789c; background-color: #fff3f8; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                </div>
                <div style="font-size: 1.5rem; margin: 15px 0; color: #4a3347;">
                    <?php echo $bestDayOfWeek; ?> 🌟
                </div>
                <div style="font-size: 0.9rem; color: #888;">
                    Your best day of the week
                </div>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- No entries message -->
            <div class="card" style="margin-bottom: 0; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border-radius: 16px; padding: 25px; grid-column: 1 / -1;">
                <div style="text-align: center; padding: 30px 20px;">
                    <div style="font-size: 3rem; margin-bottom: 15px; color: #f5d7e3;">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <h3 style="font-size: 1.2rem; color: #6e3b5c; margin-bottom: 10px;">No mood entries for <?php echo $monthName; ?></h3>
                    <p style="color: #888; margin-bottom: 20px;">Start logging your moods to see insights and patterns.</p>
                    <a href="dashboard.php?page=log-mood" style="background: linear-gradient(135deg, #d1789c, #e896b8); color: white; text-decoration: none; padding: 10px 20px; border-radius: 25px; display: inline-block; font-weight: 500;">
                        Log Your Mood
                    </a>
                </div>
            </div>
        <?php endif; ?>
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
    
    /* Enhance month navigation buttons */
    a[href*="?page=calendar"] {
        transition: background-color 0.2s ease;
    }
    
    a[href*="?page=calendar"]:hover {
        background-color: rgba(209, 120, 156, 0.1);
    }
</style> 