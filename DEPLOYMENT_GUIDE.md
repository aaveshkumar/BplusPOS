# B-Plus POS - Deployment Guide

## Part 1: LOCALHOST Setup (Local Development)

### Requirements:
- **PHP 7.4+** (8.0+ recommended)
- **MySQL 5.7+** or MariaDB
- **Git** (optional, for version control)

### Step 1: Download B-Plus POS
```bash
# Option A: From Replit
git clone <your-replit-project-url>
cd B-Plus-POS

# Option B: Download ZIP and extract
# Extract to your desired directory
cd B-Plus-POS
```

### Step 2: Setup Local Database
```bash
# Start MySQL/MariaDB service
# Windows: Services panel or MySQL Command Line Client
# Mac: brew services start mysql
# Linux: sudo systemctl start mysql

# Open MySQL client
mysql -u root -p

# Create database and user
CREATE DATABASE bplus_pos;
CREATE USER 'bplus_user'@'localhost' IDENTIFIED BY 'secure_password_123';
GRANT ALL PRIVILEGES ON bplus_pos.* TO 'bplus_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 3: Configure B-Plus POS
```bash
# Copy config file
cp config/config.example.php config/config.php

# Edit with your details (use your favorite editor)
# nano config/config.php
# Or open in VS Code/sublime text
```

**config/config.php** - Update these sections:
```php
// Database - LOCAL MySQL
define('DB_HOST', 'localhost');
define('DB_USER', 'bplus_user');
define('DB_PASS', 'secure_password_123');
define('DB_NAME', 'bplus_pos');
define('DB_PORT', 3306);

// WooCommerce Database - REMOTE (your store)
define('WC_DB_HOST', 'your-hostinger-mysql.com');  // Or your remote server
define('WC_DB_USER', 'your_wc_username');
define('WC_DB_PASS', 'your_wc_password');
define('WC_DB_NAME', 'your_wc_database');
define('WC_DB_PORT', 3306);

// WooCommerce REST API
define('WC_API_URL', 'https://your-store.com/wp-json');
define('WC_CONSUMER_KEY', 'your_consumer_key');
define('WC_CONSUMER_SECRET', 'your_consumer_secret');
```

### Step 4: Run LocalHost Server
```bash
# Navigate to project root
cd /path/to/B-Plus-POS

# Option A: PHP Built-in Server
php -S localhost:8000 -t public

# Option B: XAMPP (Windows/Mac)
# Start Apache & MySQL from XAMPP Control Panel
# Place folder in: C:\xampp\htdocs\bplus-pos
# Access: http://localhost/bplus-pos/public

# Option C: Using Composer with Valet (Mac/Linux)
composer require laravel/valet  # If using Valet
valet link bplus-pos
# Access: http://bplus-pos.test
```

### Step 5: Access B-Plus POS
```
URL: http://localhost:8000
Default Login:
  Username: admin
  Password: admin

