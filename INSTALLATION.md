# OMGPlugins CMS - Installation Guide

## System Requirements

- **PHP**: 8.0 or higher
- **Web Server**: Apache (with mod_rewrite) or Nginx
- **File System**: Write permissions on:
  - `/data/` directory
  - `/uploads/` directory
  - `/uploads/images/` directory
  - `/uploads/screenshots/` directory

## Step-by-Step Installation

### 1. Upload Files

Upload all project files to your web server's public_html or www directory.

### 2. Create Required Directories

```bash
mkdir -p data
mkdir -p uploads/images
mkdir -p uploads/screenshots
chmod 755 data uploads uploads/images uploads/screenshots
```

### 3. Configure Apache (if using Apache)

If you haven't already, copy `.htaccess.example` to `.htaccess`:

```bash
cp .htaccess.example .htaccess
```

Enable mod_rewrite:

```bash
a2enmod rewrite
sudo systemctl restart apache2
```

### 4. Access the Application

1. Open your browser and navigate to:
   ```
   https://yourdomain.com/admin/login.php
   ```

2. Login with default credentials:
   - **Username**: `admin`
   - **Password**: `password123`

3. Change password immediately in admin dashboard

### 5. Configure Settings

1. Go to Admin Dashboard
2. Update site name, description, and other settings
3. Upload your site logo and favicon

## First Run Setup

### Create Categories

1. Navigate to **Categories** in admin menu
2. Add categories for your apps and games
3. Categories help organize content

### Add First App/Game

1. Click **Add App** or **Add Game**
2. Fill in all required fields:
   - Title
   - Version
   - Category
   - Download URL
   - Description (optional)
3. Upload image (optional)
4. Set status to **Published**
5. Click Save

## Security Recommendations

1. **Change Default Password**: Login to admin panel and change the default password
2. **Backup Data**: Regularly backup the `/data/` directory
3. **File Permissions**: Ensure proper permissions (755 for directories, 644 for files)
4. **HTTPS**: Use HTTPS for your site (recommended)
5. **Regular Updates**: Keep PHP and all dependencies up to date

## Troubleshooting

### Permission Denied Errors

```bash
chmod 755 data uploads uploads/images uploads/screenshots
```

### 404 Errors on App/Game Pages

1. Check if `.htaccess` is properly configured
2. Verify mod_rewrite is enabled
3. Use query parameter URLs: `/app/detail.php?slug=app-name`

### Images Not Uploading

1. Check uploads directory permissions (755)
2. Check PHP upload_max_filesize setting
3. Verify /uploads/images and /uploads/screenshots exist

### Login Not Working

1. Clear browser cookies and cache
2. Verify `/data/admin.json` file exists
3. Check PHP session.save_path is writable

## Default Directory Structure Created

```
data/
├── admin.json          # Admin users
├── apps.json           # Apps list
├── categories.json     # Categories
├── downloads.json      # Download tracking
├── games.json          # Games list
└── settings.json       # Site settings

uploads/
├── images/             # App/game images
└── screenshots/        # Screenshots
```

## Next Steps

1. Customize site settings
2. Add categories
3. Upload content (apps/games)
4. Configure frontend appearance
5. Backup regularly

## Support

For issues or questions, refer to:
- README.md - General documentation
- FRONTEND_README.md - Frontend features
- Code comments in included files
