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

// Get today's mood
$todaysMood = $mood->getTodaysMood($userId);
$currentMood = $todaysMood ? $todaysMood['mood_type'] : 'none';

// Define mood-specific recommendations with matching icons
$moodRecommendations = [
    'happy' => [
        [
            'title' => 'Share Your Joy',
            'icon' => 'fa-smile-beam',
            'description' => 'Spread your happiness by connecting with others or doing something kind.',
        ],
        [
            'title' => 'Creative Expression',
            'icon' => 'fa-paint-brush',
            'description' => 'Channel your positive energy into a creative project or hobby.',
        ],
        [
            'title' => 'Gratitude Practice',
            'icon' => 'fa-heart',
            'description' => 'Write down what made you happy today to remember and revisit later.',
        ]
    ],
    'sad' => [
        [
            'title' => 'Gentle Movement',
            'icon' => 'fa-sad-tear',
            'description' => 'A short walk outside can help lift your mood and provide a change of scenery.',
        ],
        [
            'title' => 'Connect with Someone',
            'icon' => 'fa-phone',
            'description' => 'Reaching out to a friend or family member can provide comfort and perspective.',
        ],
        [
            'title' => 'Self-Compassion Practice',
            'icon' => 'fa-heart',
            'description' => 'Be kind to yourself today. Try a guided self-compassion meditation.',
        ]
    ],
    'angry' => [
        [
            'title' => 'Release Exercise',
            'icon' => 'fa-angry',
            'description' => 'Channel your energy into physical activity to release tension.',
        ],
        [
            'title' => 'Breathing Technique',
            'icon' => 'fa-wind',
            'description' => 'Try deep breathing exercises to help calm your nervous system.',
        ],
        [
            'title' => 'Express Yourself',
            'icon' => 'fa-pencil-alt',
            'description' => 'Write down your thoughts or feelings to process your anger constructively.',
        ]
    ],
    'anxious' => [
        [
            'title' => 'Grounding Exercise',
            'icon' => 'fa-bolt',
            'description' => 'Try the 5-4-3-2-1 technique to anchor yourself in the present moment.',
        ],
        [
            'title' => 'Calming Breath',
            'icon' => 'fa-wind',
            'description' => 'Practice the 4-7-8 breathing technique to reduce anxiety.',
        ],
        [
            'title' => 'Worry List',
            'icon' => 'fa-list-ul',
            'description' => 'Write down your worries and categorize them into "can control" and "cannot control".',
        ]
    ],
    'stressed' => [
        [
            'title' => 'Quick Meditation',
            'icon' => 'fa-head-side-virus',
            'description' => 'Take a 5-minute break for a guided stress-relief meditation.',
        ],
        [
            'title' => 'Priority Reset',
            'icon' => 'fa-tasks',
            'description' => 'List and organize your tasks to make them more manageable.',
        ],
        [
            'title' => 'Tension Release',
            'icon' => 'fa-cloud-sun',
            'description' => 'Try progressive muscle relaxation to release physical tension.',
        ]
    ],
    'calm' => [
        [
            'title' => 'Mindful Moment',
            'icon' => 'fa-peace',
            'description' => 'Use this peaceful state for mindful reflection or meditation.',
        ],
        [
            'title' => 'Creative Flow',
            'icon' => 'fa-paint-brush',
            'description' => 'Engage in a creative activity while in this balanced state.',
        ],
        [
            'title' => 'Goal Setting',
            'icon' => 'fa-bullseye',
            'description' => 'Use this clear mindset to plan and set meaningful goals.',
        ]
    ],
    'tired' => [
        [
            'title' => 'Rest Timer',
            'icon' => 'fa-bed',
            'description' => 'Set a timer for a short power nap or relaxation break.',
        ],
        [
            'title' => 'Gentle Stretching',
            'icon' => 'fa-child',
            'description' => 'Easy stretching exercises to boost energy without exhaustion.',
        ],
        [
            'title' => 'Energy Check',
            'icon' => 'fa-battery-half',
            'description' => 'Review your energy levels and identify what might help restore them.',
        ]
    ],
    'energetic' => [
        [
            'title' => 'Channel Energy',
            'icon' => 'fa-bolt',
            'description' => 'Direct your energy into a productive or creative project.',
        ],
        [
            'title' => 'Active Goals',
            'icon' => 'fa-trophy',
            'description' => 'Work on challenging tasks that require high energy.',
        ],
        [
            'title' => 'Social Connection',
            'icon' => 'fa-users',
            'description' => 'Share your energy with others through social activities.',
        ]
    ],
    'neutral' => [
        [
            'title' => 'Mood Exploration',
            'icon' => 'fa-meh',
            'description' => 'Use this balanced state to explore what might enhance your day.',
        ],
        [
            'title' => 'Mindful Check-in',
            'icon' => 'fa-bell',
            'description' => 'Take a moment to check in with your thoughts and feelings.',
        ],
        [
            'title' => 'New Experience',
            'icon' => 'fa-star',
            'description' => 'Try something new while in this receptive state.',
        ]
    ],
    'excited' => [
        [
            'title' => 'Creative Project',
            'icon' => 'fa-grin-stars',
            'description' => 'Channel your excitement into something creative or innovative.',
        ],
        [
            'title' => 'Share Joy',
            'icon' => 'fa-share-alt',
            'description' => 'Connect with others and spread your positive energy.',
        ],
        [
            'title' => 'Future Planning',
            'icon' => 'fa-calendar-alt',
            'description' => 'Use this enthusiastic energy to plan future activities.',
        ]
    ],
    'frustrated' => [
        [
            'title' => 'Problem Solving',
            'icon' => 'fa-angry',
            'description' => 'Break down what\'s frustrating you into smaller, manageable parts.',
        ],
        [
            'title' => 'Physical Release',
            'icon' => 'fa-dumbbell',
            'description' => 'Channel frustration into physical activity for release.',
        ],
        [
            'title' => 'Perspective Shift',
            'icon' => 'fa-sync',
            'description' => 'Try looking at the situation from a different angle.',
        ]
    ],
    'grateful' => [
        [
            'title' => 'Gratitude Journal',
            'icon' => 'fa-heart',
            'description' => 'Write down specific things you\'re thankful for today.',
        ],
        [
            'title' => 'Express Thanks',
            'icon' => 'fa-envelope',
            'description' => 'Share your appreciation with someone who made a difference.',
        ],
        [
            'title' => 'Pay It Forward',
            'icon' => 'fa-hand-holding-heart',
            'description' => 'Channel your gratitude into an act of kindness for others.',
        ]
    ],
    'none' => [
        [
            'title' => 'Mood Check-in',
            'icon' => 'fa-clipboard-check',
            'description' => 'Take a moment to reflect on how you\'re feeling right now.',
        ],
        [
            'title' => 'Mindful Moment',
            'icon' => 'fa-leaf',
            'description' => 'Try a quick mindfulness exercise to connect with your emotions.',
        ],
        [
            'title' => 'Activity Boost',
            'icon' => 'fa-walking',
            'description' => 'Choose a mood-lifting activity to enhance your day.',
        ]
    ]
];

