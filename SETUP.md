# OMGPlugins CMS - Quick Setup

## What You Get

✅ **Complete Admin System**
- Authentication with session management
- Dashboard with statistics
- App/Game management (CRUD)
- Category management
- Media uploads with validation
- Global search functionality
- Analytics and download tracking

✅ **Production-Ready Frontend**
- Homepage with featured content
- App/Game listing pages (with pagination)
- Detail pages with SEO tags
- Real-time search API
- Download tracking
- Responsive design

✅ **Security Features**
- CSRF protection
- XSS prevention
- Input sanitization
- Session hijacking prevention
- File upload validation
- Secure password hashing (bcrypt)

## Files Included

### Core System
- `config/init.php` - Application initialization
- `includes/auth.php` - Authentication system
- `includes/json.php` - JSON database engine
- `includes/security.php` - Security utilities
- `includes/frontend.php` - Frontend helpers
- `includes/router.php` - URL routing

### Admin Pages
- `admin/dashboard.php` - Main dashboard
- `admin/login.php` - Login page
- `admin/apps.php` - Manage apps
- `admin/add-app.php` - Add new app
- `admin/edit-app.php` - Edit app
- `admin/games.php` - Manage games
- `admin/add-game.php` - Add new game
- `admin/edit-game.php` - Edit game
- `admin/categories.php` - Manage categories
- `admin/media.php` - Upload media
- `admin/analytics.php` - View statistics
- `admin/search.php` - Search content

### Frontend Pages
- `index.php` - Homepage
- `app/index.php` - Apps listing
- `app/detail.php` - App detail page
- `game/index.php` - Games listing
- `game/detail.php` - Game detail page
- `search-api.php` - Search API endpoint
- `analytics-api.php` - Analytics API

### Configuration
- `.htaccess` - URL routing (Apache)
- `.gitignore` - Git ignore patterns

### Documentation
- `README.md` - Main documentation
- `INSTALLATION.md` - Installation guide
- `FRONTEND_README.md` - Frontend documentation
- `SETUP.md` - This file

## Quick Start

### 1. Upload to Server
```bash
# Upload all files to your web server
scp -r omgplugins/ user@server:/var/www/html/
```

### 2. Set Permissions
```bash
cd /var/www/html/omgplugins
chmod 755 data uploads uploads/images uploads/screenshots
```

### 3. Access Admin
```
https://yourdomain.com/admin/login.php
Username: admin
Password: password123
```

### 4. Change Password
- Login to admin
- Update your password immediately

## File Checklist

### Root Files
- ✅ index.php (Homepage)
- ✅ search-api.php (Search endpoint)
- ✅ analytics-api.php (Analytics endpoint)
- ✅ .htaccess (URL routing)
- ✅ README.md
- ✅ INSTALLATION.md
- ✅ FRONTEND_README.md
- ✅ SETUP.md
- ✅ .gitignore

### Directories
- ✅ admin/ (14 files)
- ✅ includes/ (5 files)
- ✅ app/ (2 files)
- ✅ game/ (2 files)
- ✅ config/ (init.php)
- ✅ data/ (auto-created)
- ✅ uploads/ (auto-created)

## Verify Installation

### Check Directories Exist
```bash
ls -la admin/
ls -la includes/
ls -la app/
ls -la game/
```

### Test Admin Panel
1. Visit `/admin/login.php`
2. Login with admin/password123
3. Should see dashboard with statistics

### Test Frontend
1. Visit `/` (homepage)
2. Visit `/app/` (apps listing)
3. Visit `/game/` (games listing)

### Test Search
1. Visit `/search-api.php?q=test`
2. Should return JSON response

## Common Issues

### 404 Errors
- Check .htaccess is enabled
- Verify Apache mod_rewrite is enabled
- Use query params: `/app/detail.php?slug=name`

### Upload Fails
- Check uploads directory permissions
- Verify disk space
- Check PHP upload_max_filesize

### Login Issues
- Clear cookies
- Check data/admin.json exists
- Verify PHP sessions work

## Support Files

- `INSTALLATION.md` - Detailed installation steps
- `FRONTEND_README.md` - Frontend features and setup
- `README.md` - General documentation and API reference
- Code comments in all files

## You're Ready!

Your OMGPlugins CMS is fully installed and ready to use. Start by:

1. Logging into admin panel
2. Creating categories
3. Adding apps and games
4. Uploading media
5. Publishing content

Enjoy your CMS! 🚀
