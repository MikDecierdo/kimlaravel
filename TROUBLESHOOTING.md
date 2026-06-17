# 🔧 Troubleshooting Guide - SPC Voting System

## Common Issues & Solutions

### 🔴 Installation Issues

#### Issue: "composer: command not found"
**Solution:**
```bash
# Download and install Composer from: https://getcomposer.org/download/
# After installation, restart your terminal
composer --version  # Verify installation
```

#### Issue: "Laravel installer not found"
**Solution:**
You don't need Laravel installer. Just use the existing project:
```bash
cd c:\xampp\htdocs\kimlaravel
php artisan serve
```

---

### 🔴 Database Issues

#### Issue: "SQLSTATE[HY000] [1049] Unknown database 'spc_voting'"
**Solution:**
```
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click "New" in the left sidebar
3. Database name: spc_voting
4. Collation: utf8mb4_general_ci
5. Click "Create"
6. Run: php artisan migrate:fresh
```

#### Issue: "SQLSTATE[HY000] [2002] Connection refused"
**Solution:**
```
1. Open XAMPP Control Panel
2. Start MySQL (click "Start" button)
3. Wait for green highlight
4. Run: check-db.bat to verify
```

#### Issue: "Base table or view not found"
**Solution:**
```bash
# Reset database completely
php artisan migrate:fresh
php artisan db:seed
```

#### Issue: "Duplicate entry" during seeding
**Solution:**
```bash
# Use fresh migrations (drops all tables first)
php artisan migrate:fresh --seed
```

---

### 🔴 Authentication Issues

#### Issue: "These credentials do not match our records"
**Solutions:**
```
1. Check you're using correct email/password
   Admin: admin@spc.edu / admin123
   Student: juan@spc.edu / student123

2. Verify seeder ran:
   php artisan db:seed

3. Check database:
   SELECT * FROM users;  (in phpMyAdmin)

4. Reset password manually in database if needed
```

#### Issue: "419 Page Expired" on login
**Solution:**
```bash
# Clear sessions and cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
# Then refresh the page (Ctrl+F5)
```

#### Issue: Logged in but redirected to login
**Solution:**
```bash
# Check session configuration
# In .env, ensure:
SESSION_DRIVER=file

# Clear sessions
php artisan session:clear
# Or delete files in: storage/framework/sessions/
```

---

### 🔴 Permission Issues

#### Issue: "403 Forbidden" when accessing admin routes
**Causes & Solutions:**
```
1. Logged in as student (not admin)
   → Logout and login as: admin@spc.edu

2. Middleware not registered
   → Check app/Http/Kernel.php has:
     'admin' => \App\Http\Middleware\AdminMiddleware::class,

3. User role is wrong in database
   → Check: SELECT role FROM users WHERE email='your@email.com';
   → Update: UPDATE users SET role='admin' WHERE email='your@email.com';
```

#### Issue: "403 Forbidden" when voting
**Solution:**
```
Check you're logged in as a STUDENT:
1. Logout
2. Login with student credentials
3. Admin cannot access /voting route
```

---

### 🔴 Voting Issues

#### Issue: "You have already voted for this position"
**Explanation:** This is by design! Each student can only vote once per position.

**To reset votes for testing:**
```sql
-- In phpMyAdmin, run:
DELETE FROM votes;
UPDATE candidates SET votes = 0;
```

#### Issue: Vote button doesn't work (no response)
**Solutions:**
```
1. Check browser console (F12) for JavaScript errors

2. Verify CSRF token:
   - View page source
   - Look for: <meta name="csrf-token">
   - Should have a value

3. Clear browser cache:
   - Press Ctrl+Shift+Delete
   - Clear cached images and files

4. Check you're not already voted for that position
```

#### Issue: Vote count not updating
**Solution:**
```bash
# Clear application cache
php artisan cache:clear

# Refresh page with Ctrl+F5
```

---

### 🔴 Event Issues

#### Issue: Event modal won't open
**Solutions:**
```
1. Check JavaScript errors in console (F12)

2. Verify jQuery is not conflicting
   (This project uses vanilla JavaScript)

3. Hard refresh: Ctrl+F5

4. Check if button has onclick attribute:
   View page source → search for "openModal"
```

#### Issue: Event not appearing after posting
**Solutions:**
```
1. Check form validation passed
   - All fields are filled
   - Date is valid

2. Check database:
   SELECT * FROM events ORDER BY id DESC;

3. Check event_date is future date

4. Clear cache and refresh:
   php artisan cache:clear
   Ctrl+F5 in browser
```

