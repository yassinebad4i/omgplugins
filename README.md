# OMGPlugins CMS

A complete self-hosted CMS for managing modded apps and games with a beautiful admin dashboard.

## 🎯 Features

### Phase 1: Auth & Core Infrastructure ✓
- Authentication system with JSON storage
- Security framework (CSRF, XSS, input sanitization, session hijacking prevention)
- Admin dashboard with statistics
- Basic apps management (CRUD operations)
- JSON file engine with atomic operations

### Phase 2: Complete Content Management ✓
- **Apps Management**: Full CRUD with edit functionality
- **Games Management**: Complete game management system
- **Categories**: Dynamic category management for apps and games
- **Media Manager**: Drag-and-drop file uploads with image preview
- **Global Search**: Search across all apps and games
- **Enhanced Security**: File upload validation, MIME type checking, size limits
- **SEO Fields**: Meta titles and descriptions for all content
- **Status Management**: Draft/Published states with automatic timestamps
- **Responsive Design**: Mobile-friendly interface

## 📋 Installation

1. Upload files to your web server
2. Create `data/` directory with write permissions (chmod 755)
3. Create `uploads/images/` and `uploads/screenshots/` directories with write permissions
4. Access `/admin/login.php`
5. Default credentials: `admin` / `password123`

## 🔧 Requirements

- PHP 8.0+
- Write permissions on `data/` and `uploads/` directories
- No database required (JSON-based storage)

## 📁 Directory Structure

```
omgplugins/
├── admin/
│   ├── dashboard.php          # Main admin dashboard
│   ├── login.php              # Authentication page
│   ├── logout.php             # Logout handler
│   ├── apps.php               # Apps list & management
│   ├── add-app.php            # Add new app
│   ├── edit-app.php           # Edit existing app
│   ├── games.php              # Games list & management
│   ├── add-game.php           # Add new game
│   ├── edit-game.php          # Edit existing game
│   ├── categories.php         # Category management
│   ├── media.php              # Media manager with uploads
│   └── search.php             # Global search
├── includes/
│   ├── auth.php               # Authentication class
│   ├── security.php           # Security utilities
│   └── json.php               # JSON database engine
├── data/                       # JSON data storage
│   ├── admin.json
│   ├── apps.json
│   ├── games.json
│   ├── categories.json
│   └── settings.json
├── uploads/
│   ├── images/                # Uploaded app/game images
│   └── screenshots/           # Screenshots storage
├── index.php                  # Homepage
└── README.md                  # This file
```

## 🔐 Security Features

- **CSRF Protection**: Token-based CSRF protection on all forms
- **XSS Prevention**: Input sanitization and HTML escaping
- **Session Security**: Session token validation and timeout (30 minutes)
- **Session Hijacking Prevention**: User agent and IP-based validation
- **File Upload Validation**: 
  - File type and MIME type checking
  - Maximum file size (5MB)
  - Secure filename generation (random hex)
- **Password Security**: bcrypt hashing with PASSWORD_BCRYPT
- **Input Validation**: Comprehensive input sanitization

## 📝 Usage

### Adding an App
1. Go to Apps Management → Add New App
2. Fill in title, version, category, and download URL
3. Add optional features, description, and SEO metadata
4. Choose status (Draft/Published)
5. Submit to save

### Managing Games
Same process as apps, with dedicated Games Management section.

### Uploading Media
1. Go to Media Manager
2. Click to upload or drag-and-drop images
3. Copy image URL for use in app/game descriptions

### Searching Content
1. Use the search box in the header (minimum 3 characters)
2. Results show both apps and games
3. Click "Edit" to modify content

## 🎨 Admin Dashboard Features

- Real-time statistics (total apps, games, categories)
- Quick action buttons for common tasks
- Recent apps and games tables
- Search functionality
- Responsive sidebar navigation
- Modern dark theme UI

## 🔄 API Endpoints (JSON Storage)

The CMS uses JSON files for storage. Key methods in `JSONEngine`:

```php
$json->read($filename)              // Read all items
$json->write($filename, $data)      // Write data
$json->getItem($filename, $id)      // Get by ID
$json->addItem($filename, $data)    // Add new item
$json->updateItem($filename, $id, $data)  // Update item
$json->deleteItem($filename, $id)   // Delete item
$json->search($filename, $field, $query)  // Search
$json->filter($filename, $field, $value)  // Filter
$json->getPublished($filename)      // Get published items
$json->slugExists($filename, $slug) // Check slug
```

## 🚀 Future Enhancements

- User management with multiple admin accounts
- Content categories with custom fields
- Advanced search filters and sorting
- Scheduled content publication
- Analytics and statistics
- API for external integrations
- Backup and restore functionality
- Content versioning and history

## 📄 License

MIT License - Feel free to use for personal or commercial projects

## 💬 Support

For issues or feature requests, please create an issue in the repository.