// Get recommendations for current mood
$recommendations = $moodRecommendations[$currentMood] ?? $moodRecommendations['none'];

// Define mood-specific resources with YouTube links
$moodResources = [
    'sad' => [
        [
            'icon' => 'video',
            'title' => 'Video: "How to cope with depression"',
            'time' => '7 min watch',
            'link' => 'https://www.youtube.com/watch?v=2UiX4dUUjWc'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Self-compassion during difficult times"',
            'time' => '10 min watch',
            'link' => 'https://www.youtube.com/watch?v=IvtZBUSplr4'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "The habits of happiness"',
            'time' => '15 min watch',
            'link' => 'https://www.youtube.com/watch?v=fLJsdqxnZb0'
        ]
    ],
    'unhappy' => [
        [
            'icon' => 'video',
            'title' => 'Video: "How to change your mindset"',
            'time' => '15 min watch',
            'link' => 'https://www.youtube.com/watch?v=zVTL_w3pV0c'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Quick ways to improve mood"',
            'time' => '8 min watch',
            'link' => 'https://www.youtube.com/watch?v=eC7xzavzEKY'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Reframing negative thoughts"',
            'time' => '12 min watch',
            'link' => 'https://www.youtube.com/watch?v=3VDsFztk4z0'
        ]
    ],
    'neutral' => [
        [
            'icon' => 'video',
            'title' => 'Video: "Finding meaning in everyday life"',
            'time' => '18 min watch',
            'link' => 'https://www.youtube.com/watch?v=skZagPiKQfQ'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Mindfulness for beginners"',
            'time' => '10 min watch',
            'link' => 'https://www.youtube.com/watch?v=6p_yaNFSYao'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "How to motivate yourself"',
            'time' => '15 min watch',
            'link' => 'https://www.youtube.com/watch?v=LnJwH_PZXnM'
        ]
    ],
    'good' => [
        [
            'icon' => 'video',
            'title' => 'Video: "The power of positive psychology"',
            'time' => '15 min watch',
            'link' => 'https://www.youtube.com/watch?v=fLbXAn8ndmI'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Habits of happy people"',
            'time' => '12 min watch',
            'link' => 'https://www.youtube.com/watch?v=GXy__kBVq1M'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Finding your inner strength"',
            'time' => '20 min watch',
            'link' => 'https://www.youtube.com/watch?v=xx163_aIv-w'
        ]
    ],
    'energetic' => [
        [
            'icon' => 'video',
            'title' => 'Video: "Productivity tips for high energy"',
            'time' => '10 min watch',
           'link' => 'https://www.youtube.com/watch?v=UBMk30rjy0o'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "High-energy workout"',
            'time' => '20 min watch',
            'link' => 'https://www.youtube.com/watch?v=ml6cT4AZdqI'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Sustaining energy throughout the day"',
            'time' => '15 min watch',
            'link' => 'https://www.youtube.com/watch?v=g_tea8ZNk5A'
        ]
    ],
    'excellent' => [
        [
            'icon' => 'video',
            'title' => 'Video: "How to maintain positivity"',
            'time' => '12 min watch',
            'link' => 'https://www.youtube.com/watch?v=1-xBhQF3bSs'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Creating moments of joy"',
            'time' => '18 min watch',
            'link' => 'https://www.youtube.com/watch?v=A_u2WFTfbcg'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "The psychology of flow"',
            'time' => '20 min watch',
            'link' => 'https://www.youtube.com/watch?v=8h6IMYRoCZw'
        ]
    ],
    'anxious' => [
        [
            'icon' => 'video',
            'title' => 'Video: "Quick techniques to reduce anxiety"',
            'time' => '5 min watch',
            'link' => 'https://www.youtube.com/watch?v=WWloIAQpMcQ'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Guided anxiety relief meditation"',
            'time' => '15 min watch',
            'link' => 'https://www.youtube.com/watch?v=O-6f5wQXSu8'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "How to overcome anxiety"',
            'time' => '12 min watch',
            'link' => 'https://www.youtube.com/watch?v=ZidGozDhOjg'
        ]
    ],
    'tired' => [
        [
            'icon' => 'video',
            'title' => 'Video: "Energy management techniques"',
            'time' => '8 min watch',
            'link' => 'https://www.youtube.com/watch?v=nls-c2v_Y3Y'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Yoga for energy restoration"',
            'time' => '15 min watch',
            'link' => 'https://www.youtube.com/watch?v=LhrlwlTS55o'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Sleep science and recovery"',
            'time' => '20 min watch',
            'link' => 'https://www.youtube.com/watch?v=LhrlwlTS55o'
        ]
    ],
    'focused' => [
        [
            'icon' => 'video',
            'title' => 'Video: "How to maintain deep focus"',
            'time' => '8 min watch',
            'link' => 'https://www.youtube.com/watch?v=Hu4Yvq-g7_Y'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Productivity techniques"',
            'time' => '12 min watch',
            'link' => 'https://www.youtube.com/watch?v=ZFQu7yuBxOA'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Deep work in a distracted world"',
            'time' => '15 min watch',
            'link' => 'https://www.youtube.com/watch?v=3E7hkPZ-HTk'
        ]
    ],
    'none' => [
        [
            'icon' => 'video',
            'title' => 'Video: "Understanding your emotions"',
            'time' => '10 min watch',
            'link' => 'https://www.youtube.com/watch?v=vXAr5dh23zU'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Benefits of mood tracking"',
            'time' => '8 min watch',
            'link' => 'https://www.youtube.com/watch?v=KJJnMmP1soM'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "The science of happiness"',
            'time' => '12 min watch',
            'link' => 'https://www.youtube.com/watch?v=GXy__kBVq1M'
        ]
    ]
];

