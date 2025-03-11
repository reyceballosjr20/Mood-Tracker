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

// Define the 4 months to display (2x2 grid)
$calendarMonths = [];

// Current month (top left)
$calendarMonths[0] = [
    'month' => $selectedMonth,
    'year' => $selectedYear
];

// Next month (top right)
$nextMonth = $selectedMonth + 1;
$nextYear = $selectedYear;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}
$calendarMonths[1] = [
    'month' => $nextMonth,
    'year' => $nextYear
];

// Month after next (bottom left)
$nextMonth2 = $nextMonth + 1;
$nextYear2 = $nextYear;
if ($nextMonth2 > 12) {
    $nextMonth2 = 1;
    $nextYear2++;
}
$calendarMonths[2] = [
    'month' => $nextMonth2,
    'year' => $nextYear2
];

// Month after that (bottom right)
$nextMonth3 = $nextMonth2 + 1;
$nextYear3 = $nextYear2;
if ($nextMonth3 > 12) {
    $nextMonth3 = 1;
    $nextYear3++;
}
$calendarMonths[3] = [
    'month' => $nextMonth3,
    'year' => $nextYear3
];

// Get previous month for navigation
$prevMonth = $selectedMonth - 1;
$prevYear = $selectedYear;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
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

// Get mood stats for the selected month (for summary cards)
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

// Get mood trends, best day, etc. (all the existing calculations)
// ...

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

// At the top of the file, add a debug line to see what's actually being selected:
// echo "Debug: Selected Month/Year: $selectedMonth/$selectedYear";

// We need to ensure the summary is always showing the selected month (first month in grid)
$monthName = date('F', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear));

// Make sure the year is correct for display
$displayYear = $selectedYear;
?>

<!-- Navigation Header Area - Remove dropdown -->
<div class="header">
    <h1 class="page-title" style="color: #d1789c; font-size: 1.8rem; margin-bottom: 15px; position: relative; display: inline-block; font-weight: 600;">
        Mood Calendar
        <span style="position: absolute; bottom: -8px; left: 0; width: 40%; height: 3px; background: linear-gradient(90deg, #d1789c, #f5d7e3); border-radius: 3px;"></span>
    </h1>
    <!-- Remove the month selection dropdown entirely -->
</div>

