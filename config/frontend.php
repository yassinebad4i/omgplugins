<?php
/**
 * Frontend Configuration & Loader
 * Handles JSON data loading with caching
 */

session_start();

define('BASE_PATH', dirname(dirname(__FILE__)));
define('JSON_DIR', BASE_PATH . '/data');
define('CACHE_DIR', BASE_PATH . '/cache');
define('UPLOADS_DIR', BASE_PATH . '/uploads');

// Ensure cache directory exists
if (!is_dir(CACHE_DIR)) {
    mkdir(CACHE_DIR, 0755, true);
}

class DataLoader {
    private static $cache = [];
    private static $cacheTTL = 3600; // 1 hour
    
    public static function loadJSON($filename) {
        $filePath = JSON_DIR . '/' . $filename;
        
        // Check memory cache first
        if (isset(self::$cache[$filename])) {
            return self::$cache[$filename];
        }
        
        // Check file cache
        $cacheFile = CACHE_DIR . '/' . md5($filename) . '.cache';
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < self::$cacheTTL) {
            $data = json_decode(file_get_contents($cacheFile), true);
            self::$cache[$filename] = $data;
            return $data;
        }
        
        // Load from JSON file
        if (!file_exists($filePath)) {
            return null;
        }
        
        $content = file_get_contents($filePath);
        $data = json_decode($content, true) ?? [];
        
        // Cache in memory and file
        self::$cache[$filename] = $data;
        file_put_contents($cacheFile, json_encode($data));
        
        return $data;
    }
    
    public static function getSettings() {
        $settings = self::loadJSON('settings.json');
        if (!$settings) {
            $settings = [
                'siteName' => 'OMGPlugins CMS',
                'siteDescription' => 'Manage your apps and games',
                'logo' => '/assets/logo.png',
                'favicon' => '/assets/favicon.ico',
                'footerText' => '© 2024 OMGPlugins. All rights reserved.',
                'socialLinks' => [
                    'twitter' => '',
                    'facebook' => '',
                    'instagram' => '',
                    'youtube' => ''
                ]
            ];
        }
        return $settings;
    }
    
    public static function getAllApps($limit = null) {
        $apps = self::loadJSON('apps.json');
        if (!$apps) return [];
        
        // Sort by date descending
        usort($apps, function($a, $b) {
            return strtotime($b['createdAt'] ?? 0) - strtotime($a['createdAt'] ?? 0);
        });
        
        if ($limit) {
            return array_slice($apps, 0, $limit);
        }
        return $apps;
    }
    
    public static function getAllGames($limit = null) {
        $games = self::loadJSON('games.json');
        if (!$games) return [];
        
        // Sort by date descending
        usort($games, function($a, $b) {
            return strtotime($b['createdAt'] ?? 0) - strtotime($a['createdAt'] ?? 0);
        });
        
        if ($limit) {
            return array_slice($games, 0, $limit);
        }
        return $games;
    }
    
    public static function getAppBySlug($slug) {
        $apps = self::loadJSON('apps.json');
        if (!$apps) return null;
        
        foreach ($apps as $app) {
            if ($app['slug'] === $slug) {
                return $app;
            }
        }
        return null;
    }
    
    public static function getGameBySlug($slug) {
        $games = self::loadJSON('games.json');
        if (!$games) return null;
        
        foreach ($games as $game) {
            if ($game['slug'] === $slug) {
                return $game;
            }
        }
        return null;
    }
    
    public static function getCategories() {
        $categories = self::loadJSON('categories.json');
        return $categories ?? [];
    }
    
    public static function getCategoryById($id) {
        $categories = self::getCategories();
        foreach ($categories as $category) {
            if ($category['id'] === $id) {
                return $category;
            }
        }
        return null;
    }
    
    public static function getDownloads() {
        $downloads = self::loadJSON('downloads.json');
        return $downloads ?? [];
    }
    
    public static function recordDownload($type, $slug) {
        $downloads = self::getDownloads();
        
        $key = $type . '_' . $slug;
        if (!isset($downloads[$key])) {
            $downloads[$key] = [
                'type' => $type,
                'slug' => $slug,
                'count' => 0,
                'lastDownload' => null
            ];
        }
        
        $downloads[$key]['count']++;
        $downloads[$key]['lastDownload'] = date('Y-m-d H:i:s');
        
        // Clear cache
        $cacheFile = CACHE_DIR . '/' . md5('downloads.json') . '.cache';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
        unset(self::$cache['downloads.json']);
        
        return file_put_contents(JSON_DIR . '/downloads.json', json_encode($downloads, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
    
    public static function getDownloadCount($type, $slug) {
        $downloads = self::getDownloads();
        $key = $type . '_' . $slug;
        return $downloads[$key]['count'] ?? 0;
    }
    
    public static function searchContent($query) {
        $query = strtolower(trim($query));
        if (strlen($query) < 2) return [];
        
        $results = [
            'apps' => [],
            'games' => [],
            'categories' => []
        ];
        
        // Search apps
        $apps = self::getAllApps();
        foreach ($apps as $app) {
            if (stripos($app['name'], $query) !== false || 
                stripos($app['description'], $query) !== false ||
                stripos($app['slug'], $query) !== false) {
                $results['apps'][] = $app;
            }
        }
        
        // Search games
        $games = self::getAllGames();
        foreach ($games as $game) {
            if (stripos($game['name'], $query) !== false || 
                stripos($game['description'], $query) !== false ||
                stripos($game['slug'], $query) !== false) {
                $results['games'][] = $game;
            }
        }
        
        // Search categories
        $categories = self::getCategories();
        foreach ($categories as $category) {
            if (stripos($category['name'], $query) !== false) {
                $results['categories'][] = $category;
            }
        }
        
        return $results;
    }
    
    public static function getAppsByCategory($categoryId) {
        $apps = self::getAllApps();
        $filtered = [];
        
        foreach ($apps as $app) {
            if (isset($app['categoryId']) && $app['categoryId'] === $categoryId) {
                $filtered[] = $app;
            }
        }
        
        return $filtered;
    }
    
    public static function getGamesByCategory($categoryId) {
        $games = self::getAllGames();
        $filtered = [];
        
        foreach ($games as $game) {
            if (isset($game['categoryId']) && $game['categoryId'] === $categoryId) {
                $filtered[] = $game;
            }
        }
        
        return $filtered;
    }
}

// Helper functions for templates
function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

function sanitizeOutput($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function generateSlug($string) {
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-');
}
