<?php
/**
 * OAuth Configuration
 * 
 * Configuration settings for OAuth providers like Google, Facebook, etc.
 */

// Google OAuth settings
// IMPORTANT: Replace these values with your actual Google Cloud Console credentials
define('GOOGLE_CLIENT_ID', '684152867710-1a92h1mu8iknhdhhn7o8ngq1vds10u83.apps.googleusercontent.com'); // From Google Cloud Console
define('GOOGLE_CLIENT_SECRET', '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'); // From Google Cloud Console
define('GOOGLE_REDIRECT_URI', 'http://localhost/Mood-Tracker/dist/login.php'); // Must match exactly what you set in Google Cloud Console 