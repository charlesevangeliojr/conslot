<?php
// Include database configuration
require_once '../config/config.php';

// Start session
session_start();

// Check if user is logged in and is an instructor
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'instructor') {
    header('Location: ../index.html');
    exit;
}

// Get user data
$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$userEmail = $_SESSION['user_email'];

// Initialize stats based on consultation_slots
$stats = [
    'total_slots' => 0,
    'open_slots' => 0,
    'full_slots' => 0,
    'closed_slots' => 0,
    'total_capacity' => 0,
    'booked_students' => 0
];

// Get consultation slots statistics
$slotsData = [];
try {
    $statsQuery = $conn->prepare("SELECT 
        COUNT(*) as total_slots,
        COUNT(CASE WHEN status = 'open' THEN 1 END) as open_slots,
        COUNT(CASE WHEN status = 'full' THEN 1 END) as full_slots,
        COUNT(CASE WHEN status = 'closed' THEN 1 END) as closed_slots,
        SUM(max_students) as total_capacity,
        SUM(booked_students) as booked_students
        FROM consultation_slots 
        WHERE instructor_id = ?");
    if ($statsQuery) {
        $statsQuery->bind_param("i", $userId);
        $statsQuery->execute();
        $result = $statsQuery->get_result()->fetch_assoc();
        if ($result) {
            $stats = array_merge($stats, $result);
        }
        $statsQuery->close();
    }
} catch (Exception $e) {
    error_log("Slots stats query error: " . $e->getMessage());
}

// Get upcoming consultation slots
$upcomingSlots = [];
try {
    $slotsQuery = $conn->prepare("SELECT 
        id,
        slot_date,
        start_time,
        end_time,
        duration_minutes,
        max_students,
        booked_students,
        status,
        notes
        FROM consultation_slots 
        WHERE instructor_id = ? 
        AND slot_date >= CURDATE()
        ORDER BY slot_date ASC, start_time ASC
        LIMIT 10");
    if ($slotsQuery) {
        $slotsQuery->bind_param("i", $userId);
        $slotsQuery->execute();
        $upcomingSlots = $slotsQuery->get_result()->fetch_all(MYSQLI_ASSOC);
        $slotsQuery->close();
    }
} catch (Exception $e) {
    error_log("Upcoming slots query error: " . $e->getMessage());
}

// Get all slots for calendar (current month view)
$calendarSlots = [];
try {
    $calendarQuery = $conn->prepare("SELECT 
        id,
        slot_date,
        start_time,
        end_time,
        max_students,
        booked_students,
        status
        FROM consultation_slots 
        WHERE instructor_id = ? 
        AND slot_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
        AND slot_date <= DATE_ADD(CURDATE(), INTERVAL 2 MONTH)
        ORDER BY slot_date ASC, start_time ASC");
    if ($calendarQuery) {
        $calendarQuery->bind_param("i", $userId);
        $calendarQuery->execute();
        $calendarSlots = $calendarQuery->get_result()->fetch_all(MYSQLI_ASSOC);
        $calendarQuery->close();
    }
} catch (Exception $e) {
    error_log("Calendar slots query error: " . $e->getMessage());
}

// Get pending bookings that need approval
$pendingBookings = [];
try {
    $pendingQuery = $conn->prepare("SELECT 
        cb.id as booking_id,
        cb.reason,
        cb.booking_status,
        cb.created_at,
        cs.id as slot_id,
        cs.slot_date,
        cs.start_time,
        cs.end_time,
        cs.duration_minutes,
        s.id as student_id,
        s.first_name as student_first_name,
        s.last_name as student_last_name,
        s.email as student_email,
        s.course,
        s.year_level
        FROM consultation_bookings cb
        JOIN consultation_slots cs ON cb.slot_id = cs.id
        JOIN students s ON cb.student_id = s.id
        WHERE cs.instructor_id = ?
        AND cb.booking_status = 'pending'
        ORDER BY cs.slot_date ASC, cs.start_time ASC");
    if ($pendingQuery) {
        $pendingQuery->bind_param("i", $userId);
        $pendingQuery->execute();
        $pendingBookings = $pendingQuery->get_result()->fetch_all(MYSQLI_ASSOC);
        $pendingQuery->close();
    }
} catch (Exception $e) {
    error_log("Pending bookings query error: " . $e->getMessage());
}

// Convert calendar slots to JSON for JavaScript
$calendarSlotsJson = json_encode($calendarSlots);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard - ConSlot</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dashboard {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }
        .stat-label {
            color: #6c757d;
            font-weight: 500;
        }
        .recent-consultations {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .consultation-item {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .consultation-item:last-child {
            border-bottom: none;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .status-pending { background: #ffc107; color: #333; }
        .status-approved { background: #28a745; color: white; }
        .status-completed { background: #17a2b8; color: white; }
        .status-cancelled { background: #dc3545; color: white; }
        .rating {
            color: #ffc107;
        }
        
        /* Slot Item Styles */
        .slot-item {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
        }
        
        .slot-info {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .slot-date {
            font-weight: 600;
            color: #333;
        }
        
        .slot-time {
            color: #666;
            font-size: 0.9rem;
        }
        
        .slot-details {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .badge-open {
            background: #28a745;
            color: white;
        }
        
        .badge-full {
            background: #ffc107;
            color: #333;
        }
        
        .badge-closed {
            background: #dc3545;
            color: white;
        }
        
        .slot-capacity, .slot-duration {
            font-size: 0.85rem;
            color: #666;
        }
        
        .slot-notes {
            font-size: 0.85rem;
            color: #888;
            font-style: italic;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px dashed #ddd;
        }
        
        /* Booking Request Styles */
        .bookings-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .booking-request {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }
        
        .booking-student-info h4 {
            margin: 0 0 5px 0;
            color: #333;
        }
        
        .student-meta {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .booking-slot-info {
            margin-top: 10px;
            color: #495057;
            font-size: 0.9rem;
        }
        
        .booking-slot-info i {
            color: #667eea;
        }
        
        .booking-reason {
            margin-top: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
            font-size: 0.9rem;
            color: #495057;
        }
        
        .booking-actions {
            display: flex;
            gap: 10px;
            flex-shrink: 0;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        /* Calendar Styles */
        .calendar-container {
            margin-bottom: var(--spacing-lg);
        }
        
        .calendar-header {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: var(--border-color);
            border-radius: var(--radius) var(--radius) 0 0;
            overflow: hidden;
        }
        
        .calendar-day-header {
            background: var(--bg-light);
            padding: var(--spacing-sm);
            text-align: center;
            font-weight: 600;
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
        }
        
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: var(--border-color);
            border-radius: 0 0 var(--radius) var(--radius);
            overflow: hidden;
        }
        
        .calendar-day {
            background: var(--bg-primary);
            min-height: 80px;
            padding: var(--spacing-xs);
            cursor: pointer;
            transition: var(--transition-fast);
            position: relative;
            display: flex;
            flex-direction: column;
        }
        
        .calendar-day:hover {
            background: var(--bg-light);
        }
        
        .calendar-day.other-month {
            background: var(--bg-secondary);
            color: var(--text-muted);
        }
        
        .calendar-day.today {
            background: rgba(0, 123, 255, 0.1);
        }
        
        .calendar-day.has-slots {
            background: rgba(40, 167, 69, 0.1);
        }
        
        .calendar-day-number {
            font-weight: 500;
            margin-bottom: var(--spacing-xs);
        }
        
        .calendar-day.today .calendar-day-number {
            background: var(--primary-color);
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--font-size-xs);
        }
        
        .calendar-slots {
            font-size: var(--font-size-xs);
            color: var(--text-muted);
        }
        
        .calendar-slot-indicator {
            background: var(--success-color);
            color: white;
            padding: 2px 6px;
            border-radius: var(--radius-sm);
            margin-bottom: 2px;
            font-size: 10px;
        }
        
        .month-display {
            font-weight: 600;
            color: var(--text-primary);
            min-width: 150px;
            text-align: center;
            display: inline-block;
        }
        
        .existing-schedules {
            margin-top: var(--spacing-lg);
            border-top: 1px solid var(--border-light);
            padding-top: var(--spacing-lg);
        }
        
        .existing-schedules h4 {
            margin-bottom: var(--spacing-md);
            color: var(--text-primary);
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Header -->
        <div class="dashboard-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1>Welcome back, <?php echo htmlspecialchars($userName); ?>!</h1>
                    <p>Instructor Dashboard - Manage your consultations</p>
                </div>
                <div>
                    <a href="../auth/logout.php" class="btn btn-secondary">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_slots'] ?? 0; ?></div>
                <div class="stat-label">Total Slots</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['open_slots'] ?? 0; ?></div>
                <div class="stat-label">Open Slots</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['booked_students'] ?? 0; ?></div>
                <div class="stat-label">Booked Students</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo ($stats['total_capacity'] ?? 0) - ($stats['booked_students'] ?? 0); ?></div>
                <div class="stat-label">Available Seats</div>
            </div>
        </div>

        <!-- Pending Bookings Section -->
        <?php if (!empty($pendingBookings)): ?>
        <div class="card" style="margin-bottom: 30px; border-left: 4px solid #ffc107;">
            <div class="card-header" style="background: #fff3cd;">
                <h3><i class="fas fa-bell"></i> Pending Bookings (<?php echo count($pendingBookings); ?>)</h3>
                <span style="color: #856404; font-size: 0.9rem;">Students waiting for your approval</span>
            </div>
            <div class="card-content">
                <div class="bookings-list">
                    <?php foreach ($pendingBookings as $booking): ?>
                        <div class="booking-request">
                            <div class="booking-student-info">
                                <h4><?php echo htmlspecialchars($booking['student_first_name'] . ' ' . $booking['student_last_name']); ?></h4>
                                <?php if ($booking['course']): ?>
                                    <span class="student-meta"><?php echo htmlspecialchars($booking['course']); ?><?php if ($booking['year_level']) echo ' - Year ' . $booking['year_level']; ?></span>
                                <?php endif; ?>
                                <div class="booking-slot-info">
                                    <i class="fas fa-calendar"></i> 
                                    <?php echo date('M j, Y', strtotime($booking['slot_date'])); ?>
                                    <i class="fas fa-clock" style="margin-left: 10px;"></i> 
                                    <?php echo date('g:i A', strtotime($booking['start_time'])) . ' - ' . date('g:i A', strtotime($booking['end_time'])); ?>
                                </div>
                                <?php if ($booking['reason']): ?>
                                    <div class="booking-reason">
                                        <strong>Reason:</strong> <?php echo htmlspecialchars($booking['reason']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="booking-actions">
                                <a href="approve_booking.php?booking_id=<?php echo $booking['booking_id']; ?>&action=approve" 
                                   class="btn btn-success btn-sm"
                                   onclick="return confirm('Approve this booking?');">
                                    <i class="fas fa-check"></i> Approve
                                </a>
                                <a href="approve_booking.php?booking_id=<?php echo $booking['booking_id']; ?>&action=reject" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Reject this booking?');">
                                    <i class="fas fa-times"></i> Reject
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Dashboard Sections -->
        <div class="dashboard-grid">
            <!-- Instructor Schedule Calendar -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-alt"></i> Instructor Schedule</h3>
                    <div>
                        <a href="create_slot.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Create Slot
                        </a>
                        <button class="btn btn-outline btn-sm" onclick="previousMonth()">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span id="currentMonth" class="month-display"></span>
                        <button class="btn btn-outline btn-sm" onclick="nextMonth()">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="card-content">
                    <p>Click on calendar dates to set consultation slots when you're available.</p>
                    
                    <!-- Calendar Grid -->
                    <div class="calendar-container">
                        <div class="calendar-header">
                            <div class="calendar-day-header">Sun</div>
                            <div class="calendar-day-header">Mon</div>
                            <div class="calendar-day-header">Tue</div>
                            <div class="calendar-day-header">Wed</div>
                            <div class="calendar-day-header">Thu</div>
                            <div class="calendar-day-header">Fri</div>
                            <div class="calendar-day-header">Sat</div>
                        </div>
                        <div id="calendarGrid" class="calendar-grid">
                            <!-- Calendar days will be generated by JavaScript -->
                        </div>
                    </div>

                    <!-- Existing Schedules -->
                    <div class="existing-schedules">
                        <h4>Upcoming Consultation Slots</h4>
                        <div id="schedule-list" class="slot-list">
                            <?php if (empty($upcomingSlots)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-calendar-times"></i>
                                    <h3>No Upcoming Slots</h3>
                                    <p>Click on "Create Slot" to add consultation slots.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($upcomingSlots as $slot): ?>
                                    <div class="slot-item">
                                        <div class="slot-info">
                                            <div class="slot-date">
                                                <i class="fas fa-calendar"></i>
                                                <?php echo date('M j, Y', strtotime($slot['slot_date'])); ?>
                                            </div>
                                            <div class="slot-time">
                                                <i class="fas fa-clock"></i>
                                                <?php echo date('g:i A', strtotime($slot['start_time'])) . ' - ' . date('g:i A', strtotime($slot['end_time'])); ?>
                                            </div>
                                            <div class="slot-details">
                                                <span class="badge badge-<?php echo $slot['status']; ?>">
                                                    <?php echo ucfirst($slot['status']); ?>
                                                </span>
                                                <span class="slot-capacity">
                                                    <?php echo $slot['booked_students']; ?>/<?php echo $slot['max_students']; ?> students
                                                </span>
                                                <span class="slot-duration">
                                                    <?php echo $slot['duration_minutes']; ?> min each
                                                </span>
                                            </div>
                                            <?php if ($slot['notes']): ?>
                                                <div class="slot-notes">
                                                    <i class="fas fa-sticky-note"></i>
                                                    <?php echo htmlspecialchars($slot['notes']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
    </div>

    <script>
        // Calendar functionality
        let currentDate = new Date();

        function initCalendar() {
            renderCalendar();
        }

        function renderCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            
            // Update month display
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                              'July', 'August', 'September', 'October', 'November', 'December'];
            document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;
            
            // Get first day of month and number of days
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const daysInPrevMonth = new Date(year, month, 0).getDate();
            
            const calendarGrid = document.getElementById('calendarGrid');
            calendarGrid.innerHTML = '';
            
            // Previous month days
            for (let i = firstDay - 1; i >= 0; i--) {
                const day = daysInPrevMonth - i;
                const dayElement = createDayElement(day, true, new Date(year, month - 1, day));
                calendarGrid.appendChild(dayElement);
            }
            
            // Current month days
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
                const dayElement = createDayElement(day, false, date);
                calendarGrid.appendChild(dayElement);
            }
            
            // Next month days
            const totalCells = calendarGrid.children.length;
            const remainingCells = 42 - totalCells; // 6 rows * 7 days
            for (let day = 1; day <= remainingCells; day++) {
                const dayElement = createDayElement(day, true, new Date(year, month + 1, day));
                calendarGrid.appendChild(dayElement);
            }
        }

        function createDayElement(day, isOtherMonth, date) {
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day';
            
            if (isOtherMonth) {
                dayElement.classList.add('other-month');
            }
            
            // Check if today
            const today = new Date();
            if (date.toDateString() === today.toDateString()) {
                dayElement.classList.add('today');
            }
            
            // Check if has slots (this would be loaded from database)
            if (hasSlotsOnDate(date)) {
                dayElement.classList.add('has-slots');
            }
            
            dayElement.innerHTML = `
                <div class="calendar-day-number">${day}</div>
                <div class="calendar-slots">
                    ${getSlotsDisplay(date)}
                </div>
            `;
            
            dayElement.onclick = () => {
                // Redirect to create slot page with pre-filled date
                const dateStr = date.toISOString().split('T')[0];
                window.location.href = `create_slot.php?date=${dateStr}`;
            };
            
            return dayElement;
        }

        // Load calendar slots data from PHP
        const calendarSlots = <?php echo $calendarSlotsJson; ?>;
        
        function hasSlotsOnDate(date) {
            const dateStr = date.toISOString().split('T')[0];
            return calendarSlots.some(slot => slot.slot_date === dateStr);
        }

        function getSlotsDisplay(date) {
            const dateStr = date.toISOString().split('T')[0];
            const daySlots = calendarSlots.filter(slot => slot.slot_date === dateStr);
            
            if (daySlots.length === 0) return '';
            
            let html = '';
            daySlots.forEach(slot => {
                html += `<div class="calendar-slot-indicator">${slot.booked_students}/${slot.max_students}</div>`;
            });
            return html;
        }

        function previousMonth() {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        }

        function nextMonth() {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        }

        // Initialize calendar on page load
        document.addEventListener('DOMContentLoaded', initCalendar);
    </script>
</body>
</html>

<?php
if (isset($conn)) {
    $conn->close();
}
?>
