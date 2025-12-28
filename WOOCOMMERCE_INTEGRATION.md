# B-Plus POS - WooCommerce Integration Guide

Complete guide to connect B-Plus POS to your WooCommerce-powered WordPress website.

---

## Prerequisites

- ✅ WordPress website with WooCommerce installed and activated
- ✅ Admin access to your WordPress dashboard
- ✅ B-Plus POS installed on your server (localhost or Hostinger)
- ✅ HTTPS enabled on your WordPress site (required for API)

---

## Step 1: Get WooCommerce Database Credentials

### Option A: Shared Hosting (cPanel - Most Common)

1. Log in to **cPanel** on your hosting provider
2. Go to **Databases** → **MySQL Databases**
3. Find your WooCommerce database (usually named `wordpress_db` or similar)
4. Create a new **MySQL user** for POS (better for security):
   - Click **Add New User**
   - **Username:** `bplus_pos` (or similar)
   - **Password:** Generate strong password (copy this!)
   - Click **Create User**
5. **Add permissions** to the user:
   - Check the new user against your database
   - Click **Add**
   - Select **All Privileges**
   - Click **Make Changes**

**Your credentials are:**
```
DB_HOST: localhost (usually)
DB_USER: bplus_pos
DB_PASS: [your generated password]
DB_NAME: [your wordpress database name]
DB_PORT: 3306
```

### Option B: Managed WordPress Hosting (Kinsta, WP Engine, etc.)

1. Log in to your hosting dashboard
2. Go to **Database** or **Database Info**
3. Copy the credentials:
   - Host (might be: `mysql.example.com` or `localhost`)
   - Database name
   - Username
   - Password
4. If you can't find credentials, contact support

### Option C: Dedicated Server / VPS

1. SSH into your server:
   ```bash
   ssh user@your-server.com
   ```

2. Find WordPress config:
   ```bash
   cat /var/www/html/wp-config.php | grep "DB_"
   ```

3. You'll see:
   ```php
   define('DB_NAME', 'wordpress_db');
   define('DB_USER', 'wordpress_user');
   define('DB_PASSWORD', 'password');
   define('DB_HOST', 'localhost');
   ```

---

## Step 2: Generate WooCommerce API Credentials

### Create API Keys in WordPress

1. Log in to your **WordPress Admin Dashboard**
   - Go to **WooCommerce** → **Settings**
   - Click **Advanced** tab
   - Click **REST API** (or **API** depending on WC version)

2. Click **Create an API Key**
   - **Description:** `B-Plus POS System`
   - **User:** Your admin account (or create dedicated user)
   - **Permissions:** Read/Write (required for orders and stock updates)

3. Click **Generate API Key**

4. **Copy these values** (they appear once, save them!):
   - **Consumer Key:** `ck_1a2b3c4d5e6f7g8h9i10...`
   - **Consumer Secret:** `cs_0i9h8g7f6e5d4c3b2a1...`

### Enable REST API (if not already enabled)

1. Go to **WooCommerce** → **Settings** → **Advanced**
2. Scroll to **REST API**
3. Make sure it shows: ✅ "WordPress REST API is enabled"
4. If disabled, check under **Settings** → **Permalinks** and set to anything except "Plain"

---

## Step 3: Find Your WordPress REST API URL

The API endpoint for WooCommerce is usually:

```
https://yourdomain.com/wp-json/wc/v3
```

Or if WordPress is in a subdirectory:

```
https://yourdomain.com/wordpress/wp-json/wc/v3
```

**To verify your API URL works:**
1. Open in browser:
   ```
   https://yourdomain.com/wp-json/wc/v3/products?per_page=1
   ```
2. You should see a JSON response with products (or empty array if no products)
3. If you see an error, your API is not working - contact support

---

## Step 4: Configure B-Plus POS

### Edit config/config.php

**Find your B-Plus POS installation** and edit `config/config.php`:

