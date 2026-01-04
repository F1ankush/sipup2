# Hostinger Deployment - Complete Fix Guide

## ✅ Issues Fixed for Hostinger Compatibility

### 1. **Session Management** ✓
- **Issue**: Session not started before output
- **Fix**: Added `session_start()` check in `includes/config.php`
- **Status**: FIXED

### 2. **Database Credentials** ✓
- **Issue**: Hardcoded localhost credentials for XAMPP
- **Fix**: Updated `includes/config_manager.php` with Hostinger credentials
- **Old**: `u110596290_b22bsystem` (typo)
- **New**: `u110596290_b2bsystem` (corrected)
- **Status**: FIXED

### 3. **Hardcoded Paths** ✓
- **Issue**: `/top1/` hardcoded in database redirect
- **Fix**: Changed to dynamic path detection in `includes/db.php`
- **Before**: `header('Location: /top1/setup_wizard.php')`
- **After**: Uses dynamic `$baseUrl . $setupPath`
- **Status**: FIXED

### 4. **ini_set Restrictions** ✓
- **Issue**: Hostinger restricts some ini_set commands
- **Fix**: Added `@` error suppression operator
- **Status**: FIXED

### 5. **Error Reporting** ✓
- **Issue**: Could expose server paths in errors
- **Fix**: Errors logged to file, display_errors = 0
- **Status**: FIXED

---

## 📋 Pre-Deployment Checklist

### Step 1: Verify Database Credentials
- [ ] Log in to Hostinger cPanel
- [ ] Go to **Databases** section
- [ ] Find your database name: `u110596290_b2bsystem`
- [ ] Find your username: `u110596290_b2bsystem`
- [ ] Find your password (copy it)
- [ ] Verify database exists and has correct name

### Step 2: Upload Files to Hostinger
- [ ] Connect via FTP or File Manager
- [ ] Upload all files to **public_html** or **www** folder
- [ ] Verify folder structure:
  ```
  public_html/
  ├── index.php
  ├── includes/
  ├── pages/
  ├── admin/
  ├── assets/
  ├── uploads/
  ├── setup_wizard.php
  └── config_api.php
  ```

### Step 3: Database Configuration
1. **Option A - Automatic (Recommended)**
   - Visit: `https://yourdomain.com/setup_wizard.php`
   - Enter database credentials from cPanel
   - Click "Test Connection"
   - Click "Save & Continue"

2. **Option B - Manual**
   - Create `.db_config` file in root directory
   - Content:
   ```json
   {
     "host": "localhost",
     "user": "u110596290_b2bsystem",
     "password": "YOUR_PASSWORD_HERE",
     "dbname": "u110596290_b2bsystem",
     "saved_at": "2025-01-04"
   }
   ```
   - Upload to server

### Step 4: File Permissions
- [ ] Set `includes/` to **755** (readable/executable)
- [ ] Set `.db_config` to **600** (read/write only by owner)
- [ ] Set `uploads/` to **755**
- [ ] Set `cache/` to **755** (create if needed)
- [ ] Set `logs/` to **755** (create if needed)

### Step 5: Create Required Directories
```bash
# Create these directories via FTP/cPanel
mkdir -p public_html/cache
mkdir -p public_html/logs
mkdir -p public_html/uploads
```

### Step 6: Verify Configuration
- [ ] Visit: `https://yourdomain.com/index.php`
- [ ] Should load home page without errors
- [ ] Check error log: `error_log.txt` (should be empty or minimal)

---

## 🔧 Configuration Details

### Database Connection Flow
1. **Load `includes/config.php`**
   - Starts session
   - Loads `config_manager.php`
   - Gets database credentials

2. **Get Credentials** (Priority Order)
   1. Check `.db_config` file (if exists)
   2. Detect Hostinger environment
   3. Return appropriate credentials

3. **Connect to Database**
   - Try configured credentials
   - If fails, try auto-detect
   - If both fail, redirect to setup wizard

### Hostinger Detection
The system automatically detects Hostinger:
```php
if (strpos($host, 'localhost') !== false || 
    strpos($host, '127.0.0.1') !== false) {
    return 'localhost';
} else {
    return 'hostinger';
}
```

### Session Configuration
- Name: `SIPUP_SESSIONID`
- Timeout: 30 minutes
- HTTPOnly: Yes (more secure)
- SameSite: Lax (CSRF protection)

---

