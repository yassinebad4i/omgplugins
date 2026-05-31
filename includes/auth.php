<?php
/**
 * Authentication System
 * Login, logout, session management
 */

session_start();

require_once __DIR__ . '/security.php';
require_once __DIR__ . '/json.php';

class Auth {
    private $json;
    private $adminFile = 'admin';
    
    public function __construct() {
        global $json;
        $this->json = $json;
        
        // Initialize admin file if not exists
        if (empty($this->json->read($this->adminFile))) {
            $this->initializeAdmin();
        }
    }
    
    /**
     * Initialize default admin user
     */
    private function initializeAdmin() {
        $adminData = [
            [
                'id' => 1,
                'username' => 'admin',
                'email' => 'admin@omgplugins.local',
                'password' => password_hash('password123', PASSWORD_BCRYPT),
                'created_at' => date('Y-m-d H:i:s'),
                'last_login' => null
            ]
        ];
        $this->json->write($this->adminFile, $adminData);
    }
    
    /**
     * Login user
     */
    public function login($username, $password, $remember = false) {
        $username = Security::sanitize($username);
        $admins = $this->json->read($this->adminFile);
        
        foreach ($admins as $admin) {
            if ($admin['username'] === $username && password_verify($password, $admin['password'])) {
                Security::setSecureSession($admin['id']);
                $_SESSION['username'] = $admin['username'];
                $_SESSION['email'] = $admin['email'];
                
                // Update last login
                $admin['last_login'] = date('Y-m-d H:i:s');
                $admins = array_map(function($a) use ($admin) {
                    return $a['id'] === $admin['id'] ? $admin : $a;
                }, $admins);
                $this->json->write($this->adminFile, $admins);
                
                // Remember me
                if ($remember) {
                    setcookie('remember_token', bin2hex(random_bytes(32)), time() + (86400 * 30), '/', '', false, true);
                }
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Logout user
     */
    public function logout() {
        session_destroy();
        setcookie('remember_token', '', time() - 3600, '/');
        header('Location: /admin/login.php?logged_out=1');
        exit;
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        if (!Security::validateSession()) {
            return false;
        }
        
        if (!Security::checkSessionTimeout()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get current user
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email']
        ];
    }
    
    /**
     * Require login (redirect if not logged in)
     */
    public function require() {
        if (!$this->isLoggedIn()) {
            header('Location: /admin/login.php');
            exit;
        }
    }
}

$auth = new Auth();

?>