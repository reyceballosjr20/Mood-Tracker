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

// Define mood-to-emoji mapping (used across all months)
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

<div class="header">
    <h1 class="page-title" style="color: #d1789c; font-size: 1.8rem; margin-bottom: 15px; position: relative; display: inline-block; font-weight: 600;">
        Mood Calendar
        <span style="position: absolute; bottom: -8px; left: 0; width: 40%; height: 3px; background: linear-gradient(90deg, #d1789c, #f5d7e3); border-radius: 3px;"></span>
    </h1>
    <div class="search-box">
        <i class="fas fa-calendar"></i>
        <select id="monthYearSelect" style="cursor: pointer; padding-left: 30px;">
            <?php
            // Get available months
            $availableMonths = $mood->getAvailableMonths($userId);
            
            // Add current and surrounding months
            $currentMonthYear = date('Y-m');
            $showingMonths = [];
            
            // Add 12 months before and after current month
            for ($i = -12; $i <= 12; $i++) {
                $timeStamp = strtotime("$i months");
                $formattedDate = date('Y-m', $timeStamp);
                $showingMonths[$formattedDate] = date('F Y', $timeStamp);
            }
            
            // Add any available months from database
            foreach ($availableMonths as $monthStr) {
                $timestamp = strtotime($monthStr . '-01');
                $showingMonths[$monthStr] = date('F Y', $timestamp);
            }
            
            // Sort years and months
            ksort($showingMonths);
            
            // Current selection
            $selectedMonthYear = $selectedYear . '-' . str_pad($selectedMonth, 2, '0', STR_PAD_LEFT);
            
            // Output options
            foreach ($showingMonths as $value => $label) {
                echo '<option value="' . $value . '"' . 
                     ($value == $selectedMonthYear ? ' selected' : '') . 
                     '>' . $label . '</option>';
            }
            ?>
        </select>
    </div>
</div>

