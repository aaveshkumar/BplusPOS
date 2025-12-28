# B-Plus POS Setup Wizard - Easy Installation

The easiest way to integrate B-Plus POS with any WordPress website!

## What is the Setup Wizard?

An interactive, guided setup process that:
- ✅ Checks if your server meets requirements
- ✅ Collects your WordPress database credentials
- ✅ Gets your WooCommerce REST API keys
- ✅ Tests all connections automatically
- ✅ Generates config.php automatically
- ✅ Creates database tables automatically
- ✅ Provides step-by-step guidance

**No manual config files needed! No command line required!**

---

## How to Use the Setup Wizard

### Step 1: Start B-Plus POS on Your Computer

**Option A: PHP Built-in Server (Easiest)**
```bash
cd B-Plus-POS
php -S localhost:8000 -t public
```

**Option B: XAMPP**
1. Place B-Plus-POS in `C:\xampp\htdocs\bplus`
2. Start Apache & MySQL in XAMPP Control Panel
3. Access: `http://localhost/bplus`

**Option C: Hostinger**
1. Upload files via FTP
2. Access: `https://yourdomain.com`

### Step 2: Open Setup Wizard

Open this URL in your browser:
```
http://localhost:8000/setup.php
```

(Replace localhost:8000 with your actual domain if using Hostinger)

### Step 3: Follow the Wizard

The wizard will:

1. **Check System Requirements** (Auto)
   - PHP version
   - MySQL support
   - Permissions
   - Takes 10 seconds

2. **Enter Credentials** (You fill this in)
   - Local database (for B-Plus POS data)
   - WordPress database (for your store)
   - WooCommerce API keys
   - Takes 5-10 minutes

3. **Test Connections** (Auto)
   - Verifies database access
   - Checks API connectivity
   - Takes 15-30 seconds

4. **Complete Setup** (Auto)
   - Generates config.php
   - Creates tables
   - Takes 5-10 seconds

5. **Ready to Use!**
   - Go to POS interface
   - Login and change password
   - Start selling!

---

## Getting Your Credentials

### WordPress Database Credentials

**From cPanel (Most Common):**
1. Log in to cPanel
2. Go to **Databases → MySQL Databases**
3. Look for your database (usually `yoursite_wp` or similar)
4. Create a new MySQL user:
   - Click **Add New User**
   - Username: `bplus_pos`
   - Password: Generate (copy it!)
   - Click **Create User**
5. Add privileges:
   - Select the user and database
   - Click **Add**
   - Select **All Privileges**
   - Click **Make Changes**

**From Hosting Dashboard:**
- Different hosts have different panels
- Usually under "Databases" or "Database Info"
- Contact support if you can't find it

### WooCommerce REST API Keys

1. Log in to your WordPress Admin Dashboard
2. Go to **WooCommerce → Settings**
3. Click **Advanced** tab
4. Click **REST API** (or **API**)
5. Click **Create an API Key**
   - Description: `B-Plus POS`
   - User: Your admin account
   - Permissions: **Read/Write**
6. Copy the keys immediately (they only show once!)

### WordPress REST API URL

Usually:
```
https://yourdomain.com/wp-json/wc/v3
```

Or if WordPress is in a folder:
```
https://yourdomain.com/wordpress/wp-json/wc/v3
```

To verify, open in browser:
```
https://yourdomain.com/wp-json/wc/v3/products
```

You should see JSON data (or empty array if no products)

---

## Troubleshooting

### "setup.php not found"
- Make sure B-Plus-POS folder is in the right location
- Access: `http://localhost:8000/setup.php`
- Check PHP server is running

### "System requirements failed"
- You're missing PHP extensions
- Contact your hosting provider
- Or install PHP locally with required extensions

### "Database connection failed"
- Double-check credentials are correct (spaces matter!)
- Verify database user has correct permissions
- Try creating the database first manually

### "API connection failed"
- Verify REST API URL has no trailing slash
- Must be HTTPS (not HTTP)
- Check API keys are copied exactly (no spaces!)
- Go back and regenerate API keys

### "Setup failed / config.php won't write"
- Check `/config` folder has write permissions
- Delete `config/config.php` if it exists
- On Linux/cPanel: `chmod 755 config/`

---

## After Setup

1. **Delete setup.php** (security):
   ```bash
   rm setup.php
   # Or delete via FTP
   ```

2. **Go to B-Plus POS**:
   ```
   http://localhost:8000/public
   ```

3. **Login**:
   - Username: `admin`
   - Password: `admin`

4. **Change Your Password**:
   - Go to Settings
   - Change admin password immediately!

5. **Start Using**:
   - Search for products
   - Add customers
   - Create orders
   - Process returns

---

## Security Checklist

- [ ] Deleted setup.php file
- [ ] Changed default admin password
- [ ] HTTPS enabled (for production)
- [ ] API keys are secure
- [ ] Database user has limited privileges (not root!)
- [ ] Never share config.php file

---

## Next Steps

1. Test with some sample orders
2. Verify products show correctly
3. Check orders appear in WordPress
4. Train your staff
5. Deploy to production (Hostinger)

---

## Need Help?

If setup fails:

1. Check the **error messages** carefully - they tell you what's wrong
2. Verify all credentials are **exactly correct**
3. Contact your **hosting provider** if database issues
4. Review **WOOCOMMERCE_INTEGRATION.md** for detailed troubleshooting

**The setup wizard makes integration SO easy!** 🚀
