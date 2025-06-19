# Production Routing Configuration Guide

## Overview

This document explains the production routing configuration for the Event Management System deployed at `accounts.dcism.org`. This setup handles the subdirectory deployment structure where the application runs in the `/accounts/` path.

## Problem Statement

The original application was designed to run from the document root, but the production server has the following structure:
- **Domain**: `accounts.dcism.org`
- **Document Root**: `/data/web/sub/accounts` (points to parent directory)
- **Application Directory**: `/data/web/sub/accounts/accounts/` (the actual app)

This caused routing issues where URLs were not generating correctly for the subdirectory structure.

## Directory Structure

```
/data/users/s21102134/accounts.dcism.org/
├── index.html                          # Root redirect file
└── accounts/                          # Main application directory
    ├── index.php                      # Application entry point (moved from public/)
    ├── .htaccess                      # URL rewriting rules
    ├── app/                           # MVC framework
    │   ├── config/
    │   │   └── config.php             # Base path detection & URL helper
    │   ├── controllers/
    │   │   ├── AuthController.php     # Fixed redirect paths
    │   │   └── HomeController.php     # Simplified base path usage
    │   ├── core/
    │   │   ├── App.php                # Updated controller loading paths
    │   │   └── Controller.php         # Base path detection & view loading
    │   ├── models/
    │   │   └── Event.php              # Fixed FileStorage.php path
    │   └── views/                     # Updated to use url() helper
    ├── public/                        # Legacy directory (old entry point)
    └── dist/                          # Assets (copied to root for access)
```

## URL Flow & Routing Logic

### 1. Root Domain Access
```
https://accounts.dcism.org/
    ↓ (serves index.html with meta refresh)
https://accounts.dcism.org/accounts/
    ↓ (MVC framework processes via .htaccess)
https://accounts.dcism.org/accounts/auth/login
    ↓ (final destination - login page)
```

### 2. Base Path Detection Logic

The application automatically detects the correct base path using this logic:

```php
function getBasePath() {
    if (isset($_SERVER['SCRIPT_NAME'])) {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $basePath = dirname($scriptName);
        
        // If we're in the public directory, go up one level
        if (basename($basePath) === 'public') {
            $basePath = dirname($basePath);
        }
        
        // Special case: if we're running from /accounts/ subdirectory
        if (strpos($scriptName, '/accounts/') !== false) {
            return '/accounts';
        }
        
        // Normalize path for root installations
        if ($basePath === '/' || $basePath === '\\') {
            return '';
        }
        
        return $basePath;
    }
    return '';
}
```

### 3. URL Helper Function

All views use the `url()` helper function to generate correct URLs:

```php
// In views:
<a href="<?= url('/auth/login') ?>">Login</a>
// Generates: /accounts/auth/login

<a href="<?= url('/dashboard') ?>">Dashboard</a>  
// Generates: /accounts/dashboard
```

## Key Configuration Files

### 1. `/index.html` (Root Redirect)
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting...</title>
    <meta http-equiv="refresh" content="0;url=/accounts/">
</head>
<body>
    <h1>Redirecting...</h1>
    <p>If you are not redirected automatically, follow the <a href="/accounts/">link to the accounts system</a>.</p>
</body>
</html>
```

### 2. `/accounts/.htaccess` (URL Rewriting)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.+)$ index.php?url=$1 [QSA,L]
```

### 3. `/accounts/index.php` (Application Entry Point)
```php
<?php
// Start session
session_start();

// Load configuration
require_once 'app/config/config.php';

// Load core classes
require_once 'app/core/Database.php';
require_once 'app/core/Model.php';
require_once 'app/core/Controller.php';
require_once 'app/core/App.php';

// Start the application
$app = new App();
```

## Changes Made for Production

### 1. File Movement
- **Moved** `index.php` from `accounts/public/` to `accounts/` root
- **Copied** essential assets (`dist/`, `profilePictures/`) to root level

### 2. Path Updates
- **Fixed relative paths** in all core files (from `../app/` to `app/`)
- **Updated** `Event.php` model to use correct FileStorage.php path
- **Fixed** all view files to use `url()` helper instead of hardcoded paths

### 3. Base Path Detection
- **Enhanced** base path detection to handle `/accounts/` subdirectory
- **Updated** all controllers to use parent class `getBasePath()` method
- **Added** special case handling for production environment

### 4. URL Generation
- **Replaced** all hardcoded URLs with `url()` helper function
- **Fixed** nested PHP tag syntax errors in views
- **Ensured** consistent URL generation across all components

## Testing Production Routes

### Command Line Testing
```bash
# Test root redirect
curl -I https://accounts.dcism.org/
# Should return: 200 OK with meta refresh to /accounts/

# Test application entry
curl -I https://accounts.dcism.org/accounts/
# Should return: 302 redirect to /accounts/auth/login

# Test login page
curl -I https://accounts.dcism.org/accounts/auth/login
# Should return: 200 OK (login page loads)
```

### Expected Behavior
1. **Root Access**: `accounts.dcism.org` → automatically redirects to login
2. **Direct Access**: `accounts.dcism.org/accounts/auth/login` → loads login page
3. **Navigation**: All internal links generate correct `/accounts/` prefixed URLs

## Common Issues & Solutions

### Issue 1: "File not found" errors
**Cause**: Incorrect relative paths after moving `index.php`
**Solution**: Update all `require_once '../app/...'` to `require_once 'app/...'`

### Issue 2: "Access level must be public" errors
**Cause**: Method visibility conflicts when overriding parent class methods
**Solution**: Remove duplicate methods from child classes or ensure correct visibility

### Issue 3: URLs missing `/accounts/` prefix
**Cause**: Base path detection not working correctly
**Solution**: Verify the special case handling for `/accounts/` in `getBasePath()`

### Issue 4: Infinite redirect loops
**Cause**: Incorrect redirect logic in `index.html` or MVC framework
**Solution**: Ensure `index.html` redirects to `/accounts/` and MVC redirects to `/accounts/auth/login`

### Issue 5: Assets not loading (CSS, images)
**Cause**: Assets still in `public/` directory but accessed from root
**Solution**: Copy assets to application root directory

## Deployment Checklist

When deploying this configuration:

- [ ] Verify document root points to parent directory of `accounts/`
- [ ] Ensure `index.html` exists at document root with correct redirect
- [ ] Confirm `.htaccess` file has proper rewrite rules
- [ ] Test base path detection returns `/accounts` in production
- [ ] Verify all assets are accessible from root level
- [ ] Test complete redirect flow from root to login page
- [ ] Confirm all internal navigation uses `url()` helper

## Environment Differences

### Development (Local)
- **Document Root**: Points directly to application directory
- **Base Path**: Usually empty (`''`) 
- **URLs**: `/auth/login`, `/dashboard`

### Production (accounts.dcism.org)
- **Document Root**: Points to parent directory
- **Base Path**: `/accounts`
- **URLs**: `/accounts/auth/login`, `/accounts/dashboard`

## Branch Information

This configuration is saved in the `production` branch:
- **Branch**: `production`
- **Commit**: `542cdeb`
- **Remote**: `origin/production`

To apply this configuration:
```bash
git checkout production
```

To return to development:
```bash
git checkout main
```

---

**Last Updated**: June 19, 2025  
**Configuration Version**: Production v1.0  
**Tested Environment**: accounts.dcism.org 