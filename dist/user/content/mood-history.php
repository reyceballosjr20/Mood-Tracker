<?php
// Initialize session if needed
session_start();

// Check if user is logged in
if(!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    echo "Authorization required";
    exit;
}

// Set timezone to UTC+8 (Singapore/Philippines/Malaysia/HK etc.)
date_default_timezone_set('Asia/Singapore');

// Get user ID
$userId = $_SESSION['user_id'] ?? 0;

// Load the Mood model
require_once '../../../models/Mood.php';
$mood = new Mood();

// Get filter parameters with defaults
$period = isset($_GET['period']) ? $_GET['period'] : 'month';
$validPeriods = ['week', 'month', 'quarter', 'year', 'all'];
if (!in_array($period, $validPeriods)) {
    $period = 'month';
}

// Handle search query
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get mood history data based on filters
$moodHistory = $mood->getMoodHistory($userId, $period, $searchQuery);

// Get mood stats for the selected period
$moodStats = $mood->getMoodStats($userId, $period);

// Calculate date range for display
$dateRangeLabel = '';
switch ($period) {
    case 'week':
        $dateRangeLabel = 'Last 7 days';
        break;
    case 'month':
        $dateRangeLabel = 'Last 30 days';
        break;
    case 'quarter':
        $dateRangeLabel = 'Last 3 months';
        break;
    case 'year':
        $dateRangeLabel = 'Last 12 months';
        break;
    case 'all':
        $dateRangeLabel = 'All time';
        break;
}

// Define mood-to-emoji mapping (consistent with other pages)
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

// Extract data for chart
$chartLabels = [];
$chartData = [];
$moodScores = [
    'sad' => 1,
    'unhappy' => 2,
    'anxious' => 2.5,
    'tired' => 3,
    'neutral' => 3.5,
    'focused' => 4,
    'good' => 4.5,
    'energetic' => 5,
    'excellent' => 5.5
];

if ($moodHistory) {
    // For chart data, we'll group by day and calculate average mood score
    $dayData = [];
    
    foreach ($moodHistory as $entry) {
        // Convert database UTC timestamp to UTC+8
        $timestamp = strtotime($entry['created_at']) + (8 * 3600); // Add 8 hours
        $date = date('Y-m-d', $timestamp);
        
        if (!isset($dayData[$date])) {
            $dayData[$date] = [
                'total' => 0,
                'count' => 0
            ];
        }
        
        if (isset($moodScores[$entry['mood_type']])) {
            $dayData[$date]['total'] += $moodScores[$entry['mood_type']];
            $dayData[$date]['count']++;
        }
    }
    
    // Calculate averages and prepare chart data
    foreach ($dayData as $date => $data) {
        $chartLabels[] = date('M j', strtotime($date));
        $chartData[] = $data['count'] > 0 ? round($data['total'] / $data['count'], 1) : 0;
    }
}

// Get monthly summary for dropdown
$yearMonths = $mood->getAvailableMonths($userId);
?>

<div class="header">
    <h1 class="page-title" style="color: #d1789c; font-size: 1.8rem; margin-bottom: 15px; position: relative; display: inline-block; font-weight: 600;">
        Mood History
        <span style="position: absolute; bottom: -8px; left: 0; width: 40%; height: 3px; background: linear-gradient(90deg, #d1789c, #f5d7e3); border-radius: 3px;"></span>
    </h1>
    <div class="search-box">
        <form method="get" action="dashboard.php" style="display: flex; align-items: center;">
            <input type="hidden" name="page" value="mood-history">
            <input type="hidden" name="period" value="<?php echo htmlspecialchars($period); ?>">
            <i class="fas fa-search" style="position: absolute; left: 10px; color: #999;"></i>
            <input type="text" name="search" placeholder="Search moods..." value="<?php echo htmlspecialchars($searchQuery); ?>" 
                   style="padding-left: 30px; width: 100%;">
            <button type="submit" style="background: none; border: none; cursor: pointer; margin-left: -30px; color: #d1789c;">
                <i class="fas fa-arrow-right"></i>
            </button>
        </form>
    </div>
</div>

