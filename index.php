<?php
/**
 * OMGPlugins Homepage Placeholder
 * Frontend will be built in Phase 2
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMGPlugins - CMS Admin</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
        }
        
        .container {
            max-width: 500px;
            text-align: center;
        }
        
        .logo {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        p {
            color: #6b7fa3;
            margin-bottom: 2rem;
            font-size: 1.05rem;
            line-height: 1.6;
        }
        
        .button {
            display: inline-block;
            padding: 0.9rem 2rem;
            background: #00f0a0;
            color: #000;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 24px rgba(0, 240, 160, 0.3);
        }
        
        .status {
            margin-top: 2rem;
            padding: 1.5rem;
            background: #111827;
            border: 1px solid #1f2d45;
            border-radius: 12px;
        }
        
        .status h3 {
            color: #00f0a0;
            margin-bottom: 0.5rem;
        }
        
        .status p {
            color: #6b7fa3;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">⚙️</div>
        <h1>OMGPlugins CMS</h1>
        <p>Phase 1: Auth System & Admin Dashboard</p>
        <p>A production-ready self-hosted CMS with JSON storage</p>
        
        <a href="/admin/login.php" class="button">Go to Admin Panel</a>
        
        <div class="status">
            <h3>✓ Phase 1 Complete</h3>
            <p>Authentication system, security framework, and basic app management are ready for use.</p>
        </div>
    </div>
</body>
</html>