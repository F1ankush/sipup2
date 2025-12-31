# 🚨 HTTP 500 ERROR FIX GUIDE

**Problem:** HTTP 500 error when accessing localhost  
**Root Cause:** Database connection failure  
**Status:** 🔧 Ready to fix

---

## 🚀 QUICK FIX (5 minutes)

### Step 1: Verify MySQL is Running
```
1. Open XAMPP Control Panel
2. Click "Start" next to MySQL
3. Wait for it to show "Running" with green indicator
```

### Step 2: Run Debug Script
```
Open in browser: http://localhost/top1/debug_500_error.php
This will show you exactly what's wrong
```

### Step 3: Check Database Credentials
The most common issue is incorrect database credentials in `includes/config.php`

**For XAMPP default setup:**
```php
DB_HOST = 'localhost'
DB_USER = 'root'
DB_PASS = ''  (EMPTY - no password!)
DB_NAME = 'b2b_billing_system'
```

---

## ✅ VERIFICATION STEPS

### 1. Ensure Database Exists
Open phpMyAdmin (http://localhost/phpmyadmin) and verify:
- Database `b2b_billing_system` exists
- If not, create it with UTF-8 charset

### 2. Check PHP MySQLi Extension
The MySQLi extension must be enabled in `php.ini`:
```
Extension: php_mysqli.dll
Extension: php_pdo_mysql.dll
```

### 3. Verify Configuration File
Check `includes/config.php` has correct settings:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // IMPORTANT: Empty password for XAMPP!
define('DB_NAME', 'b2b_billing_system');
```

---

## 🔧 TROUBLESHOOTING

### Error: "Access denied for user 'root'@'localhost'"
**Solution:** Password is incorrect
- Change `DB_PASS` to empty string `''` 
- Or set correct MySQL password if you have one

### Error: "Unknown database 'b2b_billing_system'"
**Solution:** Database doesn't exist
- Create it in phpMyAdmin
- Or run the database schema

### Error: "Connection refused"
**Solution:** MySQL is not running
- Start MySQL in XAMPP Control Panel
- Wait 5 seconds for it to fully start

### Error: "Call to undefined function"
**Solution:** Missing include files
- Verify all files exist in `includes/` directory
- Check file permissions

---

## 📝 DATABASE SETUP (If Needed)

### Create Database in phpMyAdmin:
```sql
CREATE DATABASE IF NOT EXISTS b2b_billing_system 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
```

### Or run SQL file:
```
1. Open phpMyAdmin
2. Click "Import"
3. Select database_schema.sql
4. Click "Import"
```

---

## 🧪 TEST CONNECTION

### Option 1: Use Debug Script
```
http://localhost/top1/debug_500_error.php
```

### Option 2: Check Error Log
```
File: error_log.txt in root directory
Look for specific error messages
```

### Option 3: Enable Debug Mode
Edit `includes/config.php`:
```php
define('DEBUG_MODE', true);
```

Then reload the page to see error details

---

## 🆘 COMMON ISSUES & SOLUTIONS

| Issue | Solution |
|-------|----------|
| MySQL not running | Start MySQL in XAMPP Control Panel |
| Wrong password | Change DB_PASS to '' for XAMPP default |
| Database not found | Create database or import schema |
| MySQLi not loaded | Enable extension in php.ini |
| File permissions | Check cache/ and uploads/ are writable |
| Connection timeout | Increase connection timeout in config |

---

## ✨ FINAL VERIFICATION

Once fixed, you should see:
1. ✅ Homepage loads without error
2. ✅ No 500 errors in error_log.txt
3. ✅ Monitoring dashboard accessible
4. ✅ Database operations working
5. ✅ All pages loading correctly

---

## 📞 IF ISSUE PERSISTS

1. **Run debug script:** http://localhost/top1/debug_500_error.php
2. **Check error log:** View error_log.txt for specific messages
3. **Verify MySQL:** Make sure MySQL is running and accessible
4. **Check config:** Verify DB credentials are correct
5. **Review logs:** Check XAMPP MySQL error logs

---

## 🔗 RELATED FILES

- Configuration: `includes/config.php`
- Database class: `includes/db.php`
- Debug tool: `debug_500_error.php`
- Error log: `error_log.txt`
- Schema: `database_schema.sql`

---

## 📊 WHAT WAS FIXED

✅ Updated database credentials for XAMPP default setup  
✅ Improved error handling in db.php  
✅ Added debug script for diagnosis  
✅ Better error messages  

---

**Next:** Open debug script to verify everything is working!

```
http://localhost/top1/debug_500_error.php
```

