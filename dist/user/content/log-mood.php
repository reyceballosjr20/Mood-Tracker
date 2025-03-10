<?php
// Initialize session if needed
session_start();

// Check if user is logged in
if(!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    echo "Authorization required";
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
                <button id="saveMoodBtn" style="background-color: #d1789c; color: white; border: none; border-radius: 20px; padding: 10px 30px; font-weight: 600; cursor: pointer; letter-spacing: 1px; box-shadow: 0 4px 8px rgba(209, 120, 156, 0.3); transition: all 0.3s ease;">SAVE</button>
            </div>
        </div>
    </div>
    
    <div class="card" style="background-color: #f5d7e3; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); position: relative; margin-left: 0; border-radius: 0 10px 10px 0;">
        <div style="padding: 25px; height: 100%; display: flex; align-items: center; justify-content: center; text-align: center;">
            <div id="inspirationalMessage" style="max-width: 260px; transition: opacity 0.3s ease;">
                <p style="font-size: 1.1rem; line-height: 1.6; color: #8a5878; font-style: italic;">"Select a mood to see a personalized message"</p>
            </div>
        </div>
    </div>
</div>

<style>
    /* Updated styles for mood circles with enhanced hover and selection effects */
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
        transition: all 0.3s ease;
        padding: 10px 5px;
        position: relative;
    }
    
    .mood-circle:hover {
        transform: translateY(-8px);
    }
    
    .mood-circle:hover .mood-icon {
        box-shadow: 0 8px 20px rgba(245, 182, 208, 0.4);
        background-color: #feeef5;
    }
    
    .mood-circle:hover .mood-icon i {
        transform: scale(1.15);
        color: #c25f86;
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
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    /* Updated style for Font Awesome icons */
    .mood-icon i {
        font-size: 40px;
        color: #d1789c;
        transition: all 0.3s ease;
    }
    
    /* Updated style for selected mood icons - using a darker color instead of white */
    .mood-circle.selected .mood-icon i {
        color: #7d2b4c; /* Dark burgundy instead of white for better contrast */
        transform: scale(1.15);
        text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    /* Enhanced selected state with darker background */
    .mood-circle.selected .mood-icon {
        background-color: #e8a1c0; /* Darker pink background */
        transform: scale(1.1);
        box-shadow: 0 8px 20px rgba(209, 120, 156, 0.45);
        border: 2px solid #d1789c;
    }
    
    .mood-circle.selected::after {
        content: '';
        position: absolute;
        bottom: 3px;
        left: 50%;
        transform: translateX(-50%);
        width: 12px;
        height: 12px;
        background-color: #d1789c;
        border-radius: 50%;
        animation: pulse 1.5s infinite;
    }
    
    @keyframes pulse {
        0% {
            transform: translateX(-50%) scale(0.8);
            opacity: 0.7;
        }
        50% {
            transform: translateX(-50%) scale(1);
            opacity: 1;
        }
        100% {
            transform: translateX(-50%) scale(0.8);
            opacity: 0.7;
        }
    }
    
    .mood-circle span {
        font-size: 14px;
        color: #8a5878;
        margin-top: 12px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .mood-circle.selected span {
        color: #d1789c;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    
    /* Responsive adjustments */
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
        
        #saveMoodBtn {
            width: 100%;
            padding: 12px 30px;
            margin-top: 25px;
            font-size: 16px;
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
        
        #saveMoodBtn {
            padding: 14px 30px;
            font-size: 16px;
        }
        
        .page-title {
            font-size: 1.5rem;
            text-align: center;
        }
        
        h2[style*="font-size: 1.2rem"], 
        h2[style*="font-size: 1.1rem"] {
            font-size: 1rem !important;
            text-align: center;
        }
    }
    
    @media (max-width: 350px) {
        .mood-icon {
            width: 70px;
            height: 70px;
            font-size: 35px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedMood = null;
    const moodCircles = document.querySelectorAll('.mood-circle');
    const moodInfluence = document.getElementById('moodInfluence');
    const saveMoodBtn = document.getElementById('saveMoodBtn');

    // Handle mood selection
    moodCircles.forEach(circle => {
        circle.addEventListener('click', function() {
            // Remove selected class from all circles
            moodCircles.forEach(c => c.classList.remove('selected'));
            // Add selected class to clicked circle
            this.classList.add('selected');
            selectedMood = this.dataset.mood;
        });
    });

    // Handle save button click
    saveMoodBtn.addEventListener('click', async function() {
        if (!selectedMood) {
            alert('Please select a mood first');
            return;
        }

        try {
            const response = await fetch('save-mood.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    mood_type: selectedMood,
                    mood_text: moodInfluence.value
                })
            });

            const data = await response.json();
            
            if (data.success) {
                alert('Mood logged successfully!');
                // Reset form
                moodCircles.forEach(c => c.classList.remove('selected'));
                moodInfluence.value = '';
                selectedMood = null;
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            alert('Error saving mood: ' + error.message);
        }
    });
});
</script>


