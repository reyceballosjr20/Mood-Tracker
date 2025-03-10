<?php
// Initialize session if needed
session_start();

// Check if user is logged in
if(!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    echo "Authorization required";
    exit;
}

// Get current month and year
$month = date('n');
$year = date('Y');
$daysInMonth = date('t', mktime(0, 0, 0, $month, 1, $year));
$firstDayOfMonth = date('N', mktime(0, 0, 0, $month, 1, $year));
?>

<div class="header">
    <h1 class="page-title">Mood Calendar</h1>
    <div class="search-box">
        <i class="fas fa-calendar"></i>
        <input type="month" value="<?php echo date('Y-m'); ?>" style="cursor: pointer;">
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden; margin-bottom: 30px;">
    <div style="padding: 20px; background-color: #f8dfeb; text-align: center;">
        <h2 style="margin: 0; color: #4a3347;"><?php echo date('F Y'); ?></h2>
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
                echo '<div style="height: 70px;"></div>';
            }
            
            // Calendar days
            for ($day = 1; $day <= $daysInMonth; $day++) {
                // Random mood for demo
                $moods = ['😊', '😐', '😢', '😡', '😴', '🥳', '😎'];
                $mood = $moods[array_rand($moods)];
                
                // Today has special highlighting
                $isToday = ($day == date('j') && $month == date('n') && $year == date('Y'));
                $bgColor = $isToday ? '#ffebf3' : 'white';
                $borderColor = $isToday ? '#ff8fb1' : '#e0e0e0';
                
                echo '<div style="height: 70px; border: 1px solid ' . $borderColor . '; border-radius: 10px; background-color: ' . $bgColor . '; display: flex; flex-direction: column; justify-content: space-between; padding: 5px;">
                    <div style="text-align: right; font-size: 12px;">' . $day . '</div>
                    <div style="font-size: 24px; display: flex; align-items: center; justify-content: center; height: 40px;">' . $mood . '</div>
                </div>';
            }
            ?>
        </div>
    </div>
</div>

<div class="mood-chart">
    <div class="section-header">
        <h2 class="section-title">Month Summary</h2>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <h2 class="card-title">Happiest Day</h2>
                <div class="card-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
            <div class="card-content">April 15</div>
            <div class="card-footer">Your highest mood score</div>
        </div>
        
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <h2 class="card-title">Mood Trends</h2>
                <div class="card-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
            <div class="card-content">Improving</div>
            <div class="card-footer">Up 15% from last month</div>
        </div>
        
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <h2 class="card-title">Logging Streak</h2>
                <div class="card-icon">
                    <i class="fas fa-fire"></i>
                </div>
            </div>
            <div class="card-content">7 Days</div>
            <div class="card-footer">Your current streak</div>
        </div>
    </div>
</div> 