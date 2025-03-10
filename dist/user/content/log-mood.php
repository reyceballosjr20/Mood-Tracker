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
                    <div class="mood-icon">😔</div>
                    <span>sad</span>
                </button>
                <button class="mood-circle" data-mood="unhappy">
                    <div class="mood-icon">🙁</div>
                    <span>unhappy</span>
                </button>
                <button class="mood-circle" data-mood="neutral">
                    <div class="mood-icon">😐</div>
                    <span>neutral</span>
                </button>
                <button class="mood-circle" data-mood="pleased">
                    <div class="mood-icon">🙂</div>
                    <span>pleased</span>
                </button>
                <button class="mood-circle" data-mood="happy">
                    <div class="mood-icon">😊</div>
                    <span>happy</span>
                </button>
                <button class="mood-circle" data-mood="excited">
                    <div class="mood-icon">😄</div>
                    <span>excited</span>
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
            <div style="max-width: 260px;">
                <p style="font-size: 1.1rem; line-height: 1.6; color: #8a5878; font-style: italic;">"You are surrounded by peace, and everything is unfolding as it should"</p>
            </div>
        </div>
    </div>
</div>

<style>
    .mood-options {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 15px;
    }
    
    .mood-circle {
        display: flex;
        flex-direction: column;
        align-items: center;
        background: none;
        border: none;
        cursor: pointer;
        transition: transform 0.2s;
    }
    
    .mood-circle:hover {
        transform: translateY(-5px);
    }
    
    .mood-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background-color: #feeef5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        margin-bottom: 8px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        transition: all 0.2s;
    }
    
    .mood-circle.selected .mood-icon {
        background-color: #f5b6d0;
        transform: scale(1.1);
        box-shadow: 0 5px 15px rgba(245, 182, 208, 0.4);
    }
    
    .mood-circle span {
        font-size: 12px;
        color: #8a5878;
        margin-top: 5px;
    }
    
    #saveMoodBtn {
        transition: all 0.2s;
    }
    
    #saveMoodBtn:hover {
        background-color: #d1789c;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(209, 120, 156, 0.3);
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
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const moodCircles = document.querySelectorAll('.mood-circle');
        const saveMoodBtn = document.getElementById('saveMoodBtn');
        let selectedMood = null;
        
        moodCircles.forEach(circle => {
            circle.addEventListener('click', function() {
                // Remove selected class from all circles
                moodCircles.forEach(c => c.classList.remove('selected'));
                
                // Add selected class to clicked circle
                this.classList.add('selected');
                
                // Store selected mood
                selectedMood = this.dataset.mood;
            });
        });
        
        saveMoodBtn.addEventListener('click', function() {
            if (!selectedMood) {
                alert('Please select how you are feeling today');
                return;
            }
            
            const moodInfluence = document.getElementById('moodInfluence').value;
            
            // Here you would normally save this data to your backend
            // For now we'll just show a success message
            
            alert('Your mood has been saved!');
            
            // Reset form
            moodCircles.forEach(c => c.classList.remove('selected'));
            document.getElementById('moodInfluence').value = '';
            selectedMood = null;
            
            // Redirect to dashboard
            const dashboardLink = document.querySelector('.menu-link[data-page="dashboard"]');
            if (dashboardLink) {
                dashboardLink.click();
            }
        });
    });
</script> 