# 🎓 SPC Voting System - Quick Start Guide

## 🚀 Quick Setup (Windows/XAMPP)

### Option 1: Automated Setup
1. Open command prompt in the project folder
2. Run: `setup.bat`
3. Wait for migrations and seeding to complete
4. Server will start automatically at `http://localhost:8000`

### Option 2: Manual Setup
```bash
# 1. Run migrations
php artisan migrate:fresh

# 2. Seed database
php artisan db:seed

# 3. Start server
php artisan serve
```

## 📝 Default Login Credentials

### 🔐 Admin Account
```
Email: admin@spc.edu
Password: admin123
```

### 👨‍🎓 Student Accounts
```
Email: juan@spc.edu
Password: student123
Department: IT

Email: maria@spc.edu
Password: student123
Department: BSBA
```

## 🎯 System Features Overview

### For Students:
1. **Dashboard** - View statistics and recent events
2. **Voting** - Cast votes for candidates by position
   - Can only vote once per position
   - Real-time vote count and percentage
   - Filter by department
3. **Events** - View and post campus events
   - Filter events by department
   - See who posted each event

### For Admin:
1. **Dashboard** - Overview of all system statistics
2. **Manage Candidates**
   - Add new candidates
   - Delete candidates
   - View vote counts
3. **Manage Events**
   - View all posted events
   - Delete events
4. **View Students**
   - See all registered students
   - View department assignments

## 📱 How to Use

### Registering a New Student
1. Go to `http://localhost:8000/register`
2. Fill in:
   - Full Name
   - Email Address
   - Student ID (optional)
   - Department (required)
   - Password
   - Confirm Password
3. Click "Register"
4. You'll be automatically logged in

### Voting Process
1. Login as a student
2. Click "Voting" in sidebar
3. Browse candidates or filter by department
4. Click "Vote" button on your preferred candidate
5. Vote is recorded instantly
6. You cannot vote again for the same position

### Posting an Event
1. Login as student or admin
2. Go to "Events" section
3. Click "+ Post Event" button
4. Fill in:
   - Event Title
   - Department
   - Date
   - Description
5. Click "Publish Event"

### Managing Candidates (Admin Only)
1. Login as admin
2. Go to "Candidates" in sidebar
3. Click "+ Add Candidate"
4. Fill in:
   - Name
   - Position (e.g., President, Vice President)
   - Department
   - Image URL (optional)
5. Click "Add Candidate"

## 🏗️ System Architecture

### User Roles
- **Admin**: Full system access
- **Student**: Can vote and post events

### Departments Supported
- IT (Information Technology)
- BSBA (Business Administration)
- CRIM (Criminology)
- EDUC (Education)
- ENGINEERING

### Voting Rules
- One vote per student per position
- Votes cannot be changed once cast
- Vote counts update in real-time
- Percentage calculated per position

## 🔧 Troubleshooting

### Issue: "Base table or view not found"
**Solution:** Run migrations
```bash
php artisan migrate:fresh
php artisan db:seed
```

### Issue: "Login not working"
**Solution:** Clear cache and sessions
```bash
php artisan cache:clear
php artisan config:clear
php artisan session:clear
```

### Issue: "403 Forbidden"
**Solution:** Check user role and middleware
- Students cannot access admin routes
- Make sure you're logged in with correct role

### Issue: "CSRF Token Mismatch"
**Solution:** Refresh the page or clear browser cache

## 📊 Database Schema

### Users Table
- id, name, email, password
- role (admin/student)
- department
- student_id

### Candidates Table
- id, name, position, department
- description, image, votes

### Events Table
- id, title, department
- event_date, description
- user_id (who posted)

### Votes Table
- id, user_id, candidate_id
- Unique constraint on user_id + candidate_id

## 🎨 Customization

### Change Colors
Edit `resources/views/layouts/app.blade.php`:
```css
:root {
    --primary: #4361ee;        /* Main color */
    --secondary: #f72585;      /* Accent color */
    --success: #2ec4b6;        /* Success color */
}
```

### Add More Departments
1. Update department options in:
   - `resources/views/auth/register.blade.php`
   - `resources/views/admin/candidates.blade.php`
   - `resources/views/student/events.blade.php`

### Modify Positions
Positions are flexible - just enter any position name when adding candidates.
Common positions:
- President
- Vice President
- Secretary
- Treasurer
- Representative

## 📧 Support & Development

For modifications or issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Enable debug mode in `.env`: `APP_DEBUG=true`
3. Check browser console for JavaScript errors

## ✅ Testing the System

1. **Register a new student**
   - Use register page
   - Select different departments

2. **Test voting**
   - Login as different students
   - Vote for different candidates
   - Try voting twice for same position (should fail)

3. **Test event posting**
   - Post events as student
   - View events across departments
   - Delete events as admin

4. **Test admin functions**
   - Add/delete candidates
   - View statistics
   - Manage events

---

## 🎉 You're All Set!

Your SPC Voting System is now fully functional with:
- ✅ Complete authentication system
- ✅ Role-based access control (Admin/Student)
- ✅ Full voting functionality
- ✅ Event management system
- ✅ Real-time statistics
- ✅ Department filtering
- ✅ Responsive design

**Access the system at:** `http://localhost:8000`

**Remember to start XAMPP's MySQL service before running the application!**
