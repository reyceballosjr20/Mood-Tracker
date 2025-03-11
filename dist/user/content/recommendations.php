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

// Define mood-specific recommendations
$moodRecommendations = [
    'sad' => [
        [
            'title' => 'Gentle Movement',
            'icon' => 'walking',
            'description' => 'A short walk outside can help lift your mood and provide a change of scenery.',
            'action' => 'Get Started',
        ],
        [
            'title' => 'Connect with Someone',
            'icon' => 'phone',
            'description' => 'Reaching out to a friend or family member can provide comfort and perspective.',
            'action' => 'See Suggestions',
        ],
        [
            'title' => 'Self-Compassion Practice',
            'icon' => 'heart',
            'description' => 'Be kind to yourself today. Try a guided self-compassion meditation.',
            'action' => 'Try Now',
        ]
    ],
    'unhappy' => [
        [
            'title' => 'Mood-Boosting Music',
            'icon' => 'music',
            'description' => 'Listen to uplifting music that can help shift your emotional state.',
            'action' => 'Play Playlist',
        ],
        [
            'title' => 'Gratitude Exercise',
            'icon' => 'star',
            'description' => 'Write down three things you\'re grateful for to help shift perspective.',
            'action' => 'Start Exercise',
        ],
        [
            'title' => 'Creative Expression',
            'icon' => 'paint-brush',
            'description' => 'Try drawing, writing, or another creative outlet to express your feelings.',
            'action' => 'Get Ideas',
        ]
    ],
    'neutral' => [
        [
            'title' => 'Mindfulness Practice',
            'icon' => 'leaf',
            'description' => 'A short mindfulness exercise can help you connect with the present moment.',
            'action' => 'Begin Practice',
        ],
        [
            'title' => 'Goal Setting',
            'icon' => 'bullseye',
            'description' => 'Set one small, achievable goal for today to create a sense of purpose.',
            'action' => 'Set Goal',
        ],
        [
            'title' => 'Nature Connection',
            'icon' => 'tree',
            'description' => 'Spending time in nature can help improve your mood and energy levels.',
            'action' => 'Find Activities',
        ]
    ],
    'good' => [
        [
            'title' => 'Skill Building',
            'icon' => 'brain',
            'description' => 'Use your positive mood to learn something new or practice a skill.',
            'action' => 'Explore Options',
        ],
        [
            'title' => 'Acts of Kindness',
            'icon' => 'hands-helping',
            'description' => 'Doing something nice for others can maintain and enhance your good mood.',
            'action' => 'Get Ideas',
        ],
        [
            'title' => 'Reflection Journal',
            'icon' => 'book',
            'description' => 'Record what contributed to your good mood to reference in the future.',
            'action' => 'Start Writing',
        ]
    ],
    'energetic' => [
        [
            'title' => 'Physical Exercise',
            'icon' => 'dumbbell',
            'description' => 'Channel your energy into a workout that will strengthen your body and mind.',
            'action' => 'See Workouts',
        ],
        [
            'title' => 'Productive Task',
            'icon' => 'tasks',
            'description' => 'Use your high energy to tackle a challenging task on your to-do list.',
            'action' => 'Plan Task',
        ],
        [
            'title' => 'Creative Project',
            'icon' => 'lightbulb',
            'description' => 'Start a creative project that you\'ve been wanting to work on.',
            'action' => 'Get Started',
        ]
    ],
    'excellent' => [
        [
            'title' => 'Share Your Joy',
            'icon' => 'smile-beam',
            'description' => 'Connect with others and spread your positive energy through social interaction.',
            'action' => 'Connect Now',
        ],
        [
            'title' => 'Capture the Moment',
            'icon' => 'camera',
            'description' => 'Document this excellent mood through journaling, photos, or voice notes.',
            'action' => 'Record Moment',
        ],
        [
            'title' => 'Future Planning',
            'icon' => 'calendar',
            'description' => 'Use this positive state to plan something you\'re looking forward to.',
            'action' => 'Start Planning',
        ]
    ],
    'anxious' => [
        [
            'title' => 'Breathing Exercise',
            'icon' => 'wind',
            'description' => 'Try a 4-7-8 breathing technique to help calm your nervous system.',
            'action' => 'Start Breathing',
        ],
        [
            'title' => 'Grounding Practice',
            'icon' => 'shoe-prints',
            'description' => 'Use the 5-4-3-2-1 technique to ground yourself in the present moment.',
            'action' => 'Try Now',
        ],
        [
            'title' => 'Worry Time',
            'icon' => 'clock',
            'description' => 'Schedule a specific time to address your worries, then set them aside for now.',
            'action' => 'Learn More',
        ]
    ],
    'tired' => [
        [
            'title' => 'Rest & Recovery',
            'icon' => 'bed',
            'description' => 'Give yourself permission to rest. A short nap or relaxation period can help.',
            'action' => 'Set Timer',
        ],
        [
            'title' => 'Gentle Stretching',
            'icon' => 'child',
            'description' => 'Light stretching can help increase blood flow and reduce fatigue.',
            'action' => 'See Stretches',
        ],
        [
            'title' => 'Energy Audit',
            'icon' => 'battery-half',
            'description' => 'What\'s draining your energy and what might help restore it.',
            'action' => 'Start Audit',
        ]
    ],
    'focused' => [
        [
            'title' => 'Deep Work Session',
            'icon' => 'laptop-code',
            'description' => 'Use your focused state for a productive deep work session on an important task.',
            'action' => 'Set Timer',
        ],
        [
            'title' => 'Learning Activity',
            'icon' => 'graduation-cap',
            'description' => 'Take advantage of your focus to learn something new or challenging.',
            'action' => 'Explore Topics',
        ],
        [
            'title' => 'Problem Solving',
            'icon' => 'puzzle-piece',
            'description' => 'Address a complex problem that requires your full attention and focus.',
            'action' => 'Get Started',
        ]
    ],
    'none' => [
        [
            'title' => 'Meditation',
            'icon' => 'spa',
            'description' => 'A 10-minute guided meditation can help improve your emotional balance and reduce stress levels.',
            'action' => 'Start Now',
        ],
        [
            'title' => 'Physical Activity',
            'icon' => 'running',
            'description' => 'A quick 20-minute walk can boost your endorphins and improve your mood immediately.',
            'action' => 'View Suggestions',
        ],
        [
            'title' => 'Gratitude Practice',
            'icon' => 'heart',
            'description' => 'Writing down three things you\'re grateful for can shift your perspective and improve your mood.',
            'action' => 'Start Journal',
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
            'title' => 'Video: "Understanding and Coping with Sadness"',
            'time' => '7 min watch',
            'link' => 'https://www.youtube.com/watch?v=8Su5VtKeXU8'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Self-Care Practices for Low Mood"',
            'time' => '10 min watch',
            'link' => 'https://www.youtube.com/watch?v=TFbv757kup4'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Finding Hope During Difficult Times"',
            'time' => '25 min watch',
            'link' => 'https://www.youtube.com/watch?v=xRxT9cOKiM8'
        ]
    ],
    'unhappy' => [
        [
            'icon' => 'video',
            'title' => 'Video: "Turning a Bad Day Around"',
            'time' => '5 min watch',
            'link' => 'https://www.youtube.com/watch?v=7s0S6FeS5Z0'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Quick Mood Boosters That Work"',
            'time' => '8 min watch',
            'link' => 'https://www.youtube.com/watch?v=F28MGLlpP90'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Reframing Negative Thoughts"',
            'time' => '22 min watch',
            'link' => 'https://www.youtube.com/watch?v=1vx8iUvfyCY'
        ]
    ],
    'neutral' => [
        [
            'icon' => 'video',
            'title' => 'Video: "Finding Meaning in Everyday Life"',
            'time' => '6 min watch',
            'link' => 'https://www.youtube.com/watch?v=HdqVF7-8wng'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Mindfulness for Emotional Awareness"',
            'time' => '15 min watch',
            'link' => 'https://www.youtube.com/watch?v=w6T02g5hnT4'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Creating More Engaging Days"',
            'time' => '28 min watch',
            'link' => 'https://www.youtube.com/watch?v=fLJsdqxnZb0'
        ]
    ],
    'good' => [
        [
            'icon' => 'video',
            'title' => 'Video: "Building on Positive Momentum"',
            'time' => '5 min watch',
            'link' => 'https://www.youtube.com/watch?v=ZizdB0TgAVM'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Habits That Maintain Good Moods"',
            'time' => '12 min watch',
            'link' => 'https://www.youtube.com/watch?v=75d_29QWELk'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "The Science of Positive Emotions"',
            'time' => '30 min watch',
            'link' => 'https://www.youtube.com/watch?v=GXy__kBVq1M'
        ]
    ],
    'energetic' => [
        [
            'icon' => 'video',
            'title' => 'Video: "Channeling Energy Productively"',
            'time' => '4 min watch',
            'link' => 'https://www.youtube.com/watch?v=Tz9iJ7TlQiw'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "High-Energy Workout Routines"',
            'time' => '20 min watch',
            'link' => 'https://www.youtube.com/watch?v=ml6cT4AZdqI'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Maintaining Sustainable Energy"',
            'time' => '35 min watch',
            'link' => 'https://www.youtube.com/watch?v=uju-8P7zcFU'
        ]
    ],
    'excellent' => [
        [
            'icon' => 'video',
            'title' => 'Video: "Making the Most of Peak Experiences"',
            'time' => '6 min watch',
            'link' => 'https://www.youtube.com/watch?v=qzR62JJCMBQ'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Creating More Peak Moments"',
            'time' => '11 min watch',
            'link' => 'https://www.youtube.com/watch?v=nT1TpVzGRVQ'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "The Psychology of Flow States"',
            'time' => '40 min watch',
            'link' => 'https://www.youtube.com/watch?v=znwUCNrjpD4'
        ]
    ],
    'anxious' => [
        [
            'icon' => 'video',
            'title' => 'Video: "Quick Techniques to Reduce Anxiety"',
            'time' => '5 min watch',
            'link' => 'https://www.youtube.com/watch?v=WWloIAQpMcQ'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Guided Anxiety Relief Meditation"',
            'time' => '15 min watch',
            'link' => 'https://www.youtube.com/watch?v=O-6f5wQXSu8'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Understanding Your Anxiety Triggers"',
            'time' => '32 min watch',
            'link' => 'https://www.youtube.com/watch?v=BVJkf8IuRjE'
        ]
    ],
    'tired' => [
        [
            'icon' => 'video',
            'title' => 'Video: "Energy Management vs. Time Management"',
            'time' => '7 min watch',
            'link' => 'https://www.youtube.com/watch?v=PCRSVRD2EAk'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Yoga for Energy Restoration"',
            'time' => '18 min watch',
            'link' => 'https://www.youtube.com/watch?v=UEEsdXn8oG8'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Sleep Science and Recovery"',
            'time' => '45 min watch',
            'link' => 'https://www.youtube.com/watch?v=5MuIMqhT8DM'
        ]
    ],
    'focused' => [
        [
            'icon' => 'video',
            'title' => 'Video: "Maintaining Deep Focus States"',
            'time' => '8 min watch',
            'link' => 'https://www.youtube.com/watch?v=Hu4Yvq-g7_Y'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Productivity Techniques for Flow"',
            'time' => '14 min watch',
            'link' => 'https://www.youtube.com/watch?v=y2X7c9TUQJ8'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Deep Work and Focus in a Distracted World"',
            'time' => '38 min watch',
            'link' => 'https://www.youtube.com/watch?v=3E7hkPZ-HTk'
        ]
    ],
    'none' => [
        [
            'icon' => 'video',
            'title' => 'Video: "Understanding Your Emotions"',
            'time' => '6 min watch',
            'link' => 'https://www.youtube.com/watch?v=vXAr5dh23zU'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "Introduction to Mood Tracking"',
            'time' => '9 min watch',
            'link' => 'https://www.youtube.com/watch?v=W1-qN3YDsVQ'
        ],
        [
            'icon' => 'video',
            'title' => 'Video: "The Science of Happiness"',
            'time' => '30 min watch',
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
        Recommendations
        <span style="position: absolute; bottom: -8px; left: 0; width: 40%; height: 3px; background: linear-gradient(90deg, #d1789c, #f5d7e3); border-radius: 3px;"></span>
    </h1>
</div>

<div class="card" style="margin-bottom: 30px; background-color: #f8dfeb; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border-radius: 16px;">
    <div class="card-header">
        <h2 class="card-title">Your Mood Today <?php echo $moodEmoji; ?></h2>
        <div class="card-icon">
            <i class="fas fa-lightbulb"></i>
        </div>
    </div>
    <div class="card-content" style="font-size: 18px;">
        <?php if ($currentMood != 'none'): ?>
            Based on your <?php echo htmlspecialchars(ucfirst($currentMood)); ?> mood, here are some personalized recommendations
        <?php else: ?>
            Log your mood today to get personalized recommendations
        <?php endif; ?>
    </div>
</div>

<!-- Update the recommendations grid to remove buttons -->
<div class="recommendations-grid">
    <?php foreach ($recommendations as $rec): ?>
    <div class="recommendation-card">
        <h3 class="recommendation-title"><?php echo htmlspecialchars($rec['title']); ?></h3>
        <div class="recommendation-icon">
            <i class="fas fa-<?php echo htmlspecialchars($rec['icon']); ?>"></i>
        </div>
        <p class="recommendation-text"><?php echo htmlspecialchars($rec['description']); ?></p>
    </div>
    <?php endforeach; ?>
</div>

<div class="section-header">
    <h2 class="section-title">Resources for You</h2>
</div>

<div class="recent-activities">
    <ul class="activity-list">
        <?php foreach ($resources as $resource): ?>
        <li class="activity-item">
            <div class="activity-icon">
                <i class="fas fa-<?php echo htmlspecialchars($resource['icon']); ?>"></i>
            </div>
            <div class="activity-info">
                <div class="activity-title"><?php echo htmlspecialchars($resource['title']); ?></div>
                <div class="activity-time"><?php echo htmlspecialchars($resource['time']); ?></div>
            </div>
            <a href="<?php echo htmlspecialchars($resource['link']); ?>" target="_blank" class="activity-action">
                <i class="fas fa-external-link-alt"></i>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</div>

<style>
    /* Updated styles to match the screenshot without buttons */
    .recommendation-card {
        background-color: white;
        border-radius: 16px;
        padding: 25px;
        position: relative;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        height: 100%;
    }
    
    .recommendation-text {
        font-size: 1.1rem;
        line-height: 1.5;
        color: #333;
        margin-bottom: 0; /* Remove bottom margin since there's no button */
    }
    
    /* Remove the button styles */
    .recommendation-button {
        display: none;
    }
    
    /* Updated styles to match the screenshot */
    .recommendations-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 30px;
        width: 100%;
        padding: 0 15px;
    }
    
    .recommendation-title {
        color: #6e3b5c;
        font-size: 1.1rem;
        font-weight: 500;
        margin-top: 0;
        margin-bottom: 15px;
        padding-right: 40px;
    }
    
    .recommendation-icon {
        position: absolute;
        top: 25px;
        right: 25px;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: #fff3f8;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #d1789c;
    }
    
    /* Mobile responsiveness */
    @media (max-width: 992px) {
        .recommendations-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .recommendations-grid {
            grid-template-columns: 1fr;
        }
        
        .recommendation-card {
            padding: 20px;
        }
        
        .recommendation-icon {
            top: 20px;
            right: 20px;
        }
    }
    
    /* Card styles */
    .card {
        transition: none !important;
    }
    
    .card:hover {
        transform: none !important;
        box-shadow: 0 8px 25px rgba(0,0,0,0.07) !important;
    }
    
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 20px 10px 20px;
    }
    
    .card-title {
        font-size: 1.2rem;
        color: #6e3b5c;
        margin: 0;
        font-weight: 500;
    }
    
    .card-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #fff3f8;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #d1789c;
    }
    
    .card-content {
        padding: 10px 20px 20px;
    }
    
    .card-footer {
        padding: 0 20px 20px;
    }
    
    /* Section header */
    .section-header {
        margin: 30px 0 20px;
    }
    
    .section-title {
        color: #6e3b5c;
        font-size: 1.4rem;
        font-weight: 500;
    }
    
    /* Activity list */
    .activity-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .activity-item {
        display: flex;
        align-items: center;
        padding: 18px;
        background-color: white;
        border-radius: 12px;
        margin-bottom: 15px;
        box-shadow: 0 3px 12px rgba(0,0,0,0.05);
        transition: transform 0.2s ease;
    }
    
    .activity-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
    
    .activity-icon {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background-color: #fff3f8;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #d1789c;
        margin-right: 18px;
        font-size: 1.1rem;
    }
    
    .activity-info {
        flex: 1;
    }
    
    .activity-title {
        font-weight: 500;
        color: #4a3347;
        margin-bottom: 6px;
        font-size: 1rem;
    }
    
    .activity-time {
        font-size: 0.85rem;
        color: #888;
    }
    
    .activity-action {
        background: #ff8fb1;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.2s ease;
        text-decoration: none;
    }
    
    .activity-action:hover {
        background-color: #e67d9a;
    }
    
    /* Mobile responsiveness */
    @media (max-width: 768px) {
        /* Ensure content fills available width */
        .header, 
        .recommendations-grid,
        .section-header,
        .recent-activities {
            width: 100%;
            margin-left: 0;
            margin-right: 0;
            padding-left: 0;
            padding-right: 0;
            box-sizing: border-box;
        }
        
        .card {
            width: 100%;
            margin-left: 0;
            margin-right: 0;
            border-radius: 12px;
        }
        
        .recommendations-grid {
            grid-template-columns: 1fr;
            gap: 15px;
            padding: 0 15px;
        }
        
        .section-header {
            padding: 0 15px;
        }
        
        .recent-activities {
            padding: 0 15px;
        }
        
        .activity-list {
            width: 100%;
        }
        
        .activity-item {
            width: 100%;
            box-sizing: border-box;
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
        
        .card {
            margin-bottom: 15px !important;
        }
        
        .section-title {
            font-size: 1.2rem;
        }
    }
    
    @media (max-width: 576px) {
        .activity-item {
            flex-wrap: wrap;
        }
        
        .activity-info {
            width: calc(100% - 55px);
            margin-bottom: 10px;
        }
        
        .activity-action {
            margin-left: 55px;
        }
        
        h1.page-title {
            font-size: 1.5rem !important;
        }
        
        .card-title {
            font-size: 1.1rem;
        }
        
        .card-content {
            font-size: 0.95rem !important;
        }
    }
</style> 