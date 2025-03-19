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

// Get current year
$currentYear = date('Y');

// Define all 12 months
$calendarMonths = [];
for ($month = 1; $month <= 12; $month++) {
    $calendarMonths[$month - 1] = [
        'month' => $month,
        'year' => $currentYear
    ];
}

// Update the mood icons mapping to match new emotions
$moodIcons = [
    'happy' => 'fa-smile-beam',
    'sad' => 'fa-sad-tear',
    'angry' => 'fa-angry',
    'anxious' => 'fa-bolt',
    'stressed' => 'fa-head-side-virus',
    'calm' => 'fa-peace',
    'tired' => 'fa-bed',
    'energetic' => 'fa-bolt',
    'neutral' => 'fa-meh',
    'excited' => 'fa-grin-stars',
    'frustrated' => 'fa-angry',
    'grateful' => 'fa-heart'
];

// Update emoji mapping for fallback
$moodEmojis = [
    'happy' => '😊',
    'sad' => '😢',
    'angry' => '😠',
    'anxious' => '😰',
    'stressed' => '😫',
    'calm' => '😌',
    'tired' => '😴',
    'energetic' => '⚡',
    'neutral' => '😐',
    'excited' => '🤩',
    'frustrated' => '😤',
    'grateful' => '🙏'
];

// Get mood stats for the entire year by combining monthly stats
$moodCounts = [];
$totalEntries = 0;
$topMood = null;
$topMoodCount = 0;

// Iterate through all months of the current year
for ($month = 1; $month <= 12; $month++) {
    $monthStats = $mood->getMoodStatsByMonth($userId, $month, $currentYear);
    
    if ($monthStats) {
        foreach ($monthStats as $stat) {
            if (!isset($moodCounts[$stat['mood_type']])) {
                $moodCounts[$stat['mood_type']] = 0;
            }
            $moodCounts[$stat['mood_type']] += $stat['count'];
            $totalEntries += $stat['count'];
            
            // Update top mood if necessary
            if ($moodCounts[$stat['mood_type']] > $topMoodCount) {
                $topMood = $stat['mood_type'];
                $topMoodCount = $moodCounts[$stat['mood_type']];
            }
        }
    }
}

// Calculate logging streak
$streak = $mood->getUserLoggingStreak($userId);

// Calculate year averages for each month
$monthlyAverages = [];
$yearAvg = 0;
$monthCount = 0;

for ($month = 1; $month <= 12; $month++) {
    $monthlyAverages[$month] = $mood->getAverageMoodScore($userId, $month, $currentYear);
    if ($monthlyAverages[$month] > 0) {
        $yearAvg += $monthlyAverages[$month];
        $monthCount++;
    }
}

// Calculate overall year average
$yearAvg = $monthCount > 0 ? $yearAvg / $monthCount : 0;

// Get previous year average using the same approach
$prevYearAvg = 0;
$prevMonthCount = 0;

for ($month = 1; $month <= 12; $month++) {
    $monthAvg = $mood->getAverageMoodScore($userId, $month, $currentYear - 1);
    if ($monthAvg > 0) {
        $prevYearAvg += $monthAvg;
        $prevMonthCount++;
    }
}

$prevYearAvg = $prevMonthCount > 0 ? $prevYearAvg / $prevMonthCount : 0;

// Calculate trend
$trendPercentage = 0;
$trendDirection = 'stable';
if ($prevYearAvg > 0 && $yearAvg > 0) {
    $trendPercentage = round((($yearAvg - $prevYearAvg) / $prevYearAvg) * 100);
    $trendDirection = $trendPercentage > 0 ? 'improving' : ($trendPercentage < 0 ? 'declining' : 'stable');
}

// Find best and worst days by checking each month
$bestDay = null;
$worstDay = null;
$bestDayMoodScore = 0;
$worstDayMoodScore = 10; // High initial value to be replaced