## 🚀 Deployment Steps

### Step 1: Upload via FTP
```
1. Connect to Hostinger FTP (get credentials from cPanel)
2. Navigate to public_html
3. Upload all files
4. Set file permissions (see above)
```

### Step 2: Create .htaccess for Pretty URLs (Optional)
Create `.htaccess` in root:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
</IfModule>
```

### Step 3: Database Setup
Run the configuration wizard:
```
https://yourdomain.com/setup_wizard.php
```

### Step 4: Import Database Schema
If you have existing data:
1. Via cPanel phpMyAdmin:
   - Select database
   - Go to "Import" tab
   - Upload `database_schema.sql`
   - Click Import

---

## ✅ Testing Checklist

### Test Basic Functionality
- [ ] Visit homepage: `https://yourdomain.com/`
- [ ] Navigate to About: `https://yourdomain.com/pages/about.php`
- [ ] Navigate to Products: `https://yourdomain.com/pages/products.php`
- [ ] Navigate to Apply: `https://yourdomain.com/pages/apply.php`

### Test User Features
- [ ] Apply for account - form submits
- [ ] Verify email received (check spam folder)
- [ ] Login page loads: `https://yourdomain.com/pages/login.php`

### Test Admin Panel
- [ ] Admin login: `https://yourdomain.com/admin/login.php`
- [ ] Admin dashboard loads
- [ ] View applications
- [ ] Manage products

### Test Error Handling
- [ ] Check error_log.txt (no critical errors)
- [ ] Verify 404 pages work
- [ ] Test database connection failure handling

---

## 🐛 Troubleshooting

### Problem: "Database Connection Required"
**Solution:**
1. Check database credentials in cPanel
2. Verify `.db_config` file exists and has correct permissions
3. Visit `setup_wizard.php` to test and save credentials
4. Check error_log.txt for specific error

### Problem: "Cannot redeclare function"
**Solution:**
- This has been fixed in the latest version
- Verify you're using the latest `includes/functions.php`

### Problem: Undefined function `renderUserNavbar()`
**Solution:**
- This has been fixed
- Clear browser cache
- Verify `includes/functions.php` is uploaded

### Problem: Images not loading
**Solution:**
1. Check image paths in code (use relative paths)
2. Verify `assets/images/` folder exists
3. Verify image files are uploaded
4. Check file permissions (644 for images)

### Problem: "Too many connections"
**Solution:**
1. Check if database pool size is too large
2. Edit `includes/config.php`:
   ```php
   define('DB_POOL_SIZE', 10);  // Reduce from 50
   define('DB_MAX_CONNECTIONS', 20);  // Reduce from 100
   ```

### Problem: CSS/JS not loading
**Solution:**
1. Check relative paths in HTML
2. Verify assets folder is in correct location
3. Check browser console for 404 errors
4. Verify file permissions (644 for CSS/JS)

---

## 📝 Important Files

| File | Purpose | Permissions |
|------|---------|-------------|
| `.db_config` | Database credentials | 600 |
| `includes/config.php` | Configuration | 644 |
| `includes/db.php` | Database class | 644 |
| `error_log.txt` | Error log | 644 |
| `cache/` | Cache directory | 755 |
| `uploads/` | User uploads | 755 |
| `logs/` | Application logs | 755 |

---

## 🔐 Security Recommendations

1. **HTTPS**: Hostinger provides free SSL certificate - enable it
2. **htaccess**: Protect sensitive files:
   ```apache
   <FilesMatch "\.(env|db_config|sql)$">
       Order allow,deny
       Deny from all
   </FilesMatch>
   ```

3. **Database User Permissions**: Limit to necessary databases
4. **File Permissions**: 
   - Folders: 755
   - Files: 644
   - Config: 600

5. **Regular Backups**: Set up automatic backups in cPanel

---

## 📞 Support

If issues persist:
1. Check `error_log.txt` for specific error messages
2. Verify all files are uploaded
3. Check database credentials
4. Verify file permissions
5. Clear browser cache (Ctrl+Shift+Delete)
6. Contact Hostinger support with error logs

---

## Version Information
- **Updated**: January 4, 2025
- **PHP Version**: 7.4+ (Recommended 8.0+)
- **MySQL Version**: 5.7+ (Recommended 8.0+)
- **Status**: ✅ Hostinger Compatible

---

**All critical issues have been resolved. Your system is now ready for Hostinger deployment!**
