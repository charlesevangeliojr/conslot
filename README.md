# ConSlot - DCC Consultation Booking Portal

## 📋 Overview

ConSlot is an online consultation booking system designed for DCC instructors and students. It provides a streamlined way for students to reserve consultation slots based on instructor availability while ensuring fair scheduling and proper time management.

## ✨ Features

- **Role-based Access**: Admin (Instructor) and Student roles
- **Time Slot Management**: Create and manage consultation time slots
- **Fair Scheduling**: Prevents double bookings and manages time limits
- **User Authentication**: Secure login and registration system
- **Real-time Availability**: Live updates of available slots
- **Responsive Design**: Works on desktop and mobile devices

## 🏗️ System Architecture

### User Roles
1. **Admin/Instructor**
   - Create and manage consultation slots
   - View all bookings
   - Manage student accounts
   - Set availability schedules

2. **Student**
   - View available consultation slots
   - Book appointments
   - Manage their bookings
   - View booking history

### Technology Stack
- **Backend**: PHP 8.0+
- **Database**: MySQL 8.0+
- **Frontend**: HTML5, CSS3, JavaScript
- **Authentication**: PHP Sessions
- **Styling**: Custom CSS with responsive design

## 📁 Project Structure

```
conslot/
├── config/
│   ├── database.php          # Database configuration
│   └── config.php            # Application settings
├── includes/
│   ├── functions.php         # Helper functions
│   ├── auth.php             # Authentication functions
│   └── header.php           # Header template
├── sql/
│   ├── setup.sql            # Database setup script
│   └── sample_data.sql      # Sample data
├── css/
│   └── style.css            # Main stylesheet
├── js/
│   └── script.js            # JavaScript functions
├── admin/
│   ├── dashboard.php        # Admin dashboard
│   ├── manage_slots.php     # Slot management
│   └── manage_users.php     # User management
├── student/
│   ├── dashboard.php        # Student dashboard
│   ├── book_slot.php        # Booking interface
│   └── my_bookings.php      # Booking history
├── auth/
│   ├── login.php            # Login page
│   ├── register.php         # Registration page
│   └── logout.php           # Logout handler
├── assets/
│   └── images/              # Image assets
├── index.php                # Landing page
└── README.md               # This file
```

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Web server (Apache/Nginx)
- Composer (optional, for dependency management)

### Step 1: Clone the Repository
```bash
git clone git@github.com:charlesevangeliojr/conslot.git
cd conslot
```

### Step 2: Database Setup
1. Create a MySQL database named `conslot_db`
2. Import the database schema:
```bash
mysql -u username -p conslot_db < sql/setup.sql
```
3. (Optional) Import sample data:
```bash
mysql -u username -p conslot_db < sql/sample_data.sql
```

### Step 3: Configuration
1. Update database credentials in `config/database.php`
2. Configure application settings in `config/config.php`

### Step 4: Web Server Setup
- Point your web server's document root to the project directory
- Ensure PHP error reporting is enabled for development
- Configure proper file permissions

### Step 5: Access the Application
- Open your browser and navigate to `http://localhost/conslot`
- Default admin credentials (after sample data import):
  - Email: admin@conslot.com
  - Password: admin123

## 📊 Database Schema

### Users Table
- `id` (Primary Key)
- `name` (VARCHAR)
- `email` (VARCHAR, Unique)
- `password` (VARCHAR, Hashed)
- `role` (ENUM: 'admin', 'student')
- `created_at` (TIMESTAMP)

### Consultation Slots Table
- `id` (Primary Key)
- `instructor_id` (Foreign Key)
- `date` (DATE)
- `start_time` (TIME)
- `end_time` (TIME)
- `max_bookings` (INT)
- `is_active` (BOOLEAN)
- `created_at` (TIMESTAMP)

### Bookings Table
- `id` (Primary Key)
- `slot_id` (Foreign Key)
- `student_id` (Foreign Key)
- `booking_time` (TIMESTAMP)
- `status` (ENUM: 'confirmed', 'cancelled', 'completed')
- `notes` (TEXT)
- `created_at` (TIMESTAMP)

## 🔧 Configuration

### Database Configuration (`config/database.php`)
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'conslot_db');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### Application Settings (`config/config.php`)
```php
define('APP_NAME', 'ConSlot');
define('APP_URL', 'http://localhost/conslot');
define('SESSION_TIMEOUT', 3600); // 1 hour
define('SLOT_DURATION', 30); // 30 minutes per slot
```

## 🎯 Usage Guide

### For Admins/Instructors
1. Log in with admin credentials
2. Create consultation slots with specific dates and times
3. Set maximum booking limits per slot
4. Monitor and manage all bookings
5. Generate reports on consultation usage

### For Students
1. Register for an account or log in
2. View available consultation slots
3. Book appointments based on availability
4. Manage upcoming bookings
5. View consultation history

## 🔒 Security Features

- Password hashing using PHP's `password_hash()`
- SQL injection prevention with prepared statements
- Session management and timeout
- Input validation and sanitization
- CSRF protection (where applicable)

## 🐛 Troubleshooting

### Common Issues
1. **Database Connection Error**: Check credentials in `config/database.php`
2. **Blank Pages**: Enable PHP error reporting in `php.ini`
3. **Login Issues**: Verify database tables and sample data
4. **Permission Errors**: Check file permissions on the project directory

### Debug Mode
To enable debug mode, add this to `config/config.php`:
```php
define('DEBUG_MODE', true);
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📝 License

This project is open source and available under the [MIT License](LICENSE).

## 📞 Support

For support and questions:
- Create an issue in the GitHub repository
- Email: support@conslot.com

---

**ConSlot** - "Book Smart. Consult Easy."