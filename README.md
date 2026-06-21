# 🎓 SPC Voting System & Event Posting

A comprehensive Laravel-based voting system designed for **San Pedro College (SPC)** with integrated event management. This system provides separate interfaces for **Admin** and **Student** users with role-based access control.

![Laravel](https://img.shields.io/badge/Laravel-10.x-red)
![PHP](https://img.shields.io/badge/PHP-8.1+-blue)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16+-blue)
![License](https://img.shields.io/badge/License-MIT-green)

## ✨ Features

### 👨‍🎓 Student Features
- ✅ **Secure Authentication** - Register and login with department assignment
- ✅ **Smart Voting** - Vote for candidates by position (one vote per position)
- ✅ **Real-time Results** - See live vote counts and percentages
- ✅ **Department Filtering** - Filter candidates and events by department
- ✅ **Event Management** - Post and view campus events
- ✅ **Dashboard** - Personal statistics and recent activities

### 👨‍💼 Admin Features
- ✅ **Candidate Management** - Add, edit, and delete election candidates
- ✅ **Event Oversight** - View and moderate all posted events
- ✅ **Student Directory** - View all registered students
- ✅ **Analytics Dashboard** - Complete voting statistics and insights
- ✅ **Full Control** - Manage all system components

### 🏛️ Departments Supported
- **BSIT** - Information Technology
- **CBAE** - Business Administration and Entrepreneurship
- **CRIM** - Criminology
- **CHTM** - Tourism
- **CTE** - Education
- **SHS** - Senior High School
- **CSG** - Supreme Council of Government

## 🚀 Quick Start

### Prerequisites
- XAMPP (with PHP 8.1+)
- PostgreSQL 16+
- Composer
- Git (optional)

### Installation Steps

1. **Start Services**
   - Start Apache in XAMPP
   - Start PostgreSQL (e.g., via pgAdmin or `pg_ctl`)

2. **Create Database**
   - Using psql:
     ```bash
     psql -U postgres -c "CREATE DATABASE spc_voting;"
     ```
   - Or via pgAdmin: create a new database named `spc_voting`

3. **Configure Environment**
   - Copy `.env.example` to `.env`
   - Update database settings:
     ```env
     DB_CONNECTION=pgsql
     DB_HOST=127.0.0.1
     DB_PORT=5432
     DB_DATABASE=spc_voting
     DB_USERNAME=postgres
     DB_PASSWORD=password
     ```

4. **Install & Setup**
   
   **Option A - Automated (Recommended):**
   ```bash
   setup.bat
   ```
   
   **Option B - Manual:**
   ```bash
   composer install
   php artisan key:generate
   php artisan migrate:fresh
   php artisan db:seed
   php artisan serve
   ```

5. **Access the System**
   - URL: `http://localhost:8000`

## 🔐 Default Login Credentials

### Admin Account
```
Email: admin@spc.edu
Password: admin123
```

### Student Accounts
```
Student 1:
Email: juan@spc.edu
Password: student123
Department: IT

Student 2:
Email: maria@spc.edu
Password: student123
Department: BSBA
```

## 📖 Documentation

- **[Setup Guide](SETUP_GUIDE.md)** - Detailed installation and configuration
- **[Quick Start](QUICK_START.md)** - Get started in 5 minutes
- **[Implementation Summary](IMPLEMENTATION_SUMMARY.md)** - Technical overview

## 🏗️ System Architecture

### Database Schema
```
users (enhanced with role, department, student_id)
  ├── votes (one-to-many)
  └── events (one-to-many)

candidates
  └── votes (one-to-many)

events
  └── user (belongs-to)

votes
  ├── user (belongs-to)
  └── candidate (belongs-to)
```

### Technology Stack
- **Backend:** Laravel 10.x
- **Database:** PostgreSQL 16+
- **Frontend:** Blade Templates, Vanilla JavaScript
- **Styling:** Custom CSS3 with CSS Variables
- **Icons:** Font Awesome 6.4
- **Fonts:** Google Fonts (Poppins)

## 📂 Project Structure

```
kimlaravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/           # Authentication controllers
│   │   │   ├── AdminController # Admin operations
│   │   │   ├── DashboardController
│   │   │   ├── EventController
│   │   │   └── VotingController
│   │   └── Middleware/
│   │       ├── AdminMiddleware  # Admin access control
│   │       └── StudentMiddleware # Student access control
│   └── Models/
│       ├── User, Candidate, Event, Vote
├── database/
│   ├── migrations/             # 4 migration files
│   └── seeders/               # Sample data seeder
├── resources/
│   └── views/
│       ├── layouts/           # App, Admin, Student layouts
│       ├── admin/            # Admin dashboard & pages
│       ├── student/          # Student dashboard & pages
│       └── auth/             # Login & Register
└── routes/
    └── web.php               # All application routes
```

## 🎯 Key Functionalities

### Voting System
- Position-based voting (President, Vice President, etc.)
- One vote per student per position
- Real-time vote counting
- Vote percentage calculation
- Department-wise filtering
- Visual progress indicators
- AJAX-powered (no page refresh)

### Event Management
- Student and admin can post events
- Department categorization
- Date-based organization
- Author tracking
- Admin moderation

### Security
- Password hashing (bcrypt)
- CSRF protection
- Role-based middleware
- SQL injection prevention
- XSS protection
- Unique vote constraints

## 🎨 User Interface

- **Modern Design** - Clean, professional interface
- **Responsive Layout** - Works on desktop, tablet, and mobile
- **Smooth Animations** - Engaging user experience
- **Toast Notifications** - Real-time feedback
- **Modal Dialogs** - Intuitive forms
- **Department Colors** - Visual department identification

## 🔧 Development

### Running Tests
```bash
php artisan test
```

### Clearing Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Database Reset
```bash
php artisan migrate:fresh --seed
```

## 📱 Screenshots

### Student Interface
- Dashboard with statistics
- Voting page with candidate cards
- Event posting modal
- Real-time vote results

### Admin Interface
- Complete analytics dashboard
- Candidate management table
- Event oversight panel
- Student directory

## 🤝 Contributing

This is an educational project for SPC. For improvements:
1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🆘 Support

### Common Issues

**Database Connection Failed?**
- Check PostgreSQL is running (services.msc or pgAdmin)
- Verify database credentials in `.env`
- Test connection: `psql -U postgres -d spc_voting`

**Login Not Working?**
- Clear cache: `php artisan cache:clear`
- Check seeder ran: `php artisan db:seed`
- Verify credentials are correct

**403 Forbidden Error?**
- Check user role (admin vs student)
- Verify middleware is properly configured
- Ensure you're accessing correct routes

### Need Help?
Check the documentation files:
- `SETUP_GUIDE.md` - Installation help
- `QUICK_START.md` - Quick reference
- `IMPLEMENTATION_SUMMARY.md` - Technical details

## 🎓 Educational Value

This project demonstrates:
- Laravel MVC architecture
- Authentication & Authorization
- Database relationships (One-to-Many, Many-to-Many)
- CRUD operations
- AJAX requests
- Form validation
- Middleware implementation
- Blade templating
- Role-based access control
- RESTful routing

## 🚀 Deployment

For production deployment:
1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false`
3. Generate new `APP_KEY`
4. Configure proper database credentials
5. Set up SSL certificate
6. Enable caching: `php artisan config:cache`

---

<p align="center">
<b>Built with ❤️ for San Pedro College</b><br>
Empowering Student Democracy Through Technology
</p>

<p align="center">
<i>Version 1.0 | January 2026</i>
</p>


Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
