<?php
/**
 * JSON File Engine
 * Safe reading and writing of JSON data files
 */

class JSONEngine {
    private $dataDir = __DIR__ . '/../data/';
    
    public function __construct() {
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0755, true);
        }
    }
    
    /**
     * Read JSON file safely
     */
    public function read($filename) {
        $filepath = $this->dataDir . $filename . '.json';
        
        if (!file_exists($filepath)) {
            return [];
        }
        
        $content = file_get_contents($filepath);
        $data = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }
        
        return is_array($data) ? $data : [];
    }
    
    /**
     * Write JSON file safely with atomic operations
     */
    public function write($filename, $data) {
        $filepath = $this->dataDir . $filename . '.json';
        $tempfile = $filepath . '.tmp';
        
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }
        
        if (file_put_contents($tempfile, $json, LOCK_EX) === false) {
            return false;
        }
        
        return rename($tempfile, $filepath);
    }
    
    /**
     * Update JSON data by adding or replacing items
     */
    public function update($filename, $data) {
        return $this->write($filename, $data);
    }
    
    /**
     * Get item by ID
     */
    public function getItem($filename, $id) {
        $items = $this->read($filename);
        
        foreach ($items as $item) {
            if (isset($item['id']) && $item['id'] == $id) {
                return $item;
            }
        }
        
        return null;
    }
    
    /**
     * Delete item by ID
     */
    public function deleteItem($filename, $id) {
        $items = $this->read($filename);
        $filtered = array_filter($items, function($item) use ($id) {
            return !(isset($item['id']) && $item['id'] == $id);
        });
        
        return $this->write($filename, array_values($filtered));
    }
    
    /**
     * Add item with auto-increment ID
     */
    public function addItem($filename, $data) {
        $items = $this->read($filename);
        
        $maxId = 0;
        foreach ($items as $item) {
            if (isset($item['id']) && $item['id'] > $maxId) {
                $maxId = $item['id'];
            }
        }
        
        $data['id'] = $maxId + 1;
        $items[] = $data;
        
        return $this->write($filename, $items) ? $data['id'] : false;
    }
}

$json = new JSONEngine();
?>