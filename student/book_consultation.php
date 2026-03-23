<?php
// Include database configuration
require_once '../config/config.php';

// Start session
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header('Location: ../index.html');
    exit;
}

// Get user data
$studentId = $_SESSION['user_id'];
$studentName = $_SESSION['user_name'];
$studentEmail = $_SESSION['user_email'];

// Get available consultation slots (open slots with capacity)
$availableSlots = [];
try {
    $slotsQuery = $conn->prepare("SELECT 
        cs.id,
        cs.slot_date,
        cs.start_time,
        cs.end_time,
        cs.duration_minutes,
        cs.max_students,
        cs.booked_students,
        cs.notes,
        i.first_name as instructor_first_name,
        i.last_name as instructor_last_name,
        i.specialization
        FROM consultation_slots cs
        JOIN instructors i ON cs.instructor_id = i.id
        WHERE cs.status = 'open'
        AND cs.slot_date >= CURDATE()
        AND cs.booked_students < cs.max_students
        AND cs.id NOT IN (
            SELECT slot_id FROM consultation_bookings 
            WHERE student_id = ? AND booking_status IN ('pending', 'approved')
        )
        ORDER BY cs.slot_date ASC, cs.start_time ASC");
    if ($slotsQuery) {
        $slotsQuery->bind_param("i", $studentId);
        $slotsQuery->execute();
        $availableSlots = $slotsQuery->get_result()->fetch_all(MYSQLI_ASSOC);
        $slotsQuery->close();
    }
} catch (Exception $e) {
    error_log("Available slots query error: " . $e->getMessage());
}

// Get student's bookings
$myBookings = [];
try {
    $bookingsQuery = $conn->prepare("SELECT 
        cb.id,
        cb.slot_id,
        cb.reason,
        cb.booking_status,
        cb.created_at,
        cs.slot_date,
        cs.start_time,
        cs.end_time,
        cs.duration_minutes,
        i.first_name as instructor_first_name,
        i.last_name as instructor_last_name
        FROM consultation_bookings cb
        JOIN consultation_slots cs ON cb.slot_id = cs.id
        JOIN instructors i ON cs.instructor_id = i.id
        WHERE cb.student_id = ?
        ORDER BY cs.slot_date DESC, cs.start_time DESC");
    if ($bookingsQuery) {
        $bookingsQuery->bind_param("i", $studentId);
        $bookingsQuery->execute();
        $myBookings = $bookingsQuery->get_result()->fetch_all(MYSQLI_ASSOC);
        $bookingsQuery->close();
    }
} catch (Exception $e) {
    error_log("My bookings query error: " . $e->getMessage());
}

// Close connection
if (isset($conn)) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Consultation - ConSlot</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .booking-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            margin: 0;
            font-size: 2rem;
        }
        
        .page-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .tab {
            padding: 12px 24px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            color: #6c757d;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }
        
        .tab:hover {
            color: #667eea;
        }
        
        .tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .slots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .slot-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .slot-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .slot-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .instructor-info h3 {
            margin: 0 0 5px 0;
            color: #333;
        }
        
        .instructor-specialization {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .slot-date {
            text-align: right;
        }
        
        .date-badge {
            background: #667eea;
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 600;
        }
        
        .slot-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .detail-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            color: #495057;
        }
        
        .detail-row:last-child {
            margin-bottom: 0;
        }
        
        .detail-row i {
            color: #667eea;
            width: 20px;
        }
        
        .capacity-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
        }
        
        .capacity-bar {
            flex: 1;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .capacity-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        
        .capacity-text {
            font-size: 0.85rem;
            color: #6c757d;
            min-width: 60px;
            text-align: right;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-full {
            background: #6c757d;
            color: white;
            cursor: not-allowed;
            opacity: 0.6;
        }
        
        .booking-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 15px;
        }
        
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #ffc107;
            color: #333;
        }
        
        .status-approved {
            background: #28a745;
            color: white;
        }
        
        .status-rejected {
            background: #dc3545;
            color: white;
        }
        
        .status-cancelled {
            background: #6c757d;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }
        
        .reason-text {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            margin-top: 10px;
            font-size: 0.9rem;
            color: #495057;
        }
        
        .navigation {
            margin-bottom: 20px;
        }
        
        .navigation a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .navigation a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="booking-container">
        <!-- Navigation -->
        <div class="navigation">
            <a href="dashboard.php">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-calendar-check"></i> Book Consultation</h1>
            <p>Find available consultation slots and book your appointment</p>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="showTab('available')">
                <i class="fas fa-calendar-plus"></i> Available Slots
                <span style="background: #667eea; color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; margin-left: 8px;">
                    <?php echo count($availableSlots); ?>
                </span>
            </button>
            <button class="tab" onclick="showTab('mybookings')">
                <i class="fas fa-list"></i> My Bookings
                <span style="background: #6c757d; color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; margin-left: 8px;">
                    <?php echo count($myBookings); ?>
                </span>
            </button>
        </div>

        <!-- Available Slots Tab -->
        <div id="available" class="tab-content active">
            <?php if (empty($availableSlots)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No Available Slots</h3>
                    <p>There are no consultation slots available at the moment. Please check back later.</p>
                </div>
            <?php else: ?>
                <div class="slots-grid">
                    <?php foreach ($availableSlots as $slot): ?>
                        <div class="slot-card">
                            <div class="slot-header">
                                <div class="instructor-info">
                                    <h3><?php echo htmlspecialchars($slot['instructor_first_name'] . ' ' . $slot['instructor_last_name']); ?></h3>
                                    <?php if ($slot['specialization']): ?>
                                        <div class="instructor-specialization">
                                            <i class="fas fa-briefcase"></i> 
                                            <?php echo htmlspecialchars($slot['specialization']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="slot-date">
                                    <div class="date-badge">
                                        <?php echo date('M j', strtotime($slot['slot_date'])); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="slot-details">
                                <div class="detail-row">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo date('g:i A', strtotime($slot['start_time'])) . ' - ' . date('g:i A', strtotime($slot['end_time'])); ?></span>
                                </div>
                                <div class="detail-row">
                                    <i class="fas fa-hourglass-half"></i>
                                    <span><?php echo $slot['duration_minutes']; ?> minutes per student</span>
                                </div>
                                <?php if ($slot['notes']): ?>
                                    <div class="detail-row">
                                        <i class="fas fa-sticky-note"></i>
                                        <span><?php echo htmlspecialchars($slot['notes']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="capacity-indicator">
                                <div class="capacity-bar">
                                    <div class="capacity-fill" style="width: <?php echo ($slot['booked_students'] / $slot['max_students']) * 100; ?>%"></div>
                                </div>
                                <div class="capacity-text">
                                    <?php echo $slot['booked_students']; ?>/<?php echo $slot['max_students']; ?> booked
                                </div>
                            </div>
                            
                            <div style="margin-top: 15px;">
                                <?php if ($slot['booked_students'] >= $slot['max_students']): ?>
                                    <button class="btn btn-full" disabled>
                                        <i class="fas fa-times-circle"></i> Slot Full
                                    </button>
                                <?php else: ?>
                                    <a href="book_slot.php?slot_id=<?php echo $slot['id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-calendar-plus"></i> Book Now
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- My Bookings Tab -->
        <div id="mybookings" class="tab-content">
            <?php if (empty($myBookings)): ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>No Bookings Yet</h3>
                    <p>You haven't booked any consultations yet. Browse available slots to get started.</p>
                </div>
            <?php else: ?>
                <?php foreach ($myBookings as $booking): ?>
                    <div class="booking-card">
                        <div class="booking-header">
                            <div>
                                <h3 style="margin: 0 0 5px 0;">
                                    <?php echo htmlspecialchars($booking['instructor_first_name'] . ' ' . $booking['instructor_last_name']); ?>
                                </h3>
                                <div style="color: #6c757d; font-size: 0.9rem;">
                                    <i class="fas fa-calendar"></i> 
                                    <?php echo date('F j, Y', strtotime($booking['slot_date'])); ?>
                                    <i class="fas fa-clock" style="margin-left: 15px;"></i> 
                                    <?php echo date('g:i A', strtotime($booking['start_time'])) . ' - ' . date('g:i A', strtotime($booking['end_time'])); ?>
                                </div>
                            </div>
                            <span class="status-badge status-<?php echo $booking['booking_status']; ?>">
                                <?php echo ucfirst($booking['booking_status']); ?>
                            </span>
                        </div>
                        
                        <div style="display: flex; gap: 20px; margin-bottom: 10px;">
                            <div>
                                <i class="fas fa-hourglass-half" style="color: #667eea;"></i>
                                <?php echo $booking['duration_minutes']; ?> minutes
                            </div>
                            <div>
                                <i class="fas fa-calendar-check" style="color: #667eea;"></i>
                                Booked on <?php echo date('M j, Y', strtotime($booking['created_at'])); ?>
                            </div>
                        </div>
                        
                        <?php if ($booking['reason']): ?>
                            <div class="reason-text">
                                <i class="fas fa-comment"></i> 
                                <strong>Reason:</strong> <?php echo htmlspecialchars($booking['reason']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($booking['booking_status'] === 'pending' || $booking['booking_status'] === 'approved'): ?>
                            <div style="margin-top: 15px; text-align: right;">
                                <a href="cancel_booking.php?booking_id=<?php echo $booking['id']; ?>" 
                                   class="btn btn-danger"
                                   onclick="return confirm('Are you sure you want to cancel this booking?');">
                                    <i class="fas fa-times"></i> Cancel Booking
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
