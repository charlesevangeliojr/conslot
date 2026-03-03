-- ConSlot Sample Data
-- Sample data for testing the consultation booking system

USE conslot_db;

-- Insert sample instructors (admins)
INSERT IGNORE INTO users (name, email, password, role) VALUES
('Dr. Sarah Johnson', 'sarah.johnson@dcc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Prof. Michael Chen', 'michael.chen@dcc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Dr. Emily Rodriguez', 'emily.rodriguez@dcc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample students
INSERT IGNORE INTO users (name, email, password, role) VALUES
('John Smith', 'john.smith@student.dcc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('Maria Garcia', 'maria.garcia@student.dcc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('David Kim', 'david.kim@student.dcc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('Lisa Anderson', 'lisa.anderson@student.dcc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('James Wilson', 'james.wilson@student.dcc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('Sophie Taylor', 'sophie.taylor@student.dcc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('Robert Brown', 'robert.brown@student.dcc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('Emma Davis', 'emma.davis@student.dcc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student');

-- Insert sample consultation slots for the next 2 weeks
INSERT IGNORE INTO consultation_slots (instructor_id, date, start_time, end_time, max_bookings, description) VALUES
-- Dr. Sarah Johnson's slots
(2, CURDATE(), '09:00:00', '09:30:00', 1, 'General consultation - Programming fundamentals'),
(2, CURDATE(), '09:30:00', '10:00:00', 1, 'General consultation - Programming fundamentals'),
(2, CURDATE(), '10:00:00', '10:30:00', 1, 'General consultation - Programming fundamentals'),
(2, CURDATE(), '14:00:00', '14:30:00', 1, 'Project consultation - Web development'),
(2, CURDATE(), '14:30:00', '15:00:00', 1, 'Project consultation - Web development'),
(2, CURDATE() + INTERVAL 1 DAY, '09:00:00', '09:30:00', 1, 'Database design consultation'),
(2, CURDATE() + INTERVAL 1 DAY, '09:30:00', '10:00:00', 1, 'Database design consultation'),
(2, CURDATE() + INTERVAL 2 DAY, '13:00:00', '13:30:00', 1, 'Algorithm discussion'),
(2, CURDATE() + INTERVAL 2 DAY, '13:30:00', '14:00:00', 1, 'Algorithm discussion'),

-- Prof. Michael Chen's slots
(3, CURDATE(), '11:00:00', '11:30:00', 1, 'Data structures consultation'),
(3, CURDATE(), '11:30:00', '12:00:00', 1, 'Data structures consultation'),
(3, CURDATE(), '15:00:00', '15:30:00', 1, 'Machine learning basics'),
(3, CURDATE(), '15:30:00', '16:00:00', 1, 'Machine learning basics'),
(3, CURDATE() + INTERVAL 1 DAY, '10:00:00', '10:30:00', 1, 'Python programming help'),
(3, CURDATE() + INTERVAL 1 DAY, '10:30:00', '11:00:00', 1, 'Python programming help'),
(3, CURDATE() + INTERVAL 3 DAY, '14:00:00', '14:30:00', 1, 'Research methodology'),
(3, CURDATE() + INTERVAL 3 DAY, '14:30:00', '15:00:00', 1, 'Research methodology'),

-- Dr. Emily Rodriguez's slots
(4, CURDATE(), '13:00:00', '13:30:00', 1, 'Software engineering principles'),
(4, CURDATE(), '13:30:00', '14:00:00', 1, 'Software engineering principles'),
(4, CURDATE(), '16:00:00', '16:30:00', 1, 'Mobile app development'),
(4, CURDATE(), '16:30:00', '17:00:00', 1, 'Mobile app development'),
(4, CURDATE() + INTERVAL 2 DAY, '09:00:00', '09:30:00', 1, 'UI/UX design consultation'),
(4, CURDATE() + INTERVAL 2 DAY, '09:30:00', '10:00:00', 1, 'UI/UX design consultation'),
(4, CURDATE() + INTERVAL 4 DAY, '11:00:00', '11:30:00', 1, 'Cloud computing basics'),
(4, CURDATE() + INTERVAL 4 DAY, '11:30:00', '12:00:00', 1, 'Cloud computing basics');

-- Insert some sample bookings
INSERT IGNORE INTO bookings (slot_id, student_id, status, notes) VALUES
-- Current bookings for Dr. Sarah Johnson
(1, 5, 'confirmed', 'Need help with understanding recursion in programming'),
(3, 6, 'confirmed', 'Questions about object-oriented programming concepts'),
(5, 7, 'confirmed', 'Review my web development project structure'),

-- Current bookings for Prof. Michael Chen  
(9, 8, 'confirmed', 'Help with Python data structures assignment'),
(11, 5, 'confirmed', 'Questions about machine learning algorithms'),

-- Current bookings for Dr. Emily Rodriguez
(13, 6, 'confirmed', 'Software engineering design patterns discussion'),
(15, 7, 'confirmed', 'Mobile app development framework selection'),

-- Some completed bookings (for history)
(2, 5, 'completed', 'Successfully resolved programming issues'),
(10, 8, 'completed', 'Great discussion on research methodology'),

-- Some cancelled bookings
(4, 6, 'cancelled', 'Schedule conflict, had to cancel'),
(14, 7, 'cancelled', 'Emergency came up');

-- Update current_bookings count for slots with confirmed bookings
UPDATE consultation_slots 
SET current_bookings = (
    SELECT COUNT(*) 
    FROM bookings 
    WHERE bookings.slot_id = consultation_slots.id 
    AND bookings.status = 'confirmed'
);

-- Display summary
SELECT 'Sample data inserted successfully!' as message;
SELECT COUNT(*) as total_users FROM users;
SELECT COUNT(*) as total_slots FROM consultation_slots;
SELECT COUNT(*) as total_bookings FROM bookings;
SELECT COUNT(*) as confirmed_bookings FROM bookings WHERE status = 'confirmed';
