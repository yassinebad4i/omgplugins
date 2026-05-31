<?php
/**
 * Frontend Helper Class - Phase 3
 * SEO, Slug Routing, Dynamic Content Rendering
 */

class Frontend {
    private $json;
    
    public function __construct() {
        global $json;
        $this->json = $json;
    }
    
    /**
     * Get app by slug
     */
    public function getAppBySlug($slug) {
        return $this->json->getBySlug('apps', $slug);
    }
    
    /**
     * Get game by slug
     */
    public function getGameBySlug($slug) {
        return $this->json->getBySlug('games', $slug);
    }
    
    /**
     * Get all published apps
     */
    public function getPublishedApps($limit = null) {
        $apps = $this->json->getPublished('apps');
        usort($apps, function($a, $b) {
            $timeA = strtotime($a['created_at'] ?? '0');
            $timeB = strtotime($b['created_at'] ?? '0');
            return $timeB - $timeA;
        });
        
        if ($limit) {
            $apps = array_slice($apps, 0, $limit);
        }
        
        return $apps;
    }
    
    /**
     * Get all published games
     */
    public function getPublishedGames($limit = null) {
        $games = $this->json->getPublished('games');
        usort($games, function($a, $b) {
            $timeA = strtotime($a['created_at'] ?? '0');
            $timeB = strtotime($b['created_at'] ?? '0');
            return $timeB - $timeA;
        });
        
        if ($limit) {
            $games = array_slice($games, 0, $limit);
        }
        
        return $games;
    }
    
    /**
     * Get apps by category
     */
    public function getAppsByCategory($category, $limit = null) {
        $apps = $this->json->getByCategory('apps', $category);
        $published = array_filter($apps, function($app) {
            return $app['status'] === 'published';
        });
        
        usort($published, function($a, $b) {
            $timeA = strtotime($a['created_at'] ?? '0');
            $timeB = strtotime($b['created_at'] ?? '0');
            return $timeB - $timeA;
        });
        
        if ($limit) {
            $published = array_slice($published, 0, $limit);
        }
        
        return $published;
    }
    
    /**
     * Get games by category
     */
    public function getGamesByCategory($category, $limit = null) {
        $games = $this->json->getByCategory('games', $category);
        $published = array_filter($games, function($game) {
            return $game['status'] === 'published';
        });
        
        usort($published, function($a, $b) {
            $timeA = strtotime($a['created_at'] ?? '0');
            $timeB = strtotime($b['created_at'] ?? '0');
            return $timeB - $timeA;
        });
        
        if ($limit) {
            $published = array_slice($published, 0, $limit);
        }
        
        return $published;
    }
    
    /**
     * Get all categories
     */
    public function getAllCategories() {
        return $this->json->read('categories');
    }
    