<!-- Period Filter Tabs -->
<div class="filter-tabs" style="display: flex; margin-bottom: 25px; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
    <a href="dashboard.php?page=mood-history&period=week<?php echo $searchQuery ? '&search='.urlencode($searchQuery) : ''; ?>" 
       class="tab <?php echo $period == 'week' ? 'active' : ''; ?>" 
       style="flex: 1; text-align: center; padding: 12px 5px; text-decoration: none; color: <?php echo $period == 'week' ? '#d1789c' : '#666'; ?>; font-weight: <?php echo $period == 'week' ? '600' : '400'; ?>; position: relative;">
        Week
        <?php if ($period == 'week'): ?>
        <span style="position: absolute; bottom: 0; left: 0; width: 100%; height: 3px; background: #d1789c;"></span>
        <?php endif; ?>
    </a>
    <a href="dashboard.php?page=mood-history&period=month<?php echo $searchQuery ? '&search='.urlencode($searchQuery) : ''; ?>" 
       class="tab <?php echo $period == 'month' ? 'active' : ''; ?>" 
       style="flex: 1; text-align: center; padding: 12px 5px; text-decoration: none; color: <?php echo $period == 'month' ? '#d1789c' : '#666'; ?>; font-weight: <?php echo $period == 'month' ? '600' : '400'; ?>; position: relative;">
        Month
        <?php if ($period == 'month'): ?>
        <span style="position: absolute; bottom: 0; left: 0; width: 100%; height: 3px; background: #d1789c;"></span>
        <?php endif; ?>
    </a>
    <a href="dashboard.php?page=mood-history&period=quarter<?php echo $searchQuery ? '&search='.urlencode($searchQuery) : ''; ?>" 
       class="tab <?php echo $period == 'quarter' ? 'active' : ''; ?>" 
       style="flex: 1; text-align: center; padding: 12px 5px; text-decoration: none; color: <?php echo $period == 'quarter' ? '#d1789c' : '#666'; ?>; font-weight: <?php echo $period == 'quarter' ? '600' : '400'; ?>; position: relative;">
        3 Months
        <?php if ($period == 'quarter'): ?>
        <span style="position: absolute; bottom: 0; left: 0; width: 100%; height: 3px; background: #d1789c;"></span>
        <?php endif; ?>
    </a>
    <a href="dashboard.php?page=mood-history&period=year<?php echo $searchQuery ? '&search='.urlencode($searchQuery) : ''; ?>" 
       class="tab <?php echo $period == 'year' ? 'active' : ''; ?>" 
       style="flex: 1; text-align: center; padding: 12px 5px; text-decoration: none; color: <?php echo $period == 'year' ? '#d1789c' : '#666'; ?>; font-weight: <?php echo $period == 'year' ? '600' : '400'; ?>; position: relative;">
        Year
        <?php if ($period == 'year'): ?>
        <span style="position: absolute; bottom: 0; left: 0; width: 100%; height: 3px; background: #d1789c;"></span>
        <?php endif; ?>
    </a>
    <a href="dashboard.php?page=mood-history&period=all<?php echo $searchQuery ? '&search='.urlencode($searchQuery) : ''; ?>" 
       class="tab <?php echo $period == 'all' ? 'active' : ''; ?>" 
       style="flex: 1; text-align: center; padding: 12px 5px; text-decoration: none; color: <?php echo $period == 'all' ? '#d1789c' : '#666'; ?>; font-weight: <?php echo $period == 'all' ? '600' : '400'; ?>; position: relative;">
        All Time
        <?php if ($period == 'all'): ?>
        <span style="position: absolute; bottom: 0; left: 0; width: 100%; height: 3px; background: #d1789c;"></span>
        <?php endif; ?>
    </a>
</div>

