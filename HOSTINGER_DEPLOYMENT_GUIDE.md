# Hostinger Deployment Guide

## Step-by-Step Setup Instructions

### STEP 1: Get Database Credentials from Hostinger
1. Log in to **Hostinger Control Panel**
2. Navigate to **Databases** → **MySQL Databases**
3. Create a new database or use existing one
4. Note down:
   - Database Name
   - Database User
   - Database Password
   - Host (usually `localhost`)

### STEP 2: Upload Files to Hostinger
1. In Hostinger, go to **File Manager** or use **FTP/SFTP**
2. Navigate to **public_html** folder
3. Upload all your website files there
4. Ensure proper folder structure is maintained

### STEP 3: Configure Database Credentials
**Option A: Using the Config File (Recommended)**
1. Open `includes/.db_config` on Hostinger (or create it via File Manager)
2. Add the following JSON (replace with your actual credentials):
```json
{
    "host": "localhost",
    "user": "your_database_user",
    "password": "your_database_password",
    "dbname": "your_database_name",
    "saved_at": "2026-01-01 00:00:00"
}
```

**Option B: Direct Edit in config.php**
- Edit `includes/config_manager.php`
- Update the `$defaultCredentials['production']` array with your credentials

### STEP 4: Create Database Tables
1. In Hostinger Control Panel, go to **MySQL Databases**
2. Click **phpMyAdmin**
3. Select your database
4. Import `database_schema.sql` file:
   - Click **Import** tab
   - Upload the `database_schema.sql` file
   - Click **Go**

OR use the setup script:
1. Navigate to `your-domain.com/setup_database.php`
2. Follow the on-screen instructions
3. Delete this file after completion for security

### STEP 5: Verify SSL Certificate
1. Hostinger provides FREE SSL
2. Check: **SSL Certificate** → **Manage SSL**
3. Wait for it to activate (usually instant)
4. Your site will be accessible via HTTPS

### STEP 6: File Permissions
Set these via File Manager or FTP:
```
chmod 755 /            # Main directory
chmod 755 uploads/     # Upload directory
chmod 755 cache/       # Cache directory (create if needed)
chmod 644 *.php        # PHP files
chmod 644 .db_config   # Config file
```

### STEP 7: Create Required Directories
If not present, create these folders in public_html:
- `/cache/` - for caching
- `/uploads/` - for file uploads
- `/assets/` - for CSS/JS

### STEP 8: Test Your Website
1. Visit `https://yourdomain.com`
2. Check the following pages:
   - Home page
   - Products page
   - Login page
   - Admin dashboard

---

## Configuration Changes Already Made

✅ **SITE_URL** - Now auto-detects your domain with HTTPS  
✅ **SESSION_SECURE** - Enabled for HTTPS  
✅ **CACHE** - Disabled on shared hosting (enable if Redis available)  

---

## Important Files Before Going Live

### Delete These Files (Security Risk):
- `setup_database.php` - After running once
- `setup_wizard.php` - After initial setup
- `debug_500_error.php` - Debug file
- `test_system.php` - Test file
- `verify.php` - Verification script
- `health_check.php` - Internal only
- Any other test/debug PHP files

### Keep Secure:
- `/includes/.db_config` - Keep from public access
- `/admin/` - Password protect or IP restrict

---

## Troubleshooting

### 500 Internal Server Error
- Check error logs: **Hostinger** → **Logs** → **Error logs**
- Verify database credentials in `.db_config`
- Ensure all extensions are available (mysqli, PDO)
- Check file permissions

### Database Connection Failed
1. Verify credentials are correct
2. Check if database exists
3. Ensure user has privileges
4. Test connection: Run `verify.php`

### File Upload Issues
- Ensure `/uploads/` directory has `755` permissions
- Check max file size in `config.php`
- Verify `upload_tmp_dir` is writable

### Session Issues
- Clear browser cookies
- Check `SESSION_STORAGE` setting in config
- Verify database connection

### Performance Issues
- Monitor CPU/RAM usage in Hostinger dashboard
- Disable unnecessary caching
- Consider upgrading hosting plan
- Check slow queries: Enable `QUERY_LOG_SLOW`

---

## Recommended Hostinger Settings

### PHP Configuration
- **PHP Version**: 8.0+ (check in Hostinger panel)
- **Memory Limit**: 256MB+
- **Max Execution Time**: 300+ seconds
- **Upload Max Filesize**: 100MB+

### Database
- **Backup**: Enable automatic daily backups
- **Connection Pool**: Monitor active connections
- **Slow Query Log**: Enable for optimization

---

## Maintenance Checklist

- [ ] Database credentials configured
- [ ] SSL certificate active
- [ ] All required folders created and permissions set
- [ ] Database tables created
- [ ] Test pages working
- [ ] Debug files deleted
- [ ] Backup enabled
- [ ] Error logging enabled
- [ ] Admin login working
- [ ] Products displaying correctly

---

## Post-Deployment Monitoring

1. **Regular Backups**: Enable in Hostinger
2. **Error Logs**: Check weekly via Hostinger panel
3. **SSL Renewal**: Hostinger handles automatically
4. **PHP Updates**: Keep PHP version updated
5. **Security**: Install SSL & enable firewall

---

## Support Resources

- Hostinger Help: https://support.hostinger.com
- PHP Documentation: https://www.php.net/docs.php
- MySQL Documentation: https://dev.mysql.com/doc/
- Email Support: Your Hostinger account email

---

**Last Updated**: January 1, 2026
