# 📘 Step-by-Step: Getting Database Credentials from Hostinger

## Step 1: Access Your Hostinger Control Panel

1. Open your web browser
2. Go to: **https://hpanel.hostinger.com**
3. Log in with your Hostinger email and password
4. You should see the Hostinger Control Panel dashboard

## Step 2: Navigate to MySQL Databases

1. From the dashboard, find the sidebar menu
2. Click on: **Hosting** (or **Databases** depending on your account type)
3. Select: **MySQL Databases** (or just **Databases**)
4. You should see a list of your databases

## Step 3: Find Your Database

Look for a database name like:
- `u110596290_b2bsystem` (yours will be different)
- or the name you gave it when creating

### If you don't see it:
1. Click: **Create Database**
2. Enter name: `b2bsystem`
3. Click: **Create**
4. Hostinger will auto-prefix it with your account ID

## Step 4: View Database Credentials

Once you have a database:

### Method A: In Control Panel
1. Click on your database name
2. You'll see:
   - **Database Name**: `u110596290_b2bsystem`
   - **Username**: `u110596290_b22bsystem`
   - **Password**: (hidden - see Method B to view)
   - **Host**: `localhost`

### Method B: Using phpMyAdmin (Alternative)
1. Click **Manage** next to your database
2. Or click the **phpMyAdmin** link
3. Log in if prompted
4. The URL shows your username
5. Your host is always: `localhost`

## Step 5: Finding the Password

If you forgot your database password:

### Option 1: Reset It in Hostinger
1. In MySQL Databases section
2. Click **Reset Password** for the database
3. Hostinger will generate a new one
4. Copy the new password

### Option 2: Check Your Email
Hostinger sends credentials when:
- You create a new database
- You reset the password
- Check your email for "Database Created" or similar

## Step 6: Verify You Have All Info

Make a note of:

```
Database Name: ___________________
Username: _______________________
Password: ________________________
Host: localhost
```

## Step 7: Use These Credentials

### Option A: Web Setup Wizard
1. Go to: **https://paninitech.in/setup_database.php**
2. Enter all 4 pieces of information above
3. Click "Test Connection"
4. If successful, click "Save Configuration"

### Option B: Manual .db_config File
1. Create a text file with this content:

```json
{
  "host": "localhost",
  "user": "[Username from above]",
  "password": "[Password from above]",
  "dbname": "[Database Name from above]",
  "saved_at": "2024-01-01 00:00:00"
}
```

2. Save as: `.db_config` (exact name, starts with dot)
3. Upload to your server's root directory using FTP

## Step 8: Create Database Tables (One-Time)

After credentials are configured:

1. Log into Hostinger Control Panel
2. Go to MySQL Databases
3. Click **Manage** for your database
4. Or click **phpMyAdmin** link
5. In phpMyAdmin:
   - Click: **Import** tab
   - Select: `database_schema.sql` file from your computer
   - Click: **Go**
6. Wait for import to complete

This creates all required tables for the application.

## Troubleshooting

### "Cannot find MySQL Databases"
- Your hosting account might not have database support
- Contact Hostinger support
- Ask to enable MySQL databases

### "Connection test failed"
Common causes:
1. Wrong password - try resetting it
2. Wrong database name - check exact spelling
3. Wrong username - verify in Hostinger panel
4. Database not created yet - create it first

### "Access Denied for user"
- Your database username might not have permissions
- Try resetting the password in Hostinger
- Contact Hostinger support if it persists

---

## Quick Reference

| Info | Where to Find |
|------|--------------|
| Database Name | Hostinger → MySQL Databases |
| Username | Same location, or phpMyAdmin URL |
| Password | Email from Hostinger, or reset it |
| Host | Always `localhost` for Hostinger |

---

## Need More Help?

1. Visit: **https://support.hostinger.in** (Hostinger Support)
2. Chat with Hostinger support (usually available 24/7)
3. Ask about: "How to view MySQL database credentials?"

They're very responsive and can help in minutes!

---

**Version:** 1.0  
**For:** Hostinger Users  
**System:** B2B Retailer Platform
