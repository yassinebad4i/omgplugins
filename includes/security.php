<?php
/**
 * Security Functions
 * CSRF protection, input sanitization, XSS prevention
 */

class Security {
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
     * Set secure session
     */
    public static function setSecureSession($user_id) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['session_token'] = hash('sha256', $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
        $_SESSION['created_at'] = time();
        $_SESSION['last_activity'] = time();
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
}

?>