```php
<?php
/**
 * B-Plus POS Configuration
 * Connect to your WooCommerce WordPress Database
 */

// ============================================
// 1. LOCAL POS DATABASE
// (B-Plus POS system tables - orders, returns, etc)
// ============================================
define('DB_HOST', 'localhost');
define('DB_USER', 'bplus_user');
define('DB_PASS', 'secure_password_123');
define('DB_NAME', 'bplus_pos');
define('DB_PORT', 3306);

// ============================================
// 2. WOOCOMMERCE DATABASE
// (Products, customers, inventory)
// ============================================
define('WC_DB_HOST', 'localhost');          // ← Update this
define('WC_DB_USER', 'bplus_pos');          // ← Update this
define('WC_DB_PASS', 'your_wp_password');   // ← Update this
define('WC_DB_NAME', 'wordpress_db');       // ← Update this
define('WC_DB_PORT', 3306);

// ============================================
// 3. WOOCOMMERCE REST API
// (For creating orders, updating stock)
// ============================================
define('WC_API_URL', 'https://yourdomain.com/wp-json/wc/v3');  // ← Update this
define('WC_CONSUMER_KEY', 'ck_xxxxxxxxxxxxxxxx');              // ← Paste your key
define('WC_CONSUMER_SECRET', 'cs_xxxxxxxxxxxxxxxx');           // ← Paste your secret

// ============================================
// 4. WORDPRESS TABLE PREFIX
// (Usually 'wp_' - check wp-config.php)
// ============================================
define('WP_TABLE_PREFIX', 'wp_');  // ← Verify this matches

// Rest of configuration...
?>
```

### Example: Real Configuration

If your WooCommerce site is `https://mystore.com`:

```php
// WooCommerce Database
define('WC_DB_HOST', 'db.myserver.com');
define('WC_DB_USER', 'mystore_user');
define('WC_DB_PASS', 'MySecurePass123!');
define('WC_DB_NAME', 'mystore_wordpress');
define('WC_DB_PORT', 3306);

// WooCommerce REST API
define('WC_API_URL', 'https://mystore.com/wp-json/wc/v3');
define('WC_CONSUMER_KEY', 'ck_5a7b9c2d1e4f6a8b3c9d2e5f7a8b9c2d');
define('WC_CONSUMER_SECRET', 'cs_9c8b7a6f5e4d3c2b1a0f9e8d7c6b5a4f');

// WordPress Table Prefix (from wp-config.php)
define('WP_TABLE_PREFIX', 'wp_');
```

---

## Step 5: Test the Connection

### Test 1: WordPress Database Connection

1. Open B-Plus POS in your browser
2. Try to **search for products** - if it shows products from your store, database is connected ✅

### Test 2: REST API Connection

1. Go to **Admin Dashboard** → **Settings**
2. Look for **API Test** button (if available)
3. Or test manually:
   ```bash
   curl -u "ck_xxxxx:cs_xxxxx" "https://yourdomain.com/wp-json/wc/v3/products?per_page=1"
   ```
4. Should return JSON with products ✅

### Test 3: Create an Order

1. Go to **POS** interface
2. Add products to cart
3. Complete checkout
4. **Check your WordPress site** → **WooCommerce** → **Orders**
5. New order should appear there ✅

### Test 4: Update Stock

1. Sell an item through POS
2. Go to WordPress → **Products**
3. Check product stock level - should be decreased ✅

---

## Troubleshooting

### ❌ "Database Connection Failed"

**Solution:**
1. Verify credentials in `config/config.php` are **exactly** correct
2. Check if WordPress database is on same server:
   - Same server: use `localhost`
   - Different server: use full hostname (e.g., `db.example.com`)
3. Verify MySQL user has **privileges** on the database:
   ```sql
   SHOW GRANTS FOR 'bplus_pos'@'localhost';
   ```
4. Check if MySQL port is **3306** (or different in your setup)

### ❌ "Products Not Showing"

**Solution:**
1. Verify `WC_TABLE_PREFIX` in config matches your WordPress
   - Check in WordPress → **WP-Admin URL**
   - Or check `wp-config.php` for `$table_prefix`
2. Verify WordPress database connection is working
3. Check if there are products in your WordPress store

### ❌ "API Connection Failed"