<div class="mood-chart" style="margin-bottom: 30px;">
    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 class="section-title" style="color: #d1789c; margin: 0; font-size: 1.4rem; font-weight: 500; position: relative; display: inline-block;">
            Mood Trends: <?php echo $dateRangeLabel; ?>
            <span style="position: absolute; bottom: -5px; left: 0; width: 50%; height: 2px; background: linear-gradient(90deg, #d1789c, transparent); border-radius: 2px;"></span>
        </h2>
        
        <?php if (!empty($yearMonths)): ?>
        <div>
            <select id="monthSelector" style="padding: 8px 15px; border-radius: 8px; border: 1px solid #eee; background-color: white; color: #555; font-size: 0.9rem; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.05);" onchange="window.location.href='dashboard.php?page=calendar&month='+this.value.split('-')[1]+'&year='+this.value.split('-')[0]">
                <option value="">Jump to Month...</option>
                <?php foreach ($yearMonths as $ym): ?>
                    <?php 
                    $ymParts = explode('-', $ym);
                    $monthName = date('F', mktime(0, 0, 0, $ymParts[1], 1, $ymParts[0]));
                    ?>
                    <option value="<?php echo $ym; ?>"><?php echo $monthName . ' ' . $ymParts[0]; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($chartData)): ?>
    <div class="chart-container" style="height: 300px; background-color: white; border-radius: 16px; padding: 20px; box-shadow: 0 8px 25px rgba(0,0,0,0.07); position: relative; overflow: hidden;">
        <!-- Simple Canvas-based Line Chart -->
        <canvas id="moodChart" width="800" height="250" style="width: 100%; height: 250px;"></canvas>
    </div>
    <?php else: ?>
    <div class="chart-container" style="height: 250px; background-color: white; border-radius: 16px; padding: 20px; box-shadow: 0 8px 25px rgba(0,0,0,0.07); display: flex; align-items: center; justify-content: center; flex-direction: column; color: #888;">
        <i class="fas fa-chart-line" style="font-size: 3rem; margin-bottom: 15px; color: #f5d7e3;"></i>
        <p>No mood data available for the selected period.</p>
    </div>
    <?php endif; ?>
</div>

<div class="recent-activities">
    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 class="section-title" style="color: #d1789c; margin: 0; font-size: 1.4rem; font-weight: 500; position: relative; display: inline-block;">
            Mood Log
            <span style="position: absolute; bottom: -5px; left: 0; width: 50%; height: 2px; background: linear-gradient(90deg, #d1789c, transparent); border-radius: 2px;"></span>
        </h2>
        <?php if ($searchQuery): ?>
        <a href="dashboard.php?page=mood-history&period=<?php echo $period; ?>" class="clear-filter" style="color: #d1789c; text-decoration: none; font-size: 0.9rem; display: flex; align-items: center;">
            <i class="fas fa-times-circle" style="margin-right: 5px;"></i> Clear search
        </a>
        <?php endif; ?>
    </div>
    
    <?php if (empty($moodHistory)): ?>
    <div style="text-align: center; padding: 40px 20px; background-color: white; border-radius: 16px; box-shadow: 0 8px 25px rgba(0,0,0,0.07);">
        <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 15px; color: #f5d7e3;"></i>
        <h3 style="color: #6e3b5c; margin-bottom: 10px; font-size: 1.2rem; font-weight: 500;">No mood entries found</h3>
        <p style="color: #888; margin-bottom: 20px;">
            <?php if ($searchQuery): ?>
                No results match your search "<?php echo htmlspecialchars($searchQuery); ?>".
            <?php else: ?>
                You haven't logged any moods for this time period yet.
            <?php endif; ?>
        </p>
        <a href="dashboard.php?page=log-mood" style="background: linear-gradient(135deg, #d1789c, #e896b8); color: white; text-decoration: none; padding: 10px 20px; border-radius: 25px; display: inline-block; font-weight: 500;">
            Log Your Mood
        </a>
    </div>
    <?php else: ?>
    <ul class="activity-list" style="list-style: none; padding: 0; margin: 0; background-color: white; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 25px rgba(0,0,0,0.07);">
        <?php foreach ($moodHistory as $entry): ?>
        <li class="activity-item" style="padding: 15px 20px; display: flex; align-items: center; border-bottom: 1px solid #f0f0f0;">
            <div class="activity-icon" style="width: 45px; height: 45px; border-radius: 50%; background-color: #fff3f8; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-size: 1.5rem;">
                <?php echo isset($moodEmojis[$entry['mood_type']]) ? $moodEmojis[$entry['mood_type']] : '❓'; ?>
            </div>
            <div class="activity-info" style="flex: 1;">
                <div class="activity-title" style="font-weight: 500; color: #4a3347; text-transform: capitalize; margin-bottom: 3px;">
                    <?php echo htmlspecialchars($entry['mood_type']); ?>
                    <?php if (!empty($entry['mood_text'])): ?>
                    - "<?php echo htmlspecialchars($entry['mood_text']); ?>"
                    <?php endif; ?>
                </div>
                <div class="activity-time" style="font-size: 0.85rem; color: #888;">
                    <?php 
                    // Convert database timestamp to UTC+8
                    $entryDate = strtotime($entry['created_at']) + (8 * 3600);
                    $now = time();  // Current time in the set timezone (UTC+8)
                    $diff = $now - $entryDate;
                    
                    // Improved time formatting
                    if ($diff < 60) { // Less than a minute
                        echo 'Just now';
                    } elseif ($diff < 3600) { // Less than an hour
                        $minutes = floor($diff / 60);
                        echo $minutes . ' minute' . ($minutes != 1 ? 's' : '') . ' ago';
                    } elseif ($diff < 86400) { // Less than 24 hours
                        $hours = floor($diff / 3600);
                        echo $hours . ' hour' . ($hours != 1 ? 's' : '') . ' ago';
                    } elseif ($diff < 172800) { // Less than 48 hours
                        echo 'Yesterday at ' . date('g:i A', $entryDate);
                    } elseif ($diff < 604800) { // Less than a week
                        $days = floor($diff / 86400);
                        echo $days . ' day' . ($days != 1 ? 's' : '') . ' ago';
                    } elseif ($diff < 2592000) { // Less than a month
                        $weeks = floor($diff / 604800);
                        echo $weeks . ' week' . ($weeks != 1 ? 's' : '') . ' ago';
                    } else {
                        echo date('M j, Y \a\t g:i A', $entryDate);
                    }
                    ?>
                </div>
            </div>
            <button class="activity-action" style="background: none; border: none; color: #999; cursor: pointer; padding: 5px;">
                <i class="fas fa-ellipsis-v"></i>
            </button>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</div>

