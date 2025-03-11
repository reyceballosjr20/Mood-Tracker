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
?>

<div class="header">
    <h1 class="page-title" style="color: #d1789c; font-size: 1.8rem; margin-bottom: 15px; position: relative; display: inline-block; font-weight: 600;">
        Track My Mood
        <span style="position: absolute; bottom: -8px; left: 0; width: 40%; height: 3px; background: linear-gradient(90deg, #d1789c, #f5d7e3); border-radius: 3px;"></span>
    </h1>
</div>


<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px;">
    <div class="card" style="background-color: #fff; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border-radius: 16px; transition: none; overflow: hidden;">
        <div style="padding: 30px;">
            <h2 style="font-size: 1.25rem; margin-bottom: 25px; color: #d1789c; font-weight: 500; border-bottom: 1px solid #f9e8f0; padding-bottom: 12px;">How are you feeling today?</h2>
            
            <div class="mood-options" style="margin-bottom: 10px;">
                <!-- Primary Emotions -->
                <button class="mood-circle" data-mood="happy">
                    <div class="mood-icon">
                        <i class="fas fa-smile-beam"></i>
                    </div>
                    <span style="margin-top: 10px; font-weight: 500;">happy</span>
                </button>
                <button class="mood-circle" data-mood="sad">
                    <div class="mood-icon">
                        <i class="fas fa-sad-tear"></i>
                    </div>
                    <span style="margin-top: 10px; font-weight: 500;">sad</span>
                </button>
                <button class="mood-circle" data-mood="angry">
                    <div class="mood-icon">
                        <i class="fas fa-angry"></i>
                    </div>
                    <span style="margin-top: 10px; font-weight: 500;">angry</span>
                </button>
                <button class="mood-circle" data-mood="anxious">
                    <div class="mood-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <span style="margin-top: 10px; font-weight: 500;">anxious</span>
                </button>
                <button class="mood-circle" data-mood="stressed">
                    <div class="mood-icon">
                        <i class="fas fa-head-side-virus"></i>
                    </div>
                    <span style="margin-top: 10px; font-weight: 500;">stressed</span>
                </button>
                <button class="mood-circle" data-mood="calm">
                    <div class="mood-icon">
                        <i class="fas fa-peace"></i>
                    </div>
                    <span style="margin-top: 10px; font-weight: 500;">calm</span>
                </button>
                <button class="mood-circle" data-mood="tired">
                    <div class="mood-icon">
                        <i class="fas fa-bed"></i>
                    </div>
                    <span style="margin-top: 10px; font-weight: 500;">tired</span>
                </button>
                <button class="mood-circle" data-mood="energetic">
                    <div class="mood-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <span style="margin-top: 10px; font-weight: 500;">energetic</span>
                </button>
                <button class="mood-circle" data-mood="neutral">
                    <div class="mood-icon">
                        <i class="fas fa-meh"></i>
                    </div>
                    <span style="margin-top: 10px; font-weight: 500;">neutral</span>
                </button>
                <button class="mood-circle" data-mood="excited">
                    <div class="mood-icon">
                        <i class="fas fa-grin-stars"></i>
                    </div>
                    <span style="margin-top: 10px; font-weight: 500;">excited</span>
                </button>
                <button class="mood-circle" data-mood="frustrated">
                    <div class="mood-icon">
                        <i class="fas fa-angry"></i>
                    </div>
                    <span style="margin-top: 10px; font-weight: 500;">frustrated</span>
                </button>
                <button class="mood-circle" data-mood="grateful">
                    <div class="mood-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <span style="margin-top: 10px; font-weight: 500;">grateful</span>
                </button>
            </div>
            
            <div style="margin-top: 35px;">
                <h2 style="font-size: 1.15rem; margin-bottom: 15px; color: #d1789c; font-weight: 500;">What influenced your mood?</h2>
                <textarea id="moodInfluence" style="width: 100%; height: 100px; border-radius: 12px; border: 1px solid #f5d7e3; padding: 15px; background-color: #fffcfd; resize: none; font-family: inherit; font-size: 0.95rem; box-shadow: inset 0 2px 5px rgba(0,0,0,0.02); transition: all 0.3s ease;" placeholder="Write what made you feel this way..."></textarea>
            </div>
            
            <div style="text-align: right; margin-top: 25px;">
                <button id="saveMoodBtn" style="position: relative; overflow: hidden;">SAVE</button>
            </div>
        </div>
    </div>
    
    <div class="card" style="background: linear-gradient(135deg, #fff, #fdf7fa); border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border-radius: 16px; display: flex; flex-direction: column;">
        <div style="padding: 25px 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; flex: 1; position: relative; overflow: hidden;">
            <div style="position: absolute; top: 0; right: 0; width: 120px; height: 120px; background: linear-gradient(135deg, rgba(209, 120, 156, 0.12), transparent); border-radius: 0 0 0 100%; z-index: 0;"></div>
            <div style="position: absolute; bottom: 0; left: 0; width: 100px; height: 100px; background: linear-gradient(135deg, rgba(209, 120, 156, 0.07), transparent); border-radius: 0 100% 0 0; z-index: 0;"></div>
            
            <div id="inspirationalMessage" style="position: relative; z-index: 1; padding: 15px; text-align: center; transition: opacity 0.5s ease;">
                <p style="font-size: 1.15rem; line-height: 1.7; color: #8a5878; font-style: italic; margin-bottom: 20px;">"Select a mood to see a personalized message"</p>
            </div>
        </div>
    </div>
</div>