⚠️ CHANGE DEFAULT PASSWORD IMMEDIATELY!
```

### Step 6: Initialize Database
```bash
# The application will auto-create necessary tables on first access
# If needed, manually import schema:
mysql -u bplus_user -p bplus_pos < database/schema.sql
```

---

## Part 2: HOSTINGER Deployment

### Requirements Check:
- ✅ PHP 7.4+ installed
- ✅ MySQL access
- ✅ FTP/SFTP access
- ✅ At least 100MB disk space

### Step 1: Create Hostinger Database
1. Log in to **Hostinger Control Panel**
2. Go to **Databases** → **MySQL Databases**
3. Click **Create New Database**
   - **Database Name:** `bplus_pos`
   - **Database User:** `bplus_user`
   - **Password:** Create strong password (save it!)
4. Click **Create**
5. Note down the database credentials

### Step 2: Prepare B-Plus POS Files
```bash
# On your local machine
# Clean up unnecessary files
rm -rf .git .gitignore
rm -rf storage/logs/* storage/cache/*
rm README.md DEPLOYMENT_GUIDE.md

# Ensure required folders exist and have permissions
mkdir -p storage/logs storage/cache storage/sessions
chmod 755 storage/logs storage/cache storage/sessions
```

### Step 3: Upload to Hostinger via FTP
**Using FileZilla (Recommended):**
1. Get **FTP Credentials** from Hostinger Control Panel
   - Go to **Advanced** → **FTP Accounts**
   - Create new FTP account if needed
   - Note: `Host`, `Username`, `Password`

2. Open **FileZilla**
   - File → Site Manager → New Site
   - **Protocol:** FTP (or SFTP for better security)
   - **Host:** `ftp.yourdomain.com`
   - **Username:** Your FTP username
   - **Password:** Your FTP password
   - Click **Connect**

3. Upload Files:
   - **Local:** Navigate to B-Plus-POS folder
   - **Remote:** Navigate to `public_html` (main folder)
   - Drag all B-Plus-POS files to remote
   - Wait for upload to complete

**Using cPanel File Manager (Alternative):**
1. Go to **cPanel** → **File Manager**
2. Navigate to `public_html`
3. Click **Upload** and select all B-Plus-POS files
4. Wait for upload

### Step 4: Configure Hostinger Files
**Via FTP/File Manager:**
1. Go to `B-Plus-POS/config/` folder
2. Download `config.example.php`
3. Edit locally:

```php
// HOSTINGER - Local Database (your POS DB)
define('DB_HOST', 'localhost');  // Hostinger usually 'localhost'
define('DB_USER', 'bplus_user');
define('DB_PASS', 'your_generated_password');
define('DB_NAME', 'bplus_pos');
define('DB_PORT', 3306);

// REMOTE - WooCommerce Database (if on different server)
define('WC_DB_HOST', 'remote-mysql.com');
define('WC_DB_USER', 'wc_user');
define('WC_DB_PASS', 'wc_pass');
define('WC_DB_NAME', 'wc_database');

// WooCommerce REST API
define('WC_API_URL', 'https://your-store.com/wp-json');
define('WC_CONSUMER_KEY', 'your_key_here');
define('WC_CONSUMER_SECRET', 'your_secret_here');
```

4. Rename to `config.php`
5. Upload back to `config/` folder

### Step 5: Set File Permissions
**Via SSH (Recommended - if available):**
```bash
ssh user@yourdomain.com

# Set proper permissions
chmod 755 config/
chmod 644 config/config.php
chmod 755 storage/
chmod 755 storage/logs
chmod 755 storage/cache
chmod 755 storage/sessions
chmod 755 public/
```

**Via File Manager:**
1. Right-click each folder
2. Properties → **Permissions**
3. Set to **755** for folders, **644** for files

### Step 6: Access Your B-Plus POS
```
URL: https://yourdomain.com/public
or: https://yourdomain.com/bplus-pos/public (if in subfolder)

Default Login:
  Username: admin
  Password: admin

⚠️ CHANGE IMMEDIATELY!
```

### Step 7: Enable HTTPS (SSL)
1. Hostinger → **SSL/TLS Certificates**
2. Install **Free AutoSSL** (automatic)
3. Or use **Let's Encrypt** (automatic renewal)
4. Test at: https://yourdomain.com

### Step 8: Test Everything
- [ ] Login successful
- [ ] Customer search works
- [ ] POS checkout functional
- [ ] Store credits displaying
- [ ] Admin dashboard loading
- [ ] Database tables present
- [ ] No error logs

---

## Troubleshooting

### Issue: "Database Connection Failed"
**Solution:**
```php
// Check config/config.php
// Verify credentials match Hostinger database settings
// For Hostinger: Usually 'localhost' works, not 'localhost:3306'
```

### Issue: "Permission Denied" on storage folder
**Solution:**
```bash
# Via SSH
chmod -R 755 storage/
chmod -R 644 storage/*
chmod -R 755 storage/logs storage/cache storage/sessions
```

### Issue: "Fatal error: Uncaught Exception"
**Solution:**
1. Check `storage/logs/error.log`
2. Verify PHP version (needs 7.4+)
3. Verify MySQL credentials
4. Check MySQL extensions enabled (`php -m | grep mysql`)

### Issue: WooCommerce Connection Failed
**Solution:**
1. Verify API credentials are correct
2. Test REST API: `curl https://your-store.com/wp-json/wc/v3/products`
3. Check API key permissions in WooCommerce
4. Verify HTTPS is working

### Issue: HTTPS Shows "Not Secure"
**Solution:**
1. In `config/config.php`, update WC_API_URL to use https
2. In `app/helpers/woocommerce.php`, ensure all API calls use HTTPS
3. Update all absolute URLs in config to use HTTPS

---

## Security Checklist

- [ ] Change default admin password
- [ ] Update config.php with unique values
- [ ] Set strong database password
- [ ] Enable HTTPS (SSL)
- [ ] Regular backups enabled (Hostinger → Backups)
- [ ] File permissions set correctly (755/644)
- [ ] Hide config.php from web access
- [ ] Enable firewall rules

---

## Performance Optimization

### Hostinger Specific:
1. Enable **Gzip Compression** in cPanel
2. Use **Cloudflare** for CDN (free in Hostinger)
3. Enable **OPcache** for PHP
4. Limit MySQL queries in admin reports
5. Enable browser caching in `.htaccess`

```apache
# .htaccess in public_html/
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType text/javascript "access plus 1 month"
</IfModule>
```

---

## Backup & Disaster Recovery

### Hostinger Backup:
1. **Automatic Backups:** Enabled by default
2. **Manual Backups:**
   - Go to **Backups** in control panel
   - Click **Backup Now**
   - Download backup file

### Local Backup:
```bash
# Backup database
mysqldump -u bplus_user -p bplus_pos > backup_$(date +%Y%m%d).sql

# Backup files
zip -r bplus_backup_$(date +%Y%m%d).zip /path/to/B-Plus-POS
```

---

## Support & Next Steps

If you encounter issues:
1. Check `storage/logs/error.log` for errors
2. Verify database connection in `config/config.php`
3. Test with simple PHP script: `<?php phpinfo(); ?>`
4. Contact Hostinger support for infrastructure issues
5. Review this guide for common troubleshooting

**Happy Selling!** 🚀
