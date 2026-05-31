# OMGPlugins CMS

A complete self-hosted CMS for managing modded apps and games.

## Phase 1: Auth & Core Infrastructure
- Authentication system with JSON storage
- Security framework (CSRF, XSS, input sanitization)
- Admin dashboard
- Basic apps management (CRUD)
- JSON file engine

## Installation
1. Upload files to your web server
2. Create `data/` directory with write permissions (chmod 755)
3. Access `/admin/login.php`
4. Default credentials: admin / password123

## Requirements
- PHP 8.0+
- Write permissions on `data/` directory