    /**
     * Search apps and games
     */
    public function search($query, $type = 'all') {
        $results = [];
        $query = trim($query);
        
        if (strlen($query) < 2) {
            return $results;
        }
        
        if ($type === 'all' || $type === 'apps') {
            $apps = $this->json->getPublished('apps');
            foreach ($apps as $app) {
                if (stripos($app['title'], $query) !== false || 
                    stripos($app['description'] ?? '', $query) !== false) {
                    $app['type'] = 'app';
                    $results[] = $app;
                }
            }
        }
        
        if ($type === 'all' || $type === 'games') {
            $games = $this->json->getPublished('games');
            foreach ($games as $game) {
                if (stripos($game['title'], $query) !== false || 
                    stripos($game['description'] ?? '', $query) !== false) {
                    $game['type'] = 'game';
                    $results[] = $game;
                }
            }
        }
        
        if ($type === 'all' || $type === 'categories') {
            $categories = $this->getAllCategories();
            foreach ($categories as $category) {
                if (stripos($category['name'], $query) !== false) {
                    $category['type'] = 'category';
                    $results[] = $category;
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Track download
     */
    public function trackDownload($itemId, $itemType = 'app') {
        $downloads = $this->json->read('downloads');
        $date = date('Y-m-d');
        $found = false;
        
        foreach ($downloads as &$download) {
            if ($download['item_id'] == $itemId && $download['type'] === $itemType) {
                $download['count']++;
                $download['last_downloaded'] = date('Y-m-d H:i:s');
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $downloads[] = [
                'id' => count($downloads) + 1,
                'item_id' => $itemId,
                'type' => $itemType,
                'count' => 1,
                'last_downloaded' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        
        return $this->json->write('downloads', $downloads);
    }
    
    /**
     * Get download count
     */
    public function getDownloadCount($itemId, $itemType = 'app') {
        $downloads = $this->json->read('downloads');
        
        foreach ($downloads as $download) {
            if ($download['item_id'] == $itemId && $download['type'] === $itemType) {
                return $download['count'] ?? 0;
            }
        }
        
        return 0;
    }
    
    /**
     * Get settings
     */
    public function getSettings() {
        $settings = $this->json->read('settings');
        return !empty($settings) ? $settings[0] : $this->getDefaultSettings();
    }
    
    /**
     * Get default settings
     */
    private function getDefaultSettings() {
        return [
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
        ];
    }
    
    /**
     * Generate SEO meta tags
     */
    public function generateMetaTags($item, $type = 'app') {
        $settings = $this->getSettings();
        $baseUrl = 'https://' . $_SERVER['HTTP_HOST'];
        
        $meta = [
            'title' => $item['seo_title'] ?? $item['title'],
            'description' => $item['seo_description'] ?? substr($item['description'] ?? '', 0, 160),
            'image' => $item['image'] ?? '/images/default.png',
            'url' => $baseUrl . ($type === 'app' ? '/app/' . $item['slug'] . '.php' : '/game/' . $item['slug'] . '.php'),
            'canonical' => $baseUrl . ($type === 'app' ? '/app/' . $item['slug'] . '.php' : '/game/' . $item['slug'] . '.php')
        ];
        
        return $meta;
    }
    
    /**
     * Render SEO meta tags HTML
     */
    public function renderMetaTags($item, $type = 'app') {
        $meta = $this->generateMetaTags($item, $type);
        $settings = $this->getSettings();
        $baseUrl = 'https://' . $_SERVER['HTTP_HOST'];
        
        $html = '<meta charset="UTF-8">' . "\n";
        $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
        $html .= '<title>' . Security::escape($meta['title']) . ' - ' . Security::escape($settings['site_name']) . '</title>' . "\n";
        $html .= '<meta name="description" content="' . Security::escape($meta['description']) . '">' . "\n";
        $html .= '<meta name="keywords" content="' . Security::escape($item['title'] ?? '') . ', apps, games">' . "\n";
        $html .= '<link rel="canonical" href="' . Security::escape($meta['canonical']) . '">' . "\n";
        
        // Open Graph
        $html .= '<meta property="og:title" content="' . Security::escape($meta['title']) . '">' . "\n";
        $html .= '<meta property="og:description" content="' . Security::escape($meta['description']) . '">' . "\n";
        $html .= '<meta property="og:image" content="' . Security::escape($baseUrl . $meta['image']) . '">' . "\n";
        $html .= '<meta property="og:url" content="' . Security::escape($meta['url']) . '">' . "\n";
        $html .= '<meta property="og:type" content="website">' . "\n";
        
        // Twitter Card
        $html .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
        $html .= '<meta name="twitter:title" content="' . Security::escape($meta['title']) . '">' . "\n";
        $html .= '<meta name="twitter:description" content="' . Security::escape($meta['description']) . '">' . "\n";
        $html .= '<meta name="twitter:image" content="' . Security::escape($baseUrl . $meta['image']) . '">' . "\n";
        
        return $html;
    }
}

$frontend = new Frontend();
?>
