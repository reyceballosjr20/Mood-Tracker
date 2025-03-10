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

// Handle AJAX request to save mood
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_mood') {
    $mood = new Mood();
    
    // Get data from POST
    $mood_type = $_POST['mood_type'] ?? '';
    $mood_text = $_POST['mood_text'] ?? '';
    
    // Convert mood type to numerical value (1-5)
    $mood_value_map = [
        'sad' => 1,
        'unhappy' => 2,
        'neutral' => 3,
        'good' => 4,
        'excellent' => 5,
        'anxious' => 2,
        'tired' => 2,
        'energetic' => 4,
        'focused' => 4
    ];
    
    $mood_value = $mood_value_map[$mood_type] ?? 3; // Default to neutral
    
    // Save the mood
    $result = $mood->saveMood($_SESSION['user_id'], $mood_type, $mood_value, $mood_text);
    
    // Return JSON response
    header('Content-Type: application/json');
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Mood saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save mood']);
    }
    exit;
}
?>

<div class="header">
    <h1 class="page-title" style="color: #d1789c;">Track My Mood</h1>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 0;">
    <div class="card" style="background-color: #fff9fb; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-right: 0; border-radius: 10px 0 0 10px;">
        <div style="padding: 25px;">
            <h2 style="font-size: 1.2rem; margin-bottom: 20px; color: #d1789c; font-weight: 500;">How are you feeling today?</h2>
            
            <div class="mood-options">
                <button class="mood-circle" data-mood="sad">
                    <div class="mood-icon">
                        <i class="fas fa-sad-tear"></i>
                    </div>
                    <span>sad</span>
                </button>
                <button class="mood-circle" data-mood="unhappy">
                    <div class="mood-icon">
                        <i class="fas fa-frown"></i>
                    </div>
                    <span>unhappy</span>
                </button>
                <button class="mood-circle" data-mood="neutral">
                    <div class="mood-icon">
                        <i class="fas fa-meh"></i>
                    </div>
                    <span>neutral</span>
                </button>
                <button class="mood-circle" data-mood="good">
                    <div class="mood-icon">
                        <i class="fas fa-smile"></i>
                    </div>
                    <span>good</span>
                </button>
                <button class="mood-circle" data-mood="energetic">
                    <div class="mood-icon">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <span>energetic</span>
                </button>
                <button class="mood-circle" data-mood="excellent">
                    <div class="mood-icon">
                        <i class="fas fa-laugh-beam"></i>
                    </div>
                    <span>excellent</span>
                </button>
                <button class="mood-circle" data-mood="anxious">
                    <div class="mood-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <span>anxious</span>
                </button>
                <button class="mood-circle" data-mood="tired">
                    <div class="mood-icon">
                        <i class="fas fa-bed"></i>
                    </div>
                    <span>tired</span>
                </button>
                <button class="mood-circle" data-mood="focused">
                    <div class="mood-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <span>focused</span>
                </button>
            </div>
            
            <div style="margin-top: 30px;">
                <h2 style="font-size: 1.1rem; margin-bottom: 15px; color: #d1789c; font-weight: 500;">What influenced your mood?</h2>
                <textarea id="moodInfluence" style="width: 100%; height: 80px; border-radius: 8px; border: 1px solid #f5d7e3; padding: 12px; background-color: #fffcfd; resize: none;" placeholder="Write what made you feel this way..."></textarea>
            </div>
            
            <div style="text-align: right; margin-top: 20px;">
                <button id="saveMoodBtn" style="background-color: #f5d7e3; color: white; border: none; border-radius: 20px; padding: 8px 24px; font-weight: 500; cursor: pointer; letter-spacing: 1px;">SAVE</button>
            </div>
        </div>
    </div>
    
    <div class="card" style="background-color: #f5d7e3; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); position: relative; margin-left: 0; border-radius: 0 10px 10px 0;">
        <div style="padding: 25px; height: 100%; display: flex; align-items: center; justify-content: center; text-align: center;">
            <div id="inspirationalMessage" style="max-width: 260px;">
                <p style="font-size: 1.1rem; line-height: 1.6; color: #8a5878; font-style: italic;">"You are surrounded by peace, and everything is unfolding as it should"</p>
            </div>
        </div>
    </div>
</div>

<style>
    .mood-options {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        max-width: 100%;
        margin: 0 auto;
    }
    
    .mood-circle {
        display: flex;
        flex-direction: column;
        align-items: center;
        background: none;
        border: none;
        cursor: pointer;
        transition: transform 0.2s;
        padding: 10px 5px;
    }
    
    .mood-circle:hover {
        transform: translateY(-5px);
    }
    
    .mood-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background-color: #feeef5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        margin-bottom: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        transition: all 0.2s;
    }
    
    .mood-circle.selected .mood-icon {
        background-color: #f5b6d0;
        transform: scale(1.1);
        box-shadow: 0 5px 15px rgba(245, 182, 208, 0.4);
    }
    
    .mood-circle span {
        font-size: 14px;
        color: #8a5878;
        margin-top: 8px;
        font-weight: 500;
    }
    
    #saveMoodBtn {
        transition: all 0.2s;
    }
    
    #saveMoodBtn:hover {
        background-color: #d1789c;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(209, 120, 156, 0.3);
    }
    
    @media (max-width: 1200px) {
        .mood-icon {
            width: 70px;
            height: 70px;
            font-size: 35px;
        }
    }
    
    @media (max-width: 992px) {
        .mood-icon {
            width: 65px;
            height: 65px;
            font-size: 32px;
        }
    }
    
    @media (max-width: 768px) {
        div[style*="grid-template-columns: 2fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
        
        .card[style*="border-radius: 10px 0 0 10px"],
        .card[style*="border-radius: 0 10px 10px 0"] {
            border-radius: 10px !important;
            margin: 0 0 15px 0 !important;
        }
        
        .mood-options {
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        
        .mood-icon {
            width: 75px;
            height: 75px;
            font-size: 38px;
        }
    }
    
    @media (max-width: 576px) {
        .mood-options {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .mood-icon {
            width: 85px;
            height: 85px;
            font-size: 42px;
        }
    }
    
    @media (max-width: 350px) {
        .mood-icon {
            width: 70px;
            height: 70px;
            font-size: 35px;
        }
    }
    
    /* Updated style for Font Awesome icons */
    .mood-icon i {
        font-size: 40px;
        color: #d1789c;
        transition: all 0.2s ease;
    }
    
    .mood-circle.selected .mood-icon i {
        color: white;
        transform: scale(1.1);
    }
    
    /* Style for image-based icons */
    .mood-icon img {
        max-width: 80%;
        max-height: 80%;
        object-fit: contain;
    }
    
    .mood-circle.selected .mood-icon {
        background-color: #f5b6d0;
    }
</style>

