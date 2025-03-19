<?php
/**
 * OAuth Configuration
 * 
 * Configuration settings for OAuth providers like Google, Facebook, etc.
 */

// Google OAuth settings
// IMPORTANT: Replace these values with your actual Google Cloud Console credentials
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID'); // From Google Cloud Console
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET'); // From Google Cloud Console
define('GOOGLE_REDIRECT_URI', 'http://localhost/Mood-Tracker/dist/google-callback.php'); // Must match exactly what you set in Google Cloud Console 