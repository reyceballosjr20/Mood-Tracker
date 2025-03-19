<?php
/**
 * OAuth Configuration
 * 
 * Configuration settings for OAuth providers like Google, Facebook, etc.
 */

// Google OAuth settings
// IMPORTANT: Replace these values with your actual Google Cloud Console credentials

define('GOOGLE_CLIENT_ID', '684152867710-1a92h1mu8iknhdhhn7o8ngq1vds10u83.apps.googleusercontent.com'); 
define('GOOGLE_CLIENT_SECRET', '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'); 
define('GOOGLE_REDIRECT_URI', 'http://localhost/Mood-Tracker/dist/login.php'); 

// Facebook OAuth settings
// IMPORTANT: Replace these values with your actual Facebook Developer credentials
define('FACEBOOK_APP_ID', '970665021799791');
define('FACEBOOK_APP_SECRET', '4aeff603d6b510019393a6b6b7d758e8');
define('FACEBOOK_REDIRECT_URI', 'http://localhost/Mood-Tracker/dist/login.php'); 