<!-- Month Navigation Controls -->
<div class="card" style="padding: 20px; background-color: #f8dfeb; margin-bottom: 20px; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border-radius: 16px; text-align: center; display: flex; justify-content: space-between; align-items: center;">
    <a href="javascript:void(0)" id="prevMonthBtn" data-month="<?php echo $prevMonth; ?>" data-year="<?php echo $prevYear; ?>" 
       style="color: #6e3b5c; text-decoration: none; display: flex; align-items: center; padding: 8px 15px; border-radius: 20px; transition: background-color 0.3s ease;">
        <i class="fas fa-chevron-left" style="margin-right: 5px;"></i>
        <?php echo date('M', mktime(0, 0, 0, $prevMonth, 1, $prevYear)); ?>
    </a>
    
    <h2 style="margin: 0; color: #4a3347; font-size: 1.4rem;">
        <?php 
        // Show current month only 
        echo date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear));
        ?>
    </h2>
    
    <a href="javascript:void(0)" id="nextMonthBtn" data-month="<?php echo $nextMonth; ?>" data-year="<?php echo $nextYear; ?>" 
       style="color: #6e3b5c; text-decoration: none; display: flex; align-items: center; padding: 8px 15px; border-radius: 20px; transition: background-color 0.3s ease;">
        <?php echo date('M', mktime(0, 0, 0, $nextMonth, 1, $nextYear)); ?>
        <i class="fas fa-chevron-right" style="margin-left: 5px;"></i>
    </a>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
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
        <div class="card month-calendar" style="padding: 25px; background-color: white; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border-radius: 16px; overflow: hidden;">
            <!-- Month header with more padding -->
            <div style="text-align: center; margin-bottom: 20px; padding-bottom: 18px; border-bottom: 1px solid #f5d7e3;">
                <h3 style="color: #d1789c; font-size: 1.2rem; margin: 0; font-weight: 500;">
                    <?php echo $monthName . ' ' . $year; ?>
                </h3>
            </div>
            
            <!-- Day headers - with more height -->
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; margin-bottom: 15px; font-weight: 500; color: #6e3b5c;">
                <div style="padding: 8px 0;">M</div>
                <div style="padding: 8px 0;">T</div>
                <div style="padding: 8px 0;">W</div>
                <div style="padding: 8px 0;">T</div>
                <div style="padding: 8px 0;">F</div>
                <div style="padding: 8px 0;">S</div>
                <div style="padding: 8px 0;">S</div>
            </div>
            
            <!-- Calendar days - increased height -->
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px;">
                <?php 
                // Add empty cells for days before the 1st of the month
                for ($i = 1; $i < $firstDayOfMonth; $i++) {
                    echo '<div style="height: 55px;"></div>';
                }
                
                // Add days of the month with increased height
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $isToday = ($day == date('j') && $month == date('n') && $year == date('Y'));
                    $hasMood = isset($moodsByDay[$day]);
                    
                    $dayStyle = 'height: 55px; border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: space-evenly; position: relative; padding: 5px 0;';
                    
                    if ($isToday) {
                        $dayStyle .= 'border: 2px solid #d1789c; background-color: #fff3f8;';
                    } else {
                        $dayStyle .= 'border: 1px solid #eee; background-color: white;';
                    }
                    
                    echo '<div style="' . $dayStyle . '">';
                    echo '<div style="font-size: 0.85rem; font-weight: 500;">' . $day . '</div>';
                    
                    if ($hasMood) {
                        $moodType = $moodsByDay[$day]['mood_type'];
                        $moodIcon = isset($moodIcons[$moodType]) ? $moodIcons[$moodType] : 'fa-meh';
                        $moodText = $moodsByDay[$day]['mood_text'];
                        
                        echo '<div class="mood-entry" style="font-size: 1rem; cursor: pointer;">';
                        echo '<div class="calendar-mood-icon" data-mood="' . $moodType . '">';
                        echo '<i class="fas ' . ($moodIcons[$moodType] ?? 'fa-meh') . '"></i>';
                        echo '</div>';
                        
                        // Add tooltip with mood details
                        if (!empty($moodText)) {
                            echo '<div class="mood-tooltip">';
                            echo '<div style="font-weight: 500; margin-bottom: 5px; color: #4a3347; text-transform: capitalize;">' . $moodType . '</div>';
                            echo '<div style="font-size: 0.85rem; color: #666;">"' . htmlspecialchars($moodText) . '"</div>';
                            echo '</div>';
                        }
                        
                        echo '</div>';
                    } else {
                        // Add an empty space holder to maintain consistent height
                        echo '<div style="height: 24px;"></div>';
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
        min-height: 400px; /* Set a minimum height for month cards */
    }
    
    .month-calendar:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.1);
    }
    
    @media (max-width: 992px) {
        .month-calendar {
            margin-bottom: 20px;
        }
    }
    
    @media (max-width: 768px) {
        div[style*="grid-template-columns: 1fr 1fr"] {
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
            gap: 4px !important;
        }
        
        div[style*="height: 55px"] {
            height: 45px !important;
        }
        
        div[style*="font-size: 1rem"] {
            font-size: 0.9rem !important;
        }
    }
    
    /* Enhance month navigation buttons */
    a[href="#"] {
        transition: background-color 0.2s ease;
    }
    
    a[href="#"]:hover {
        background-color: rgba(209, 120, 156, 0.2);
    }
    
    /* Tooltip styles */
    .mood-tooltip {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background-color: #fff;
        border-radius: 8px;
        padding: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        width: max-content;
        max-width: 200px;
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
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: linear-gradient(135deg, #feeef5, #ffffff);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 5px rgba(209, 120, 156, 0.2);
        margin: 0 auto;
    }
    
    .calendar-mood-icon i {
        font-size: 14px;
        color: #d1789c;
    }
    
    .summary-mood-icon {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: linear-gradient(135deg, #feeef5, #ffffff);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 5px 10px rgba(209, 120, 156, 0.15);
        margin-right: 10px;
    }
    
    .summary-mood-icon i {
        font-size: 22px;
        color: #d1789c;
        filter: drop-shadow(0 1px 2px rgba(0,0,0,0.1));
    }
    
    /* Responsive styles for mood icons */
    @media (max-width: 576px) {
        .calendar-mood-icon {
            width: 24px;
            height: 24px;
        }
        
        .calendar-mood-icon i {
            font-size: 12px;
        }
        
        .summary-mood-icon {
            width: 35px;
            height: 35px;
        }
        
        .summary-mood-icon i {
            font-size: 18px;
        }
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