---

### 🔴 Display Issues

#### Issue: Styles not loading / page looks broken
**Solutions:**
```
1. Hard refresh: Ctrl+Shift+R

2. Check Font Awesome CDN is loading:
   - Open browser console
   - Look for 404 errors

3. Verify internet connection
   (Fonts and icons load from CDN)

4. Clear browser cache completely
```

#### Issue: Icons not showing (boxes instead)
**Solution:**
```
Internet connection required for Font Awesome CDN.
Alternative: Download Font Awesome and use locally.
```

#### Issue: Layout broken on mobile
**Solution:**
```
This is expected during development.
The design is responsive but optimized for desktop.
Test on actual device or use Chrome DevTools mobile view.
```

---

### 🔴 Routing Issues

#### Issue: "404 Not Found" on routes
**Solutions:**
```
1. Verify route exists:
   php artisan route:list

2. Check .htaccess in public folder exists

3. Ensure you're accessing through Laravel:
   http://localhost:8000/voting  ✅
   NOT: c:\xampp\htdocs\kimlaravel\voting  ❌

4. Restart server:
   Ctrl+C to stop
   php artisan serve
```

#### Issue: "Target class [controller] does not exist"
**Solution:**
```bash
# Clear compiled files
php artisan clear-compiled
composer dump-autoload
php artisan cache:clear
```

---

### 🔴 Performance Issues

#### Issue: Page loads slowly
**Solutions:**
```
1. Check XAMPP is not running too many services

2. Clear Laravel cache:
   php artisan cache:clear
   php artisan view:clear

3. Optimize for production:
   php artisan config:cache
   php artisan route:cache

4. Check MySQL is not overloaded:
   - Restart MySQL in XAMPP
```

---

### 🔴 Development Issues

#### Issue: Changes not reflecting
**Solutions:**
```
1. For Blade views:
   php artisan view:clear
   Ctrl+F5 in browser

2. For routes:
   php artisan route:clear

3. For config:
   php artisan config:clear

4. Hard refresh browser:
   Ctrl+Shift+R
```

#### Issue: "Class not found" error
**Solution:**
```bash
composer dump-autoload
```

---

## 🛠️ Debugging Tools

### Check Database Connection
```bash
check-db.bat
```

### View All Routes
```bash
php artisan route:list
```

### Check Logs
```
View: storage/logs/laravel.log
```

### Enable Debug Mode
```env
# In .env file
APP_DEBUG=true
```

### Browser Console
```
Press F12 → Console tab
Look for JavaScript errors
```

### Database Queries
```
In phpMyAdmin:
- Check users table for correct roles
- Check votes table for vote records
- Check candidates table for vote counts
```

---

## 🚨 Emergency Reset

If everything fails, complete reset:

```bash
# 1. Drop database in phpMyAdmin
# 2. Create new database: spc_voting
# 3. Run:
composer install
php artisan key:generate
php artisan migrate:fresh
php artisan db:seed
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan serve
```

---

## 📞 Getting Help

### Before Asking for Help

1. ✅ Check this troubleshooting guide
2. ✅ Check Laravel logs: `storage/logs/laravel.log`
3. ✅ Check browser console for errors (F12)
4. ✅ Try clearing all caches
5. ✅ Try complete reset (see above)

### Information to Provide

When reporting issues, include:
- Error message (exact text)
- What you were trying to do
- Steps to reproduce
- PHP version: `php -v`
- Laravel version: `php artisan --version`
- Database engine and version
- Browser and version

---

## ✅ System Requirements Check

```bash
# PHP Version (need 8.1+)
php -v

# Composer installed
composer --version

# MySQL running
# Check XAMPP Control Panel → MySQL should be green

# Write permissions
# Ensure storage/ and bootstrap/cache/ are writable
```

---

## 🎯 Quick Diagnostic

Run these commands to check system health:

```bash
# 1. Check artisan works
php artisan --version

# 2. Check database connection
php artisan migrate:status

# 3. Check routes
php artisan route:list

# 4. Check for errors in logs
type storage\logs\laravel.log
```

---

**Still having issues?**
- Re-read the SETUP_GUIDE.md
- Check QUICK_START.md for basic setup
- Review IMPLEMENTATION_SUMMARY.md for architecture details
- Ensure you followed all installation steps correctly

---

*Last Updated: January 2026*