// Get resources for current mood
$resources = $moodResources[$currentMood] ?? $moodResources['none'];

// Get emoji for current mood
$moodEmojis = [
    'sad' => '😢',
    'unhappy' => '😞',
    'neutral' => '😐',
    'good' => '😊',
    'energetic' => '💪',
    'excellent' => '🤩',
    'anxious' => '😰',
    'tired' => '😴',
    'focused' => '🎯',
    'none' => '📝'
];

$moodEmoji = $moodEmojis[$currentMood] ?? $moodEmojis['none'];
?>

<div class="header">
    <h1 class="page-title" style="color: #d1789c; font-size: 1.8rem; margin-bottom: 15px; position: relative; display: inline-block; font-weight: 600;">
        Your Recommendations
        <span style="position: absolute; bottom: -8px; left: 0; width: 40%; height: 3px; background: linear-gradient(90deg, #d1789c, #f5d7e3); border-radius: 3px;"></span>
    </h1>
</div>

<!-- Current Mood Display -->
<div class="mood-summary" style="background: white; padding: 20px; border-radius: 16px; margin-bottom: 30px; box-shadow: 0 8px 25px rgba(0,0,0,0.07);">
    <h2 style="font-size: 1.25rem; margin-bottom: 20px; color: #d1789c; font-weight: 500;">Your Mood Today</h2>
    <div style="display: flex; align-items: center;">
        <?php if ($currentMood !== 'none'): ?>
            <div class="mood-circle selected" style="margin-right: 15px;">
                <div class="mood-icon" style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #feeef5, #ffffff); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(209, 120, 156, 0.2);">
                    <i class="fas <?php echo $moodRecommendations[$currentMood][0]['icon']; ?>" style="font-size: 28px; color: #d1789c;"></i>
                </div>
                <span style="display: block; margin-top: 8px; color: #6e3b5c; font-weight: 500; text-transform: capitalize;">
                    <?php echo $currentMood; ?>
                </span>
            </div>
        <?php else: ?>
            <div class="mood-circle" style="margin-right: 15px;">
                <div class="mood-icon" style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #f5f5f5, #ffffff); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <i class="fas fa-question" style="font-size: 28px; color: #999;"></i>
                </div>
                <span style="display: block; margin-top: 8px; color: #666; font-weight: 500;">
                    No mood logged
                </span>
            </div>
        <?php endif; ?>
        <div style="flex: 1;">
            <p style="color: #6e3b5c; margin-bottom: 5px;">
                <?php if ($currentMood !== 'none'): ?>
                    Here are some recommendations based on your current mood.
                <?php else: ?>
                    Log your mood to get personalized recommendations.
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>

