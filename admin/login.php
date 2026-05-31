<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/security.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        if (empty($username) || empty($password)) {
            $error = 'Please enter username and password.';
        } else {
            if ($auth->login($username, $password, $remember)) {
                header('Location: /admin/dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        }
    }
}

if (isset($_GET['logged_out'])) {
    $success = 'You have been logged out successfully.';
}

$csrf_token = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - OMGPlugins CMS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DM Sans', sans-serif;
            background: #0a0d14;
            color: #e8ecf5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
        }
        
        .login-card {
            background: #111827;
            border: 1px solid #1f2d45;
            border-radius: 20px;
            padding: 2.5rem 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #00f0a0;
            box-shadow: 0 0 12px #00f0a0;
            margin-right: 0.5rem;
        }
        
        .login-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: #6b7fa3;
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: #e8ecf5;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #1f2d45;
            border-radius: 12px;
            background: #0a0d14;
            color: #e8ecf5;
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #00f0a0;
            box-shadow: 0 0 16px rgba(0, 240, 160, 0.15);
        }
        
        input::placeholder {
            color: #6b7fa3;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        
        input[type="checkbox"] {
            cursor: pointer;
            width: 18px;
            height: 18px;
        }
        
        .remember-me label {
            margin: 0;
            cursor: pointer;
            color: #6b7fa3;
            font-weight: 500;
        }
        
        .btn-login {
            width: 100%;
            padding: 0.9rem;
            background: #00f0a0;
            color: #000;
            border: none;
            border-radius: 14px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 0 24px rgba(0, 240, 160, 0.3);
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 40px rgba(0, 240, 160, 0.5);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        
        .alert-error {
            background: rgba(255, 85, 85, 0.1);
            border: 1px solid rgba(255, 85, 85, 0.3);
            color: #ff8585;
        }
        
        .alert-success {
            background: rgba(0, 240, 160, 0.1);
            border: 1px solid rgba(0, 240, 160, 0.3);
            color: #00f0a0;
        }
        
        .info-text {
            text-align: center;
            font-size: 0.85rem;
            color: #6b7fa3;
            margin-top: 1.5rem;
        }
        
        .info-text strong {
            color: #e8ecf5;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1><span class="logo-dot"></span>OMGPlugins</h1>
                <p>Admin Panel</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo Security::escape($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo Security::escape($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo Security::escape($csrf_token); ?>">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" required>
                </div>
                
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember" value="1">
                    <label for="remember">Remember me for 30 days</label>
                </div>
                
                <button type="submit" class="btn-login">Sign In</button>
            </form>
            
            <div class="info-text">
                <strong>Demo Credentials:</strong><br>
                Username: admin<br>
                Password: password123
            </div>
        </div>
    </div>
</body>
</html>