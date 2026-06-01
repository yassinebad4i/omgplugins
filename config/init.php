<?php
/**
 * Application Initialization
 * Loads all required files and initializes the application
 */

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start session
session_start();

// Load core classes
require_once __DIR__ . '/../includes/json.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/frontend.php';
require_once __DIR__ . '/../includes/router.php';

// Set timezone
date_default_timezone_set('UTC');

// Set base URL
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    define('BASE_URL', $protocol . '://' . $host);
}

// Initialize data directories
$dataDirs = [
    __DIR__ . '/../data',
    __DIR__ . '/../uploads',
    __DIR__ . '/../uploads/images',
    __DIR__ . '/../uploads/screenshots'
];

foreach ($dataDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Initialize default data files if they don't exist
$defaultFiles = [
    'admin' => [],
    'apps' => [],
    'games' => [],
    'categories' => [
        ['id' => 1, 'name' => 'Utilities', 'slug' => 'utilities', 'created_at' => date('Y-m-d H:i:s')],
        ['id' => 2, 'name' => 'Games', 'slug' => 'games', 'created_at' => date('Y-m-d H:i:s')]
    ],
    'downloads' => [],
    'settings' => [
        [
            'id' => 1,
            'site_name' => 'OMGPlugins',
            'site_description' => 'A complete self-hosted CMS for managing modded apps and games',
            'logo' => '/images/logo.png',
            'favicon' => '/favicon.ico',
            'footer_text' => '© 2024 OMGPlugins. All rights reserved.',
            'social_facebook' => '',
            'social_twitter' => '',
            'social_instagram' => '',
            'social_github' => '',
            'contact_email' => 'contact@omgplugins.local'
        ]
    ]
];

foreach ($defaultFiles as $filename => $defaultData) {
    $filepath = __DIR__ . '/../data/' . $filename . '.json';
    if (!file_exists($filepath)) {
        file_put_contents($filepath, json_encode($defaultData, JSON_PRETTY_PRINT));
    }
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

?>