for ($month = 1; $month <= 12; $month++) {
    $monthBestDay = $mood->getBestDay($userId, $month, $currentYear);
    $monthWorstDay = $mood->getWorstDay($userId, $month, $currentYear);
    
    // Process best day if found
    if ($monthBestDay) {
        $bestDay = $bestDay ? $bestDay : $monthBestDay;
    }
    
    // Process worst day if found
    if ($monthWorstDay) {
        $worstDay = $worstDay ? $worstDay : $monthWorstDay;
    }
}

// Get day of week patterns
$dayOfWeekPatterns = [];
$bestDayOfWeek = '';
$bestDayOfWeekScore = 0;
$dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

// We would need to implement day of week calculation manually here
// For now, just initializing the variable
?>

<!-- Navigation Header Area -->
<div class="header">
    <h1 class="page-title" style="color: #d1789c; font-size: 1.8rem; margin-bottom: 15px; position: relative; display: inline-block; font-weight: 600;">
        Mood Calendar <?php echo $currentYear; ?>
        <span style="position: absolute; bottom: -8px; left: 0; width: 40%; height: 3px; background: linear-gradient(90deg, #d1789c, #f5d7e3); border-radius: 3px;"></span>
    </h1>
</div>

<!-- Year Overview Grid -->
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px;">
    <?php foreach ($calendarMonths as $index => $calendarData): ?>
        <?php
        // Extract month data
        $month = $calendarData['month'];
        $year = $calendarData['year'];
        $monthName = date('F', mktime(0, 0, 0, $month, 1, $year));
        $daysInMonth = date('t', mktime(0, 0, 0, $month, 1, $year));
        $firstDayOfMonth = date('N', mktime(0, 0, 0, $month, 1, $year));
        
        // Get moods for this specific month
        $userMoods = $mood->getUserMoodsByMonth($userId, $month, $year);
        
        // Create associative array with day => mood data
        $moodsByDay = [];
        if ($userMoods) {
            foreach ($userMoods as $moodEntry) {
                $day = date('j', strtotime($moodEntry['created_at']));
                $moodsByDay[$day] = $moodEntry;
            }
        }
        ?>
        
        <!-- Each month as a separate card -->
        <div class="card month-calendar" style="padding: 20px; background-color: white; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border-radius: 16px; overflow: hidden;">
            <!-- Month header -->
            <div style="text-align: center; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f5d7e3;">
                <h3 style="color: #d1789c; font-size: 1.1rem; margin: 0; font-weight: 500;">
                    <?php echo $monthName; ?>
                </h3>
            </div>
            
            <!-- Day headers -->
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; margin-bottom: 10px; font-weight: 500; color: #6e3b5c; font-size: 0.8rem;">
                <div style="padding: 4px 0;">M</div>
                <div style="padding: 4px 0;">T</div>
                <div style="padding: 4px 0;">W</div>
                <div style="padding: 4px 0;">T</div>
                <div style="padding: 4px 0;">F</div>
                <div style="padding: 4px 0;">S</div>
                <div style="padding: 4px 0;">S</div>
            </div>
            
            <!-- Calendar days -->
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px;">
                <?php 
                // Add empty cells for days before the 1st of the month
                for ($i = 1; $i < $firstDayOfMonth; $i++) {
                    echo '<div style="height: 40px;"></div>';
                }
                
                // Add days of the month
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $isToday = ($day == date('j') && $month == date('n') && $year == date('Y'));
                    $hasMood = isset($moodsByDay[$day]);
                    
                    $dayStyle = 'height: 40px; border-radius: 8px; display: flex; flex-direction: column; align-items: center; justify-content: space-evenly; position: relative; padding: 3px 0;';
                    
                    if ($isToday) {
                        $dayStyle .= 'border: 2px solid #d1789c; background-color: #fff3f8;';
                    } else {
                        $dayStyle .= 'border: 1px solid #eee; background-color: white;';
                    }
                    
                    echo '<div style="' . $dayStyle . '">';
                    echo '<div style="font-size: 0.75rem; font-weight: 500;">' . $day . '</div>';
                    
                    if ($hasMood) {
                        $moodType = $moodsByDay[$day]['mood_type'];
                        $moodIcon = isset($moodIcons[$moodType]) ? $moodIcons[$moodType] : 'fa-meh';
                        $moodText = $moodsByDay[$day]['mood_text'];
                        
                        echo '<div class="mood-entry" style="font-size: 0.9rem; cursor: pointer;">';
                        echo '<div class="calendar-mood-icon" data-mood="' . $moodType . '">';
                        echo '<i class="fas ' . ($moodIcons[$moodType] ?? 'fa-meh') . '"></i>';
                        echo '</div>';
                        
                        // Add tooltip with mood details
                        if (!empty($moodText)) {
                            echo '<div class="mood-tooltip">';
                            echo '<div style="font-weight: 500; margin-bottom: 3px; color: #4a3347; text-transform: capitalize;">' . $moodType . '</div>';
                            echo '<div style="font-size: 0.75rem; color: #666;">"' . htmlspecialchars($moodText) . '"</div>';
                            echo '</div>';
                        }
                        
                        echo '</div>';
                    } else {
                        echo '<div style="height: 20px;"></div>';
                    }
                    
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
    /* Calendar grid responsive styles */
    .month-calendar {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        min-height: 300px;
    }
    
    .month-calendar:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.1);
    }
    
    @media (max-width: 1200px) {
        div[style*="grid-template-columns: repeat(3, 1fr)"] {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }
    
    @media (max-width: 768px) {
        div[style*="grid-template-columns: repeat(3, 1fr)"] {
            grid-template-columns: 1fr !important;
        }
        
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
        div[style*="display: grid; grid-template-columns: repeat(7, 1fr)"] > div {
            font-size: 10px !important;
        }
        
        div[style*="display: grid; grid-template-columns: repeat(7, 1fr)"] {
            gap: 2px !important;
        }
        
        div[style*="height: 40px"] {
            height: 35px !important;
        }
        
        div[style*="font-size: 0.9rem"] {
            font-size: 0.8rem !important;
        }
    }
    
    /* Tooltip styles */
    .mood-tooltip {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background-color: #fff;
        border-radius: 8px;
        padding: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        width: max-content;
        max-width: 180px;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
        z-index: 10;
    }
    
    .mood-entry {
        position: relative;
    }
    
    .mood-entry:hover .mood-tooltip {
        opacity: 1;
        visibility: visible;
    }
    
    /* New styles for mood icons */
    .calendar-mood-icon {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: linear-gradient(135deg, #feeef5, #ffffff);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 5px rgba(209, 120, 156, 0.2);
        margin: 0 auto;
    }
    
    .calendar-mood-icon i {
        font-size: 12px;
        color: #d1789c;
    }
    
    /* Add color coding based on mood type */
    .calendar-mood-icon[data-mood="happy"],
    .calendar-mood-icon[data-mood="excited"],
    .calendar-mood-icon[data-mood="grateful"] {
        background: linear-gradient(135deg, #feeef5, #ffe8d6);
    }
    .calendar-mood-icon[data-mood="sad"],
    .calendar-mood-icon[data-mood="angry"],
    .calendar-mood-icon[data-mood="frustrated"] {
        background: linear-gradient(135deg, #f5e6e6, #fee6e3);
    }
    .calendar-mood-icon[data-mood="anxious"],
    .calendar-mood-icon[data-mood="stressed"] {
        background: linear-gradient(135deg, #e6f5f5, #e3eefe);
    }
    .calendar-mood-icon[data-mood="calm"],
    .calendar-mood-icon[data-mood="neutral"] {
        background: linear-gradient(135deg, #e6f5eb, #e3feef);
    }
    .calendar-mood-icon[data-mood="tired"] {
        background: linear-gradient(135deg, #f5f5f5, #efefef);
    }
    .calendar-mood-icon[data-mood="energetic"] {
        background: linear-gradient(135deg, #fff3d6, #ffedd6);
    }
</style>

<!-- Load the calendar script -->
<script src="../js/calendar.js"></script> 