<?php if (!empty($chartData)): ?>
<!-- Chart.js for visualization -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('moodChart').getContext('2d');
    
    // Chart data
    var labels = <?php echo json_encode($chartLabels); ?>;
    var data = <?php echo json_encode($chartData); ?>;
    
    // Create gradient
    var gradient = ctx.createLinearGradient(0, 0, 0, 250);
    gradient.addColorStop(0, 'rgba(209, 120, 156, 0.5)');
    gradient.addColorStop(1, 'rgba(209, 120, 156, 0.0)');
    
    // Draw line
    ctx.strokeStyle = '#d1789c';
    ctx.lineWidth = 3;
    ctx.beginPath();
    
    // Calculate x and y step
    var xStep = ctx.canvas.width / (labels.length - 1);
    var yRange = 6 - 0; // Max - Min expected mood score
    var yStep = ctx.canvas.height / yRange;
    
    // Draw line chart manually
    for (var i = 0; i < data.length; i++) {
        // Normalize data point to canvas coordinates
        var x = i * xStep;
        var y = ctx.canvas.height - ((data[i] - 0) * yStep);
        
        if (i === 0) {
            ctx.moveTo(x, y);
        } else {
            ctx.lineTo(x, y);
        }
        
        // Draw point
        ctx.fillStyle = '#d1789c';
        ctx.beginPath();
        ctx.arc(x, y, 5, 0, Math.PI * 2);
        ctx.fill();
        
        // Draw value
        ctx.fillStyle = '#4a3347';
        ctx.font = '12px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(data[i], x, y - 15);
    }
    
    ctx.stroke();
    
    // Fill area under line
    var lastX = (data.length - 1) * xStep;
    var lastY = ctx.canvas.height - ((data[data.length - 1] - 0) * yStep);
    ctx.lineTo(lastX, ctx.canvas.height);
    ctx.lineTo(0, ctx.canvas.height);
    ctx.fillStyle = gradient;
    ctx.fill();
    
    // Draw x-axis labels
    ctx.fillStyle = '#888';
    ctx.font = '12px Arial';
    ctx.textAlign = 'center';
    for (var i = 0; i < labels.length; i++) {
        var x = i * xStep;
        ctx.fillText(labels[i], x, ctx.canvas.height - 5);
    }
});
</script>
<?php endif; ?>

<style>
/* Mobile responsiveness */
@media (max-width: 768px) {
    .filter-tabs {
        flex-wrap: wrap;
    }
    
    .filter-tabs .tab {
        flex: 1 1 33.33%;
        font-size: 0.9rem;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start !important;
    }
    
    .section-header > div {
        margin-top: 15px;
        width: 100%;
    }
    
    select {
        width: 100%;
    }
    
    .activity-title {
        font-size: 0.95rem;
    }
}

@media (max-width: 576px) {
    .filter-tabs .tab {
        flex: 1 1 50%;
        padding: 10px 5px !important;
    }
    
    .activity-icon {
        width: 40px !important;
        height: 40px !important;
        font-size: 1.2rem !important;
    }
    
    .activity-item {
        padding: 12px 15px !important;
    }
    
    h1.page-title {
        font-size: 1.5rem !important;
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
</style> 