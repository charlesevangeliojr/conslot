# ConSlot - DCC Consultation Booking Portal

**Book Smart. Consult Easy.**

A comprehensive web-based consultation booking system designed to streamline the process of scheduling appointments between students and instructors at DCC (Department of Computer and Communication).

## Project Overview

ConSlot is a modern, responsive web application that provides an intuitive platform for students to book consultations with instructors and for instructors to manage their consultation schedules efficiently.

## Current Progress

### ✅ Completed Features

#### Frontend Development
- **Landing Page**: Complete with hero section, features showcase, and call-to-action
- **Responsive Design**: Mobile-first approach with cross-device compatibility
- **Modern UI/UX**: Clean, professional interface using Tailwind CSS and Font Awesome icons
- **Navigation System**: Intuitive menu structure for different user roles

#### Authentication System
- **Student Registration**: Complete registration form with validation
- **Instructor Registration**: Separate registration pathway for instructors
- **Login System**: Role-based authentication for students and instructors
- **Password Security**: Hashed password storage and validation

#### Database Architecture
- **User Management**: Separate tables for students and instructors
- **Comprehensive Schema**: Includes profiles, schedules, appointments, and notifications
- **Data Integrity**: Proper relationships and constraints
- **Scalability**: Designed to handle multiple users and concurrent bookings

#### Core Features
- **User Profiles**: Detailed profile management for both students and instructors
- **Consultation Booking**: Time slot selection and appointment scheduling
- **Schedule Management**: Instructor availability and time slot configuration
- **Notification System**: Email and in-app notifications for appointments

### 🚧 In Progress

#### Booking System
- **Calendar Integration**: Interactive calendar for selecting consultation times
- **Availability Management**: Real-time instructor availability updates
- **Conflict Resolution**: Automatic detection of booking conflicts

#### User Dashboard
- **Student Dashboard**: View upcoming appointments, booking history
- **Instructor Dashboard**: Manage schedule, view student requests
- **Analytics**: Basic usage statistics and reporting

### 📋 Planned Features

#### Advanced Functionality
- **Video Integration**: Built-in video conferencing for remote consultations
- **File Sharing**: Secure document exchange between students and instructors
- **Rating System**: Feedback and rating mechanism for consultations
- **Mobile App**: Native mobile applications for iOS and Android

#### Administrative Features
- **Admin Panel**: Comprehensive admin dashboard for system management
- **Reporting**: Advanced analytics and reporting capabilities
- **System Configuration**: Customizable settings and preferences

## Technical Stack

### Frontend
- **HTML5**: Semantic markup and modern web standards
- **CSS3**: Responsive design with Tailwind CSS framework
- **JavaScript**: Interactive functionality and dynamic content
- **Font Awesome**: Icon library for enhanced UI

### Backend (Planned)
- **PHP**: Server-side logic and API development
- **MySQL**: Database management and data storage
- **REST API**: Standardized API for frontend-backend communication

### Development Tools
- **Git**: Version control and collaboration
- **GitHub**: Code repository and project management
- **VS Code**: Primary development environment

## Project Structure

```
conslot/
├── auth/                  # Authentication pages
│   ├── login.html
│   ├── register_student.html
│   └── register_instructor.html
├── css/                   # Stylesheets
│   └── style.css
├── img/                   # Images and assets
├── js/                    # JavaScript files
├── student/               # Student-specific pages
├── instructor/            # Instructor-specific pages
├── config/                # Configuration files
├── database.sql           # Database schema and data
├── index.html             # Main landing page
└── README.md              # Project documentation
```

## Database Schema

The system uses a comprehensive MySQL database with the following main tables:

- **students**: Student user accounts and profiles
- **instructors**: Instructor accounts and professional information
- **consultations**: Consultation types and settings
- **instructor_schedules**: Instructor availability and time slots
- **appointments**: Booked appointments and consultation records
- **notifications**: System notifications and alerts
- **reviews**: Student feedback and ratings

## Installation & Setup

### Prerequisites
- Web server (Apache/Nginx)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Git

### Setup Instructions
1. Clone the repository: `git clone git@github.com:charlesevangeliojr/conslot.git`
2. Import the database schema from `database.sql`
3. Configure database connection in `config/`
4. Set up web server to point to project root
5. Access the application via your web browser

## Contributing

This project is actively under development. Contributions are welcome in the following areas:
- Frontend UI/UX improvements
- Backend API development
- Database optimization
- Testing and quality assurance
- Documentation enhancement

## Future Roadmap

### Phase 1 (Current)
- Complete booking system implementation
- User dashboard development
- Basic notification system

### Phase 2 (Next 3 months)
- Video consultation integration
- Mobile application development
- Advanced analytics and reporting

### Phase 3 (Long-term)
- AI-powered scheduling recommendations
- Integration with learning management systems
- Multi-institution support

## Contact & Support

For questions, suggestions, or support regarding the ConSlot project:
- **Project Repository**: https://github.com/charlesevangeliojr/conslot
- **Development Team**: ConSlot Development Team

---

**Last Updated**: March 2026
**Version**: 1.0.0-alpha
**Status**: Active Development
