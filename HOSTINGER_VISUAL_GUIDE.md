# 📸 Visual Guide: Finding Your Hostinger Database Credentials

## The Exact Steps You Need to Take Right Now

### STEP 1: Open Hostinger Control Panel

**URL:** https://hpanel.hostinger.com

1. Open a new browser tab
2. Paste the URL above
3. Log in with your Hostinger email and password
4. You'll see the Hostinger Control Panel dashboard

---

### STEP 2: Navigate to Databases

Once logged in, look at the **left sidebar menu**.

You should see something like:
```
Hosting
├── Domains
├── Email
├── MySQL Databases    ← CLICK HERE
├── File Manager
└── ...
```

**Click on: "MySQL Databases"**

---

### STEP 3: Find Your Database

You'll see a section like:

```
┌─────────────────────────────────────────┐
│ MySQL DATABASES                         │
├─────────────────────────────────────────┤
│ Database Name: u110596290_b2bsystem     │
│ Host: localhost                         │
│ Username: u110596290_b22bsystem         │
│ [Manage] [Reset Password] [Delete]      │
└─────────────────────────────────────────┘
```

**Note down these 3 things:**
- Database Name: `u110596290_b2bsystem`
- Username: `u110596290_b22bsystem`
- Host: `localhost`

---

### STEP 4: Get Your Password

**Your password is one of these:**

#### Option A: Check Your Email
1. Check your email inbox for "Database Created" email from Hostinger
2. Should contain password in message
3. If found, write it down!

#### Option B: Reset Password in Control Panel
If you can't find the email:

1. In MySQL Databases section
2. Find your database
3. Click: **"Reset Password"** button
4. Hostinger generates a new password
5. **COPY** the new password immediately
6. Use this new password in the next step

---

### STEP 5: Open Your Database (Optional but Helpful)

To verify your credentials work:

1. In MySQL Databases section
2. Click: **"Manage"** button (or phpMyAdmin link)
3. If it logs in successfully, credentials are correct!
4. You might see a list of tables
5. If tables are missing, you need to import database_schema.sql (see next section)

---

## 📋 Credential Checklist

Now you should have:

```
✓ Database Name: __________________
✓ Username: _____________________
✓ Password: _____________________
✓ Host: localhost
```

**If you have all 4, proceed to next section!**

---

## 🚀 Now Configure Your Website

### Option A: Use Web Wizard (EASIEST)

1. Open new browser tab
2. Go to: **https://paninitech.in/setup_database.php**
3. You'll see a form with 4 fields:
   ```
   Database Host: [localhost]
   Database Username: [enter here]
   Database Password: [enter here]
   Database Name: [enter here]
   ```
4. Enter your 4 credentials above
5. Click: **"Test Connection"** button
6. If successful, click: **"Save Configuration"**
7. Done! ✓

### Option B: Manual File Upload

If wizard doesn't work:

1. Create a text file called `.db_config` (starts with a dot!)
2. Copy this exact content:
```
{
  "host": "localhost",
  "user": "u110596290_b22bsystem",
  "password": "YOUR_PASSWORD_HERE",
  "dbname": "u110596290_b2bsystem",
  "saved_at": "2024-01-01 00:00:00"
}
```
3. Replace `YOUR_PASSWORD_HERE` with your actual password
4. Replace the user and dbname if they're different
5. Save the file
6. Upload to your website root using FTP or File Manager

---

## ✅ One-Time Setup: Create Database Tables

**Do this only ONCE, the first time:**

### If you see this error:
```
"Table 'u110596290_b2bsystem.orders' doesn't exist"
```

**This means:** Database exists but is empty - needs tables

### How to fix:

1. In Hostinger Control Panel
2. Go to: MySQL Databases
3. Click: **"Manage"** for your database (or click phpMyAdmin link)
4. You'll see the phpMyAdmin interface
5. Look for: **"Import"** tab (at the top)
6. Click it
7. Click: **"Choose File"**
8. Select: `database_schema.sql` from your computer
9. Click: **"Go"** or **"Import"** button
10. Wait for it to complete (should see "Import successful" message)
11. Done! All tables created ✓

---

## 🔍 Verify Everything Works

### Test 1: Website Loads
1. Open new browser tab
2. Go to: **https://paninitech.in/**
3. Should see your website homepage
4. ✓ If yes, database is working!
5. ✗ If no, check credentials again

### Test 2: Login Works
1. Go to homepage
2. Click: **"Login"** button
3. Try logging in with retailer account
4. Should see dashboard
5. ✓ If yes, system is fully working!

### Test 3: Health Check
1. Go to: **https://paninitech.in/health_check.php**
2. Should show green ✅ for everything
3. If any red ❌, click on it for fix suggestions

---

## 🆘 Troubleshooting Checklist

If something doesn't work, check:

### ❌ "Connection Failed" Error

**Cause:** Wrong credentials

**Fix:**
1. Double-check your password in Hostinger
2. Verify exact spelling of username
3. Verify exact database name
4. Try resetting password in Hostinger
5. Try again

### ❌ "Table doesn't exist" Error

**Cause:** Database is empty

**Fix:**
1. Import `database_schema.sql` via phpMyAdmin (see instructions above)
2. Verify all tables appear
3. Try accessing website again

### ❌ "Access Denied" Error

**Cause:** Wrong username or password

**Fix:**
1. Log into phpMyAdmin to verify username works
2. If not, reset password in Hostinger
3. Try new credentials in setup_database.php

### ❌ "Still shows 500 error"

**Fix steps:**
1. Refresh page (Ctrl+F5 or Cmd+Shift+R)
2. Check health_check.php for red ❌
3. Follow the suggestions shown
4. Contact Hostinger support if stuck

---

## 📞 Hostinger Support

If you get stuck:

1. Go to: **https://support.hostinger.in**
2. Live chat is usually available 24/7
3. Ask: "How do I view my MySQL database password?"
4. They respond in minutes!

---

## 📝 Common Questions

**Q: Where do I find the database password?**
A: Check your Hostinger account email or reset it in Control Panel

**Q: What if I don't have a database?**
A: Create one in MySQL Databases section → "Create Database"

**Q: Can I use a different database name?**
A: Yes, but then use that name in setup_database.php

**Q: Will this work on localhost too?**
A: Yes, system auto-detects and uses localhost credentials locally

**Q: Do I need to do this again?**
A: No, once saved in .db_config, it remembers for future visits

---

**You're almost there! Just follow these steps and your HTTP 500 errors will be gone! 🎉**
