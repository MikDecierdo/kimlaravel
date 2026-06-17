# SPC Voting System & Event Posting

A fully functional Laravel-based voting system for SPC (San Pedro College) with event posting capabilities.

## Features

### Student Features
- ✅ User authentication with department assignment
- ✅ Vote for candidates by position
- ✅ One vote per position restriction
- ✅ Real-time vote count and percentage display
- ✅ Post and view campus events
- ✅ Filter candidates and events by department
- ✅ Dashboard with statistics

### Admin Features
- ✅ Manage candidates (Add/Delete)
- ✅ Manage events (View/Delete)
- ✅ View all registered students
- ✅ Complete voting statistics
- ✅ Admin dashboard with analytics

## Departments
- IT (Information Technology)
- BSBA (Business Administration)
- CRIM (Criminology)
- EDUC (Education)
- ENGINEERING

## Installation & Setup

### 1. Database Configuration

Update your `.env` file with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=spc_voting
DB_USERNAME=root
DB_PASSWORD=
```

### 2. Run Migrations

```bash
php artisan migrate
```

This will create the following tables:
- `users` (with role, department, student_id fields)
- `candidates`
- `events`
- `votes`

### 3. Seed the Database

```bash
php artisan db:seed
```

This creates:
- **Admin Account:**
  - Email: `admin@spc.edu`
  - Password: `admin123`

- **Student Accounts:**
  - Email: `juan@spc.edu` | Password: `student123` (IT Dept)
  - Email: `maria@spc.edu` | Password: `student123` (BSBA Dept)

- Sample candidates (6 candidates)
- Sample events (4 events)

### 4. Start the Server

```bash
php artisan serve
```

Visit: `http://localhost:8000`

## User Roles & Access

### Admin
- **Login:** admin@spc.edu / admin123
- **Access:**
  - Dashboard
  - Manage Candidates
  - Manage Events
  - View Students

### Student
- **Login:** juan@spc.edu / student123 (or maria@spc.edu)
- **Access:**
  - Dashboard
  - Voting
  - Events (View & Post)

## Routes

### Public Routes
- `GET /` - Redirect to login or dashboard
- `GET /login` - Login page
- `POST /login` - Process login
- `GET /register` - Registration page
- `POST /register` - Process registration

### Student Routes (Authenticated)
- `GET /dashboard` - Student dashboard
- `GET /voting` - View candidates and vote
- `POST /vote/{candidate}` - Cast a vote
- `GET /events` - View events
- `POST /events` - Post new event

### Admin Routes (Admin Only)
- `GET /admin/candidates` - Manage candidates
- `POST /admin/candidates` - Add candidate
- `DELETE /admin/candidates/{id}` - Delete candidate
- `GET /admin/events` - View all events
- `DELETE /admin/events/{id}` - Delete event
- `GET /admin/students` - View all students

## Voting Rules

1. Students can only vote once per position
2. Vote counts are tracked in real-time
3. Percentage is calculated based on total votes for each position
4. Students cannot change their vote once cast

## Event Posting

- Students can post events
- Events can be filtered by department
- Events display date, description, and posted by information
- Admin can delete any event

## File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   ├── LoginController.php
│   │   │   └── RegisterController.php
│   │   ├── AdminController.php
│   │   ├── DashboardController.php
│   │   ├── EventController.php
│   │   └── VotingController.php
│   └── Middleware/
│       ├── AdminMiddleware.php
│       └── StudentMiddleware.php
├── Models/
│   ├── Candidate.php
│   ├── Event.php
│   ├── User.php
│   └── Vote.php
database/
├── migrations/
│   ├── 2024_01_01_000001_add_role_to_users_table.php
│   ├── 2024_01_01_000002_create_candidates_table.php
│   ├── 2024_01_01_000003_create_events_table.php
│   └── 2024_01_01_000004_create_votes_table.php
└── seeders/
    └── DatabaseSeeder.php
resources/
└── views/
    ├── layouts/
    │   ├── app.blade.php
    │   ├── admin.blade.php
    │   └── student.blade.php
    ├── admin/
    │   ├── dashboard.blade.php
    │   ├── candidates.blade.php
    │   ├── events.blade.php
    │   └── students.blade.php
    ├── student/
    │   ├── dashboard.blade.php
    │   ├── voting.blade.php
    │   └── events.blade.php
    └── auth/
        ├── login.blade.php
        └── register.blade.php
```

## Technologies Used

- Laravel 10
- MySQL Database
- Vanilla JavaScript (no framework)
- Font Awesome Icons
- Google Fonts (Poppins)
- CSS3 with Custom Properties

## Support

For issues or questions, please refer to the Laravel documentation or contact the system administrator.

---

**Built for San Pedro College (SPC) - Voting System & Event Management**