**Solution:**
1. Verify `WC_API_URL` is correct:
   - Should be: `https://yourdomain.com/wp-json/wc/v3`
   - **NOT** `http://` (must be HTTPS)
   - **NO** trailing slash
2. Test REST API directly:
   ```bash
   curl "https://yourdomain.com/wp-json/wc/v3/products?per_page=1"
   ```
3. If 404 error, enable **Permalinks**:
   - Go to WordPress → **Settings** → **Permalinks**
   - Select anything except "Plain"
   - Click **Save Changes**
4. Regenerate API keys - old ones may be revoked

### ❌ "Authentication Failed" (API)

**Solution:**
1. Verify Consumer Key and Secret are copied **exactly** (spaces matter!)
2. Regenerate new API keys:
   - **WordPress** → **WooCommerce** → **Settings** → **API**
   - Delete old key, create new one
   - Copy immediately
3. Check API permissions - should be **Read/Write**
4. Check user still has admin access

### ❌ "Orders Not Syncing"

**Solution:**
1. Check `WC_API_URL` has correct domain
2. Verify API credentials are working (test above)
3. Check if WooCommerce payment gateways are configured
4. Review error logs: `storage/logs/error.log`

### ❌ "Stock Not Updating"

**Solution:**
1. Verify product is being tracked for stock:
   - WordPress → **Products** → Edit product
   - Check **Inventory** tab → **Track Quantity** is enabled
2. Verify API has **Write** permissions
3. Check product sync:
   - Sell item through POS
   - Wait 2-3 seconds
   - Refresh WordPress product page
4. Check error logs for details

---

## Security Checklist

- [ ] Use **HTTPS** (http**s** not http) for all WordPress URLs
- [ ] Store API keys securely in `config/config.php`
- [ ] Never commit `config/config.php` to Git
- [ ] Create separate MySQL user for POS (with limited privileges if possible)
- [ ] Regularly rotate API keys (regenerate in WordPress)
- [ ] Use strong passwords for both DB user and API credentials
- [ ] Keep WordPress and WooCommerce **updated**
- [ ] Enable two-factor authentication on WordPress admin
- [ ] Disable XML-RPC if not needed (WordPress → **Settings** → **Writing**)

---

## Connection Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    B-Plus POS System                     │
│                   (Your Server/Laptop)                   │
└─────────────────────────────────────────────────────────┘
                            ↑
                ┌───────────┴───────────┐
                ↓                       ↓
        ┌──────────────┐       ┌──────────────┐
        │ Local POS DB │       │ WordPress DB │
        │ (Orders,     │       │ (Products,   │
        │  Returns)    │       │  Customers)  │
        └──────────────┘       └──────────────┘
                                      ↑
                        ┌─────────────┴────────────┐
                        │  WordPress Site          │
                        │  https://yourdomain.com  │
                        │  (WooCommerce + REST API)│
                        └──────────────────────────┘
```

**Data Flow:**
1. **POS creates order** → Sent via REST API to WordPress
2. **WordPress stores order** in WooCommerce database
3. **Stock updated** in WordPress automatically
4. **Customer data synced** from WordPress to POS database

---

## Multi-Store Setup (Advanced)

If you have **multiple WordPress sites**, repeat this process for each:

```php
// For Store 1
define('WC_API_URL', 'https://store1.com/wp-json/wc/v3');
define('WC_CONSUMER_KEY', 'ck_store1_xxxxx');
define('WC_CONSUMER_SECRET', 'cs_store1_xxxxx');

// For Store 2 (requires separate POS database)
// Run B-Plus POS twice with different databases
```

---

## Next Steps

1. ✅ Complete the configuration above
2. ✅ Test connection (see **Test the Connection** section)
3. ✅ Add some products through POS
4. ✅ Verify they appear in WordPress
5. ✅ Train your staff on POS interface
6. ✅ Go live!

---

## Support

If you encounter issues:

1. **Check logs** in `storage/logs/error.log`
2. **Verify credentials** in `config/config.php`
3. **Test REST API** directly in browser
4. **Review troubleshooting** section above
5. **Contact your hosting provider** for database/server issues

**Happy selling!** 🚀
