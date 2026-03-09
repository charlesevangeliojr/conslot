-- ConSlot Database Structure - Separated Tables
-- Created for student and instructor registration system with separate tables

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS conslot;
USE conslot;

-- Students table
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    student_id VARCHAR(50) UNIQUE,
    year_level INT,
    course VARCHAR(100),
    department VARCHAR(100),
    profile_image VARCHAR(255),
    bio TEXT,
    status ENUM('active', 'inactive', 'pending') DEFAULT 'active',
    email_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Instructors table
CREATE TABLE instructors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    department VARCHAR(100),
    specialization VARCHAR(255),
    employee_id VARCHAR(50) UNIQUE,
    office_location VARCHAR(100),
    office_hours VARCHAR(100),
    profile_image VARCHAR(255),
    bio TEXT,
    status ENUM('active', 'inactive', 'pending') DEFAULT 'active',
    email_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Create indexes for better performance
-- Students indexes
CREATE INDEX idx_student_email ON students(email);
CREATE INDEX idx_student_status ON students(status);
CREATE INDEX idx_student_created_at ON students(created_at);
CREATE INDEX idx_student_student_id ON students(student_id);

-- Instructors indexes
CREATE INDEX idx_instructor_email ON instructors(email);
CREATE INDEX idx_instructor_status ON instructors(status);
CREATE INDEX idx_instructor_created_at ON instructors(created_at);
CREATE INDEX idx_instructor_department ON instructors(department);
CREATE INDEX idx_instructor_employee_id ON instructors(employee_id);

-- Consultations table
CREATE TABLE consultations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    instructor_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    consultation_date DATE NOT NULL,
    consultation_time TIME NOT NULL,
    duration INT DEFAULT 30, -- in minutes
    status ENUM('pending', 'approved', 'rejected', 'completed', 'cancelled') DEFAULT 'pending',
    meeting_link VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (instructor_id) REFERENCES instructors(id) ON DELETE CASCADE
);

-- Schedules table for instructor availability
CREATE TABLE schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    instructor_id INT NOT NULL,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    max_consultations INT DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (instructor_id) REFERENCES instructors(id) ON DELETE CASCADE
);

-- Activity logs table (unified for both students and instructors)
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_type ENUM('student', 'instructor') NOT NULL,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Notifications table (unified for both students and instructors)
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_type ENUM('student', 'instructor') NOT NULL,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Password resets table (unified for both students and instructors)
CREATE TABLE password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL,
    user_type ENUM('student', 'instructor') NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Email verifications table (unified for both students and instructors)
CREATE TABLE email_verifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL,
    user_type ENUM('student', 'instructor') NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Reviews table
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    instructor_id INT NOT NULL,
    consultation_id INT,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (instructor_id) REFERENCES instructors(id) ON DELETE CASCADE,
    FOREIGN KEY (consultation_id) REFERENCES consultations(id) ON DELETE SET NULL,
    UNIQUE KEY unique_review (student_id, consultation_id)
);

-- Create additional indexes for performance
CREATE INDEX idx_consultation_student ON consultations(student_id);
CREATE INDEX idx_consultation_instructor ON consultations(instructor_id);
CREATE INDEX idx_consultation_date ON consultations(consultation_date);
CREATE INDEX idx_consultation_status ON consultations(status);
CREATE INDEX idx_schedule_instructor ON schedules(instructor_id);
CREATE INDEX idx_schedule_day ON schedules(day_of_week);
CREATE INDEX idx_activity_logs_user ON activity_logs(user_type, user_id);
CREATE INDEX idx_notifications_user ON notifications(user_type, user_id);
CREATE INDEX idx_notifications_read ON notifications(is_read);

-- Create view for instructor statistics
CREATE VIEW instructor_stats AS
SELECT 
    i.id,
    i.first_name,
    i.last_name,
    i.email,
    i.specialization,
    i.department,
    COUNT(c.id) as total_consultations,
    AVG(r.rating) as average_rating,
    COUNT(r.id) as total_reviews
FROM instructors i
LEFT JOIN consultations c ON i.id = c.instructor_id
LEFT JOIN reviews r ON i.id = r.instructor_id
GROUP BY i.id;

-- Create view for student statistics
CREATE VIEW student_stats AS
SELECT 
    s.id,
    s.first_name,
    s.last_name,
    s.email,
    s.student_id,
    s.year_level,
    s.course,
    COUNT(c.id) as total_consultations,
    AVG(r.rating) as average_rating_given
FROM students s
LEFT JOIN consultations c ON s.id = c.student_id
LEFT JOIN reviews r ON s.id = r.student_id
GROUP BY s.id;

-- Insert sample data
INSERT INTO students (first_name, middle_name, last_name, email, password, phone, student_id, year_level, course, department, bio, status) VALUES
('Juan', 'Santos', 'Dela Cruz', 'juan.student@conslot.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09123456789', '2021-001', 3, 'Computer Science', 'College of Computer Studies', 'Computer Science student interested in web development', 'active'),
('Maria', 'Reyes', 'Garcia', 'maria.student@conslot.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09987654321', '2021-002', 2, 'Information Technology', 'College of Computer Studies', 'IT student with passion for mobile development', 'active');

INSERT INTO instructors (first_name, middle_name, last_name, email, password, phone, department, specialization, employee_id, office_location, office_hours, bio, status) VALUES
('Jose', 'Martinez', 'Rizal', 'jose.instructor@conslot.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09123456789', 'College of Computer Studies', 'Web Development, Database Systems, Software Engineering', 'INST-001', 'Room 301, CCS Building', 'MWF 9:00 AM - 12:00 PM', 'Experienced IT instructor with 10+ years in software development', 'active'),
('Ana', 'Santos', 'Lopez', 'ana.instructor@conslot.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09987654321', 'College of Business Administration', 'Accounting, Business Management, Finance', 'INST-002', 'Room 205, CBA Building', 'TTH 1:00 PM - 5:00 PM', 'Business administration expert with industry experience', 'active');