<!-- Add this CSS for consistent mood icon styling -->
<style>
    .mood-circle {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 10px;
        border-radius: 12px;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .mood-circle.selected .mood-icon {
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(209, 120, 156, 0.25);
    }

    .mood-icon {
        transition: all 0.3s ease;
    }

    /* Mobile responsiveness */
    @media (max-width: 768px) {
        .mood-circle .mood-icon {
            width: 50px !important;
            height: 50px !important;
        }
        
        .mood-circle .mood-icon i {
            font-size: 24px !important;
        }
        
        .mood-summary {
            padding: 15px !important;
        }
    }

    @media (max-width: 480px) {
        .mood-circle .mood-icon {
            width: 45px !important;
            height: 45px !important;
        }
        
        .mood-circle .mood-icon i {
            font-size: 20px !important;
        }
        
        .mood-circle span {
            font-size: 0.9rem;
        }
    }
</style>

<div class="recommendations-grid">
    <?php foreach ($recommendations as $rec): ?>
    <div class="recommendation-card">
        <div class="recommendation-icon-wrapper">
            <div class="recommendation-icon">
                <i class="fas <?php echo htmlspecialchars($rec['icon']); ?>"></i>
            </div>
        </div>
        <div class="recommendation-content">
            <h3 class="recommendation-title"><?php echo htmlspecialchars($rec['title']); ?></h3>
            <p class="recommendation-text"><?php echo htmlspecialchars($rec['description']); ?></p>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="section-header">
    <h2 class="section-title">Resources for You</h2>
</div>

<div class="resources-container">
    <ul class="resource-list">
        <?php foreach ($resources as $resource): ?>
        <li class="resource-item">
            <div class="resource-icon">
                <i class="fas fa-<?php echo htmlspecialchars($resource['icon']); ?>"></i>
            </div>
            <div class="resource-info">
                <div class="resource-title"><?php echo htmlspecialchars($resource['title']); ?></div>
                <div class="resource-time"><?php echo htmlspecialchars($resource['time']); ?></div>
            </div>
            <a href="<?php echo htmlspecialchars($resource['link']); ?>" target="_blank" class="resource-action">
                <i class="fas fa-external-link-alt"></i>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</div>

<style>
    /* Global Styles */
    .header {
        margin-bottom: 25px;
        padding: 0 10px;
    }
    
    .page-title {
        color: #d1789c;
        font-size: 2rem;
        margin-bottom: 15px;
        position: relative;
        display: inline-block;
        font-weight: 600;
    }
    
    .title-underline {
        position: absolute;
        bottom: -8px;
        left: 0;
        width: 40%;
        height: 3px;
        background: linear-gradient(90deg, #d1789c, #f5d7e3);
        border-radius: 3px;
    }
    
    /* Mood Card Styles */
    .mood-card {
        margin-bottom: 35px;
        background-color: #f8dfeb;
        border: none;
        box-shadow: 0 10px 30px rgba(209, 120, 156, 0.1);
        border-radius: 16px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }
    
    .mood-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 35px rgba(209, 120, 156, 0.15);
    }
    
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 22px 25px 12px;
    }
    
    .card-title {
        font-size: 1.3rem;
        color: #6e3b5c;
        margin: 0;
        font-weight: 500;
    }
    
    .card-icon {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background-color: #fff3f8;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #d1789c;
        box-shadow: 0 4px 12px rgba(209, 120, 156, 0.15);
    }
    
    .card-content {
        padding: 12px 25px 25px;
        font-size: 1.1rem;
        color: #6e3b5c;
    }
    
    /* Recommendations Grid */
    .recommendations-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .recommendation-card {
        background: white;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.07);
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .recommendation-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(209, 120, 156, 0.15);
    }
    
    .recommendation-icon-wrapper {
        margin-bottom: 20px;
    }
    
    .recommendation-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #feeef5, #ffffff);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 15px rgba(209, 120, 156, 0.2);
        margin-bottom: 15px;
        transition: transform 0.3s ease;
    }
    
    .recommendation-card:hover .recommendation-icon {
        transform: scale(1.1);
    }
    
    .recommendation-icon i {
        font-size: 28px;
        color: #d1789c;
    }
    
    .recommendation-content {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .recommendation-title {
        color: #6e3b5c;
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 10px;
    }
    
    .recommendation-text {
        color: #8a5878;
        font-size: 0.95rem;
        line-height: 1.5;
        margin-bottom: 0;
        flex: 1;
    }
    
    /* Section Header */
    .section-header {
        margin: 35px 0 25px;
        padding: 0 10px;
    }
    
    .section-title {
        color: #6e3b5c;
        font-size: 1.6rem;
        font-weight: 500;
        position: relative;
        display: inline-block;
        padding-bottom: 10px;
    }
    
    .section-title:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, #d1789c, #f5d7e3);
        border-radius: 3px;
    }
    
    /* Resources List */
    .resources-container {
        padding: 0 10px;
    }
    
    .resource-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .resource-item {
        display: flex;
        align-items: center;
        padding: 20px;
        background-color: white;
        border-radius: 12px;
        margin-bottom: 15px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .resource-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    }
    
    .resource-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: #fff3f8;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #d1789c;
        margin-right: 20px;
        font-size: 1.1rem;
        box-shadow: 0 5px 15px rgba(209, 120, 156, 0.12);
    }
    
    .resource-info {
        flex: 1;
    }
    
    .resource-title {
        font-weight: 500;
        color: #4a3347;
        margin-bottom: 6px;
        font-size: 1.05rem;
    }
    
    .resource-time {
        font-size: 0.9rem;
        color: #888;
    }
    
    .resource-action {
        background: linear-gradient(135deg, #ff8fb1, #d1789c);
        color: white;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        text-decoration: none;
        box-shadow: 0 5px 15px rgba(209, 120, 156, 0.2);
    }
    
    .resource-action:hover {
        transform: scale(1.1);
        background: linear-gradient(135deg, #ff97b7, #c36c8d);
    }
    
    /* Mobile Responsiveness */
    @media (max-width: 992px) {
        .recommendations-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
    }
    
    @media (max-width: 768px) {
        .page-title {
            font-size: 1.7rem;
            text-align: center;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        
        .title-underline {
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
        }
        
        .recommendations-grid {
            grid-template-columns: 1fr;
            gap: 15px;
            padding: 0 15px;
        }
        
        .card-header {
            padding: 18px 20px 10px;
        }
        
        .card-content {
            padding: 10px 20px 20px;
            font-size: 1rem;
        }
        
        .card-title {
            font-size: 1.2rem;
        }
        
        .section-title {
            font-size: 1.4rem;
            display: block;
            text-align: center;
        }
        
        .section-title:after {
            left: 50%;
            transform: translateX(-50%);
        }
        
        .recommendation-icon {
            width: 40px;
            height: 40px;
        }
        
        .recommendation-title {
            font-size: 1.1rem;
        }
    }
    
    @media (max-width: 576px) {
        .header {
            margin-bottom: 15px;
        }
        
        .page-title {
            font-size: 1.5rem;
        }
        
        .mood-card {
            margin-bottom: 25px;
        }
        
        .card-icon {
            width: 40px;
            height: 40px;
        }
        
        .resource-item {
            padding: 15px;
            flex-wrap: wrap;
        }
        
        .resource-info {
            width: calc(100% - 70px);
            margin-bottom: 10px;
        }
        
        .resource-action {
            margin-left: 70px;
        }
        
        .recommendation-card {
            padding: 18px;
        }
        
        .section-header {
            margin: 25px 0 15px;
        }
    }
    
    /* Ensure breathing room around content on mobile */
    @media (max-width: 480px) {
        .recommendations-grid,
        .resources-container,
        .header,
        .section-header {
            padding-left: 12px;
            padding-right: 12px;
        }
        
        .mood-card {
            margin-left: 12px;
            margin-right: 12px;
            border-radius: 12px;
        }
        
        .recommendation-card {
            padding: 15px;
        }
        
        .recommendation-text {
            font-size: 0.95rem;
        }
    }
</style> 