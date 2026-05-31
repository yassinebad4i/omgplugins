<?php
/**
 * Security Functions - ENHANCED for Phase 2
 * CSRF protection, input sanitization, XSS prevention, file upload validation
 */

class Security {
    const MAX_FILE_SIZE = 5242880;
    const ALLOWED_FILE_TYPES = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    const UPLOAD_DIR_IMAGES = '/uploads/images/';
    const UPLOAD_DIR_SCREENSHOTS = '/uploads/screenshots/';
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Sanitize input - remove HTML tags and encode
     */
    public static function sanitize($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Escape for HTML output (XSS protection)
     */
    public static function escape($input) {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Generate slug from string
     */
    public static function generateSlug($string) {
        $string = strtolower(trim($string));
        $string = preg_replace('/[^a-z0-9]+/', '-', $string);
        return trim($string, '-');
    }
    
    /**
     * Prevent session hijacking
     */
    public static function validateSession() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_token'])) {
            return false;
        }
        
        $expected_token = hash('sha256', $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
        return hash_equals($_SESSION['session_token'], $expected_token);
    }
    
    /**
     * Set secure session with regeneration
     */
    public static function setSecureSession($user_id) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['session_token'] = hash('sha256', $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
        $_SESSION['created_at'] = time();
        $_SESSION['last_activity'] = time();
        session_regenerate_id(true);
    }
    
    /**
     * Check session timeout (30 minutes)
     */
    public static function checkSessionTimeout($timeout = 1800) {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
            session_destroy();
            return false;
        }
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    /**
     * Validate file upload
     */
    public static function validateFileUpload($file, $type = 'image') {
        if (!isset($file['tmp_name']) || !isset($file['name']) || !isset($file['error'])) {
            return ['valid' => false, 'error' => 'Invalid file upload'];
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => 'Upload error: ' . $file['error']];
        }
        
        if ($file['size'] > self::MAX_FILE_SIZE) {
            return ['valid' => false, 'error' => 'File size exceeds 5MB limit'];
        }
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_FILE_TYPES)) {
            return ['valid' => false, 'error' => 'File type not allowed. Allowed: ' . implode(', ', self::ALLOWED_FILE_TYPES)];
        }
        
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (!in_array($mime, $allowed_mimes)) {
                return ['valid' => false, 'error' => 'Invalid file MIME type'];
            }
        }
        
        return ['valid' => true, 'ext' => $ext, 'name' => $file['name']];
    }
    
    /**
     * Save uploaded file
     */
    public static function saveUploadedFile($file, $type = 'image') {
        $validation = self::validateFileUpload($file, $type);
        if (!$validation['valid']) {
            return ['success' => false, 'error' => $validation['error']];
        }
        
        $uploadDir = $type === 'screenshot' ? __DIR__ . '/..' . self::UPLOAD_DIR_SCREENSHOTS : __DIR__ . '/..' . self::UPLOAD_DIR_IMAGES;
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filename = bin2hex(random_bytes(16)) . '.' . $validation['ext'];
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'filename' => $filename,
                'url' => ($type === 'screenshot' ? self::UPLOAD_DIR_SCREENSHOTS : self::UPLOAD_DIR_IMAGES) . $filename,
                'path' => $filepath
            ];
        }
        
        return ['success' => false, 'error' => 'Failed to save file'];
    }
    
    /**
     * Delete uploaded file
     */
    public static function deleteUploadedFile($filename, $type = 'image') {
        $uploadDir = $type === 'screenshot' ? __DIR__ . '/..' . self::UPLOAD_DIR_SCREENSHOTS : __DIR__ . '/..' . self::UPLOAD_DIR_IMAGES;
        $filepath = $uploadDir . basename($filename);
        
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        
        return false;
    }
}

?>