<div class="card" style="padding: 20px; background-color: #f8dfeb; margin-bottom: 20px; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border-radius: 16px; text-align: center; display: flex; justify-content: space-between; align-items: center;">
    <a href="#" id="prevMonthBtn" data-month="<?php echo $prevMonth; ?>" data-year="<?php echo $prevYear; ?>" 
       style="color: #6e3b5c; text-decoration: none; display: flex; align-items: center; padding: 8px 15px; border-radius: 20px; transition: background-color 0.3s ease;">
        <i class="fas fa-chevron-left" style="margin-right: 5px;"></i>
        <?php echo date('M', mktime(0, 0, 0, $prevMonth, 1, $prevYear)); ?>
    </a>
    
    <h2 style="margin: 0; color: #4a3347; font-size: 1.4rem;">
        <?php 
        // Show range of months being displayed
        $startMonth = date('M', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear));
        $endMonth = date('M', mktime(0, 0, 0, $nextMonth3, 1, $nextYear3));
        $startYear = $selectedYear;
        $endYear = $nextYear3;
        
        if ($startYear == $endYear) {
            echo "$startMonth - $endMonth $startYear";
        } else {
            echo "$startMonth $startYear - $endMonth $endYear";
        }
        ?>
    </h2>
    
    <a href="#" id="nextMonthBtn" data-month="<?php echo $nextMonth3; ?>" data-year="<?php echo $nextYear3; ?>" 
       style="color: #6e3b5c; text-decoration: none; display: flex; align-items: center; padding: 8px 15px; border-radius: 20px; transition: background-color 0.3s ease;">
        <?php echo date('M', mktime(0, 0, 0, $nextMonth3, 1, $nextYear3)); ?>
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
        <div class="card month-calendar" style="padding: 20px; background-color: white; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border-radius: 16px; overflow: hidden;">
            <!-- Month header with distinct styling -->
            <div style="text-align: center; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f5d7e3;">
                <h3 style="color: #d1789c; font-size: 1.2rem; margin: 0; font-weight: 500;">
                    <?php echo $monthName . ' ' . $year; ?>
                </h3>
            </div>
            
            <!-- Day headers -->
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; margin-bottom: 10px; font-weight: 500; color: #6e3b5c;">
                <div>M</div>
                <div>T</div>
                <div>W</div>
                <div>T</div>
                <div>F</div>
                <div>S</div>
                <div>S</div>
            </div>
            
            <!-- Calendar days -->
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px;">
                <?php 
                // Add empty cells for days before the 1st of the month
                for ($i = 1; $i < $firstDayOfMonth; $i++) {
                    echo '<div style="height: 40px;"></div>';
                }
                
                // Add days of the month
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $isToday = ($day == date('j') && $month == date('n') && $year == date('Y'));
                    $hasMood = isset($moodsByDay[$day]);
                    
                    $dayStyle = 'height: 40px; border-radius: 10px; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative;';
                    
                    if ($isToday) {
                        $dayStyle .= 'border: 2px solid #d1789c; background-color: #fff3f8;';
                    } else {
                        $dayStyle .= 'border: 1px solid #eee; background-color: white;';
                    }
                    
                    echo '<div style="' . $dayStyle . '">';
                    echo '<div style="font-size: 0.8rem;">' . $day . '</div>';
                    
                    if ($hasMood) {
                        $moodType = $moodsByDay[$day]['mood_type'];
                        $moodEmoji = isset($moodEmojis[$moodType]) ? $moodEmojis[$moodType] : '😐';
                        $moodText = $moodsByDay[$day]['mood_text'];
                        
                        echo '<div class="mood-entry" style="font-size: 1rem; cursor: pointer; margin-top: -2px;">';
                        echo $moodEmoji;
                        
                        // Add tooltip with mood details
                        if (!empty($moodText)) {
                            echo '<div class="mood-tooltip">';
                            echo '<div style="font-weight: 500; margin-bottom: 5px; color: #4a3347; text-transform: capitalize;">' . $moodType . '</div>';
                            echo '<div style="font-size: 0.85rem; color: #666;">"' . htmlspecialchars($moodText) . '"</div>';
                            echo '</div>';
                        }
                        
                        echo '</div>';
                    }
                    
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin: 30px 0 20px 0;">
    <h2 style="color: #d1789c; font-size: 1.4rem; margin: 0; font-weight: 500; position: relative; display: inline-block;">
        <?php echo $monthName . ' ' . $displayYear; ?> Summary
        <span style="position: absolute; bottom: -5px; left: 0; width: 40%; height: 2px; background: linear-gradient(90deg, #d1789c, transparent); border-radius: 2px;"></span>
    </h2>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
    <?php if ($totalEntries > 0): ?>
        <!-- Card 1: Dominant Mood - Only show if we have mood data -->
        <?php if ($topMood): ?>
        <div class="card" style="margin-bottom: 0; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border-radius: 16px; padding: 25px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2 style="font-size: 1.1rem; color: #6e3b5c; margin: 0; font-weight: 500;">Dominant Mood</h2>
                <div style="color: #d1789c; background-color: #fff3f8; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                    <i class="fas fa-chart-pie"></i>
                </div>
            </div>
            <div style="font-size: 2rem; margin: 15px 0;">
                <?php 
                echo isset($moodEmojis[$topMood]) ? $moodEmojis[$topMood] : '😐';
                ?>
            </div>
            <div style="font-size: 1.2rem; color: #4a3347; text-transform: capitalize;">
                <?php echo $topMood; ?>
            </div>
            <div style="font-size: 0.9rem; color: #888; margin-top: 5px;">
                <?php echo $topMoodCount; ?> out of <?php echo $totalEntries; ?> entries (<?php echo round(($topMoodCount / $totalEntries) * 100); ?>%)
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Card 2: Logging Streak - Only show if user has a streak -->
        <?php if ($streak > 0): ?>
        <div class="card" style="margin-bottom: 0; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border-radius: 16px; padding: 25px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2 style="font-size: 1.1rem; color: #6e3b5c; margin: 0; font-weight: 500;">Logging Streak</h2>
                <div style="color: #d1789c; background-color: #fff3f8; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                    <i class="fas fa-fire"></i>
                </div>
            </div>
            <div style="font-size: 2rem; margin: 15px 0; color: #4a3347;">
                <?php echo $streak; ?> <?php echo $streak == 1 ? 'day' : 'days'; ?>
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
        <?php endif; ?>
        
        <!-- Card 3: Mood Trend - Only show if we have comparison data -->
        <?php if ($currentMonthAvg > 0 && $prevMonthAvg > 0 && abs($trendPercentage) > 0): ?>
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
        
        <!-- Card 4: Best Day - Only show if we have a best day -->
        <?php if (isset($bestDay) && $bestDay): ?>
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
        
        <!-- Card 5: Weekly Pattern - Only show if we have a best day of week -->
        <?php if (!empty($bestDayOfWeek)): ?>
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
        
        <!-- If none of the specific cards have data, show a generic info card -->
        <?php if (!$topMood && $streak == 0 && !$bestDay && empty($bestDayOfWeek) && !($currentMonthAvg > 0 && $prevMonthAvg > 0)): ?>
        <div class="card" style="margin-bottom: 0; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border-radius: 16px; padding: 25px; grid-column: 1 / -1;">
            <div style="text-align: center; padding: 20px;">
                <div style="font-size: 2.5rem; margin-bottom: 15px; color: #f5d7e3;">
                    <i class="fas fa-info-circle"></i>
                </div>
                <h3 style="font-size: 1.2rem; color: #6e3b5c; margin-bottom: 10px;">Limited Data Available</h3>
                <p style="color: #888; margin-bottom: 10px;">
                    Continue logging your moods to unlock more insights for <?php echo $monthName; ?>.
                </p>
                <p style="color: #888; font-size: 0.9rem;">
                    More entries = better insights!
                </p>
            </div>
        </div>
        <?php endif; ?>
    <?php else: ?>
        <!-- No entries message - When there are no entries for the month -->
        <div class="card" style="margin-bottom: 0; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border-radius: 16px; padding: 25px; grid-column: 1 / -1;">
            <div style="text-align: center; padding: 30px 20px;">
                <div style="font-size: 3rem; margin-bottom: 15px; color: #f5d7e3;">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <h3 style="font-size: 1.2rem; color: #6e3b5c; margin-bottom: 10px;">No mood entries for <?php echo $monthName; ?> <?php echo $selectedYear; ?></h3>
                <p style="color: #888; margin-bottom: 20px;">Start logging your moods to see insights and patterns.</p>
                <a href="dashboard.php?page=log-mood" class="btn" style="background: linear-gradient(135deg, #d1789c, #e896b8); color: white; text-decoration: none; padding: 10px 20px; border-radius: 25px; display: inline-block; font-weight: 500;">
                    Log Your Mood
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Calendar grid responsive styles */
    .month-calendar {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
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
            gap: 3px !important;
        }
        
        div[style*="height: 40px"] {
            height: 35px !important;
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
</style>

<!-- Load the calendar script -->
<script src="../js/calendar.js"></script> 