<style>
    .mood-options {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 25px;
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
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        padding: 10px 5px;
        position: relative;
        overflow: visible;
    }
    
    .mood-circle:focus {
        outline: none;
    }
    
    /* Keep hover animations only for mood icons */
    .mood-circle:hover {
        transform: translateY(-7px);
    }
    
    .mood-circle:hover .mood-icon {
        box-shadow: 0 15px 25px rgba(209, 120, 156, 0.3);
    }
    
    .mood-circle:active {
        transform: translateY(-3px);
    }
    
    .mood-icon {
        width: 85px;
        height: 85px;
        border-radius: 50%;
        background: linear-gradient(135deg, #feeef5, #ffffff);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 
            0 10px 20px rgba(245, 182, 208, 0.2),
            0 5px 8px rgba(0, 0, 0, 0.03),
            inset 0 -5px 10px rgba(255, 255, 255, 0.8);
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    /* Updated style for Font Awesome icons */
    .mood-icon i {
        font-size: 40px;
        color: #d1789c;
        transition: all 0.3s ease;
        filter: drop-shadow(0 2px 3px rgba(0,0,0,0.1));
    }
    
    /* Updated style for selected mood icons - using a darker color instead of white */
    .mood-circle.selected .mood-icon i {
        color: #7d2b4c; /* Dark burgundy instead of white for better contrast */
        transform: scale(1.15);
        filter: drop-shadow(0 3px 5px rgba(0,0,0,0.2));
    }
    
    /* Enhanced selected state with darker background */
    .mood-circle.selected .mood-icon {
        background: linear-gradient(135deg, #e8a1c0, #f5d2e3);
        border-color: #d1789c;
    }
    
    /* Remove animation from specific elements as requested */
    .card {
        transition: none !important;
    }
    
    .card:hover {
        transform: none !important;
        box-shadow: 0 8px 25px rgba(0,0,0,0.07) !important;
    }

    /* Textarea style */
    #moodInfluence:focus {
        border-color: #d1789c;
        box-shadow: 0 0 0 3px rgba(209, 120, 156, 0.15);
        outline: none;
    }
    
    /* Save button without animations */
    #saveMoodBtn {
        background: linear-gradient(135deg, #d1789c, #e896b8);
        color: white;
        border: none;
        border-radius: 25px;
        padding: 12px 32px;
        font-weight: 600;
        cursor: pointer;
        letter-spacing: 1px;
        font-size: 0.95rem;
        box-shadow: 0 4px 10px rgba(209, 120, 156, 0.25);
        transition: background-color 0.2s ease;
        border: none;
        outline: none;
    }
    
    /* Remove ripple animation and simplify hover/active states */
    #saveMoodBtn::after {
        display: none;
    }
    
    #saveMoodBtn:hover:not(:disabled) {
        background: linear-gradient(135deg, #c76490, #e48db0);
        transform: none;
    }
    
    #saveMoodBtn:active:not(:disabled) {
        transform: none;
    }
    
    #saveMoodBtn.disabled,
    #saveMoodBtn:disabled {
        background: linear-gradient(135deg, #d1d1d1, #e6e6e6);
        color: #999;
        cursor: not-allowed;
        box-shadow: none;
    }
    
    /* Keep the mobile responsiveness */
    @media (max-width: 1200px) {
        .mood-icon {
            width: 75px;
            height: 75px;
        }
        
        .mood-icon i {
            font-size: 35px;
        }
    }
    
    @media (max-width: 992px) {
        .mood-icon {
            width: 70px;
            height: 70px;
        }
        
        .mood-icon i {
            font-size: 32px;
        }
        
        .mood-options {
            gap: 20px;
        }
    }
    
    @media (max-width: 768px) {
        /* Fix the gap between sidebar and content */
        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .container-fluid,
        .row,
        main,
        .content-area {
            padding-left: 0 !important;
            padding-right: 0 !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
        
        /* Ensure the sidebar connects directly with content */
        .sidebar {
            margin-right: 0;
            padding-right: 0;
        }
        
        /* Ensure content fills available width */
        .log-mood-container {
            padding: 0 15px;
            margin: 0;
            width: 100%;
            box-sizing: border-box;
        }
        
        /* Reduce spacing between elements */
        .mood-options {
            margin-top: 15px;
        }
        
        h2, h3 {
            margin-bottom: 15px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        div[style*="grid-template-columns: 2fr 1fr"] {
            grid-template-columns: 1fr !important;
            gap: 25px !important;
        }
        
        .card {
            margin: 0 !important;
        }
        
        .mood-options {
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        
        .mood-icon {
            width: 75px;
            height: 75px;
        }
        
        .mood-icon i {
            font-size: 38px;
        }
        
        #saveMoodBtn {
            width: 100%;
            padding: 14px 35px;
            margin-top: 25px;
            font-size: 16px;
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
        .mood-options {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px 15px;
        }
        
        .mood-icon {
            width: 85px;
            height: 85px;
        }
        
        .mood-icon i {
            font-size: 42px;
        }
        
        #saveMoodBtn {
            padding: 14px 30px;
            font-size: 16px;
        }
        
        .page-title {
            font-size: 1.5rem;
            text-align: center;
        }
        
        h2[style*="font-size: 1.25rem"], 
        h2[style*="font-size: 1.15rem"] {
            font-size: 1.1rem !important;
            text-align: center;
        }
        
        div[style*="padding: 30px"] {
            padding: 20px !important;
        }
    }
    
    @media (max-width: 350px) {
        .mood-icon {
            width: 70px;
            height: 70px;
        }
        
        .mood-icon i {
            font-size: 35px;
        }
        
        .mood-options {
            gap: 15px 10px;
        }
    }
</style>

<!-- All JavaScript code has been moved to the external mood-tracker.js file -->

<script>
    // Trigger reinitialization when content is loaded
    if (typeof reinitMoodTracker === 'function') {
        reinitMoodTracker();
    }
</script>


