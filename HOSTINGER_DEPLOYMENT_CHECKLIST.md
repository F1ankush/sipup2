# Hostinger Deployment Checklist

## Before Uploading to Hostinger

- [ ] Backup your current website locally
- [ ] Note down your Hostinger database credentials
- [ ] Get your domain name ready
- [ ] Ensure you have FTP/SFTP access to Hostinger

---

## Upload & Configuration (30 minutes)

### 1. Upload Files
- [ ] Connect via FTP/SFTP to Hostinger
- [ ] Navigate to `public_html` directory
- [ ] Upload ALL files from your project
- [ ] Verify all folders uploaded:
  - [ ] `/admin/`
  - [ ] `/assets/`
  - [ ] `/includes/`
  - [ ] `/pages/`
  - [ ] `/uploads/` (create if missing)
  - [ ] `/cache/` (create if missing)

### 2. Configure Database Credentials
- [ ] Open `setup_hostinger_db.php` in browser:
  ```
  https://yourdomain.com/setup_hostinger_db.php
  ```
- [ ] Enter your Hostinger database credentials
- [ ] Click "Save Configuration"
- [ ] Verify success message appears
- [ ] **DELETE** `setup_hostinger_db.php` after successful save

### 3. Set File Permissions
- [ ] Via File Manager, set permissions:
  - [ ] `/uploads/` → 755
  - [ ] `/cache/` → 755
  - [ ] `/includes/` → 755
  - [ ] `.db_config` → 600 (read-only)

### 4. Create Database Tables
**Option A: Using phpMyAdmin**
- [ ] Go to Hostinger → MySQL Databases → phpMyAdmin
- [ ] Select your database
- [ ] Click "Import" tab
- [ ] Upload `database_schema.sql`
- [ ] Click "Go"

**Option B: Using Setup Script**
- [ ] Visit `https://yourdomain.com/setup_database.php`
- [ ] Click "Create Tables"
- [ ] Wait for confirmation
- [ ] **DELETE** `setup_database.php` after completion

### 5. Enable SSL (HTTPS)
- [ ] Go to Hostinger → SSL Certificate
- [ ] Verify SSL status shows "Active"
- [ ] Test: Visit `https://yourdomain.com` (note the HTTPS)
- [ ] Update browser favorites to use HTTPS

---

## Testing Phase (15 minutes)

### Functionality Tests
- [ ] **Home Page**: `https://yourdomain.com/` loads correctly
- [ ] **Products Page**: `https://yourdomain.com/pages/products.php` displays products
- [ ] **Login Page**: `https://yourdomain.com/pages/login.php` opens
- [ ] **Contact Page**: `https://yourdomain.com/pages/contact.php` works
- [ ] **Admin Panel**: `https://yourdomain.com/admin/login.php` accessible

### Functionality Tests (Cont.)
- [ ] **Admin Login**: Can log in with correct credentials
- [ ] **Dashboard**: Admin dashboard loads and shows data
- [ ] **Add Product**: Can add a test product
- [ ] **Upload Images**: File upload works in product creation
- [ ] **Database**: Data persists after refresh

### Security Tests
- [ ] No debug files visible (404 errors for debug files)
- [ ] `.db_config` not accessible via browser
- [ ] `/includes/` folder returns 403
- [ ] HTTPS redirect working
- [ ] Session secure cookie enabled

---

## Clean Up (5 minutes)

### Delete These Files
- [ ] `setup_database.php`
- [ ] `setup_wizard.php`
- [ ] `setup_hostinger_db.php`
- [ ] `debug_500_error.php`
- [ ] `test_system.php`
- [ ] `verify.php`
- [ ] `verify_footer.php`
- [ ] `health_check.php`
- [ ] `load_test.php`

### Keep But Secure
- [ ] `admin/login.php` - Password protect or IP restrict
- [ ] `/admin/setup.php` - Delete after setup
- [ ] `.db_config` - Keep, ensure 600 permissions

---

## Post-Deployment (Ongoing)

### Week 1
- [ ] Monitor error logs daily
- [ ] Test all user workflows
- [ ] Verify email notifications work
- [ ] Check admin panel operations

### Monthly
- [ ] Review Hostinger error logs
- [ ] Check database performance
- [ ] Verify SSL certificate valid
- [ ] Test backup restoration

### Quarterly
- [ ] Update PHP version if available
- [ ] Review security settings
- [ ] Optimize database
- [ ] Check for deprecated functions

---

## Quick Troubleshooting

### 500 Error After Upload
```
✓ Check Hostinger error logs
✓ Verify database credentials in .db_config
✓ Ensure file permissions are correct
✓ Check PHP version compatibility
```

### Database Connection Fails
```
✓ Run setup_hostinger_db.php again
✓ Verify credentials are correct
✓ Check database user has all privileges
✓ Ensure database exists
```

### Images Not Showing
```
✓ Check /uploads/ folder exists
✓ Verify permissions are 755
✓ Check file upload size limit
✓ Verify absolute paths in code
```

### Session/Login Issues
```
✓ Clear browser cookies
✓ Check SESSION_SECURE in config.php
✓ Verify database tables created
✓ Check error logs for SQL errors
```

---

## Hostinger Resources

| Resource | Link |
|----------|------|
| Hostinger Support | https://support.hostinger.com |
| Control Panel Docs | https://hostinger.com/help |
| SSL Setup Guide | https://support.hostinger.com/en/articles/360003246471 |
| FTP/SFTP Guide | https://support.hostinger.com/en/articles/360003248251 |
| PHP Version Info | https://support.hostinger.com/en/articles/360004233172 |
| MySQL Info | https://support.hostinger.com/en/articles/360003253212 |

---

## Support Contacts

- **Hostinger Support**: support@hostinger.com
- **Your Domain**: [yourdomain.com]
- **Database Name**: [check in Hostinger panel]
- **FTP User**: [check in Hostinger panel]

---

**Status**: ✅ Ready for Hostinger  
**Last Updated**: January 1, 2026  
**Time Estimate**: 1-2 hours total

