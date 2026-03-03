-- ConSlot Database Setup Script
-- DCC Consultation Booking Portal Database Schema

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS conslot_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE conslot_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'student') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Consultation slots table
CREATE TABLE IF NOT EXISTS consultation_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    instructor_id INT NOT NULL,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    max_bookings INT NOT NULL DEFAULT 1,
    current_bookings INT NOT NULL DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_instructor (instructor_id),
    INDEX idx_date (date),
    INDEX idx_active (is_active),
    CONSTRAINT chk_time CHECK (end_time > start_time),
    CONSTRAINT chk_bookings CHECK (current_bookings <= max_bookings)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slot_id INT NOT NULL,
    student_id INT NOT NULL,
    booking_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('confirmed', 'cancelled', 'completed') NOT NULL DEFAULT 'confirmed',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (slot_id) REFERENCES consultation_slots(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_slot (slot_id),
    INDEX idx_student (student_id),
    INDEX idx_status (status),
    INDEX idx_booking_time (booking_time),
    UNIQUE KEY unique_student_slot (slot_id, student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create triggers for updating current_bookings count
DELIMITER //

CREATE TRIGGER after_booking_insert 
AFTER INSERT ON bookings 
FOR EACH ROW
BEGIN
    IF NEW.status = 'confirmed' THEN
        UPDATE consultation_slots 
        SET current_bookings = current_bookings + 1 
        WHERE id = NEW.slot_id;
    END IF;
END//

CREATE TRIGGER after_booking_update 
AFTER UPDATE ON bookings 
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        CASE 
            WHEN OLD.status = 'confirmed' AND NEW.status != 'confirmed' THEN
                UPDATE consultation_slots 
                SET current_bookings = current_bookings - 1 
                WHERE id = NEW.slot_id;
            WHEN OLD.status != 'confirmed' AND NEW.status = 'confirmed' THEN
                UPDATE consultation_slots 
                SET current_bookings = current_bookings + 1 
                WHERE id = NEW.slot_id;
        END CASE;
    END IF;
END//

CREATE TRIGGER after_booking_delete 
AFTER DELETE ON bookings 
FOR EACH ROW
BEGIN
    IF OLD.status = 'confirmed' THEN
        UPDATE consultation_slots 
        SET current_bookings = current_bookings - 1 
        WHERE id = OLD.slot_id;
    END IF;
END//

DELIMITER ;

-- Create view for available slots
CREATE OR REPLACE VIEW available_slots AS
SELECT 
    cs.id,
    cs.date,
    cs.start_time,
    cs.end_time,
    cs.max_bookings,
    cs.current_bookings,
    cs.description,
    u.name as instructor_name,
    u.email as instructor_email,
    (cs.max_bookings - cs.current_bookings) as available_spots
FROM consultation_slots cs
JOIN users u ON cs.instructor_id = u.id
WHERE cs.is_active = TRUE 
AND cs.date >= CURDATE()
AND cs.current_bookings < cs.max_bookings
ORDER BY cs.date, cs.start_time;

-- Create view for booking details
CREATE OR REPLACE VIEW booking_details AS
SELECT 
    b.id,
    b.booking_time,
    b.status,
    b.notes,
    cs.date,
    cs.start_time,
    cs.end_time,
    cs.description,
    instructor.name as instructor_name,
    instructor.email as instructor_email,
    student.name as student_name,
    student.email as student_email
FROM bookings b
JOIN consultation_slots cs ON b.slot_id = cs.id
JOIN users instructor ON cs.instructor_id = instructor.id
JOIN users student ON b.student_id = student.id
ORDER BY cs.date, cs.start_time;

-- Insert default admin user (password: admin123)
INSERT IGNORE INTO users (id, name, email, password, role) 
VALUES (1, 'System Administrator', 'admin@conslot.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Set up completed message
SELECT 'ConSlot database setup completed successfully!' as message;
