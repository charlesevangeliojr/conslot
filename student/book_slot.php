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

// Get slot ID from URL
$slotId = isset($_GET['slot_id']) ? intval($_GET['slot_id']) : 0;

$error = '';
$success = '';

// Get slot details
$slot = null;
if ($slotId > 0) {
    try {
        $slotQuery = $conn->prepare("SELECT 
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
            WHERE cs.id = ?
            AND cs.status = 'open'
            AND cs.booked_students < cs.max_students");
        
        if ($slotQuery) {
            $slotQuery->bind_param("i", $slotId);
            $slotQuery->execute();
            $result = $slotQuery->get_result();
            $slot = $result->fetch_assoc();
            $slotQuery->close();
        }
        
        // Check if student already booked this slot
        if ($slot) {
            $checkQuery = $conn->prepare("SELECT id FROM consultation_bookings 
                WHERE slot_id = ? AND student_id = ? AND booking_status IN ('pending', 'approved')");
            if ($checkQuery) {
                $checkQuery->bind_param("ii", $slotId, $studentId);
                $checkQuery->execute();
                $existingBooking = $checkQuery->get_result()->fetch_assoc();
                $checkQuery->close();
                
                if ($existingBooking) {
                    $error = "You have already booked this slot.";
                }
            }
        } else {
            $error = "This slot is no longer available or is fully booked.";
        }
    } catch (Exception $e) {
        error_log("Slot query error: " . $e->getMessage());
        $error = "An error occurred while loading the slot details.";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_slot'])) {
    $slotId = intval($_POST['slot_id']);
    $reason = trim($_POST['reason'] ?? '');
    
    // Validate
    if (empty($reason)) {
        $error = "Please provide a reason for your consultation.";
    } elseif (strlen($reason) < 10) {
        $error = "Reason must be at least 10 characters long.";
    } else {
        try {
            // Check if slot is still available
            $checkSlotQuery = $conn->prepare("SELECT booked_students, max_students FROM consultation_slots 
                WHERE id = ? AND status = 'open' AND booked_students < max_students FOR UPDATE");
            
            if ($checkSlotQuery) {
                $checkSlotQuery->bind_param("i", $slotId);
                $checkSlotQuery->execute();
                $slotData = $checkSlotQuery->get_result()->fetch_assoc();
                $checkSlotQuery->close();
                
                if (!$slotData) {
                    $error = "This slot is no longer available.";
                } else {
                    // Insert booking
                    $insertQuery = $conn->prepare("INSERT INTO consultation_bookings 
                        (slot_id, student_id, reason, booking_status) 
                        VALUES (?, ?, ?, 'pending')");
                    
                    if ($insertQuery) {
                        $insertQuery->bind_param("iis", $slotId, $studentId, $reason);
                        
                        if ($insertQuery->execute()) {
                            // Update slot booked count
                            $updateSlot = $conn->prepare("UPDATE consultation_slots 
                                SET booked_students = booked_students + 1,
                                    status = CASE WHEN booked_students + 1 >= max_students THEN 'full' ELSE status END
                                WHERE id = ?");
                            
                            if ($updateSlot) {
                                $updateSlot->bind_param("i", $slotId);
                                $updateSlot->execute();
                                $updateSlot->close();
                            }
                            
                            $success = "Your consultation has been booked successfully! Please wait for instructor approval.";
                            $slot = null; // Clear slot to prevent rebooking
                        } else {
                            $error = "Failed to book consultation. Please try again.";
                        }
                        
                        $insertQuery->close();
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Booking error: " . $e->getMessage());
            $error = "An error occurred while booking. Please try again.";
        }
    }
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
    <title>Book Slot - ConSlot</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .booking-form-container {
            max-width: 600px;
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
        
        .slot-summary {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .slot-summary h3 {
            margin: 0 0 15px 0;
            color: #333;
        }
        
        .detail-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            color: #495057;
        }
        
        .detail-row i {
            color: #667eea;
            width: 20px;
        }
        
        .form-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
            font-family: inherit;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
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
        
        .form-help {
            font-size: 14px;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .navigation {
            margin-bottom: 20px;
        }
        
        .navigation a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="booking-form-container">
        <!-- Navigation -->
        <div class="navigation">
            <a href="book_consultation.php">
                <i class="fas fa-arrow-left"></i> Back to Available Slots
            </a>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-calendar-plus"></i> Book Consultation</h1>
            <p>Complete your booking by providing a reason for consultation</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> 
                <?php echo htmlspecialchars($success); ?>
                <div style="margin-top: 15px;">
                    <a href="book_consultation.php" class="btn btn-primary">
                        <i class="fas fa-list"></i> View My Bookings
                    </a>
                </div>
            </div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> 
                <?php echo htmlspecialchars($error); ?>
                <div style="margin-top: 15px;">
                    <a href="book_consultation.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Slots
                    </a>
                </div>
            </div>
        <?php elseif ($slot): ?>
            <!-- Slot Summary -->
            <div class="slot-summary">
                <h3><?php echo htmlspecialchars($slot['instructor_first_name'] . ' ' . $slot['instructor_last_name']); ?></h3>
                <?php if ($slot['specialization']): ?>
                    <div class="detail-row">
                        <i class="fas fa-briefcase"></i>
                        <span><?php echo htmlspecialchars($slot['specialization']); ?></span>
                    </div>
                <?php endif; ?>
                <div class="detail-row">
                    <i class="fas fa-calendar"></i>
                    <span><?php echo date('F j, Y (l)', strtotime($slot['slot_date'])); ?></span>
                </div>
                <div class="detail-row">
                    <i class="fas fa-clock"></i>
                    <span><?php echo date('g:i A', strtotime($slot['start_time'])) . ' - ' . date('g:i A', strtotime($slot['end_time'])); ?></span>
                </div>
                <div class="detail-row">
                    <i class="fas fa-hourglass-half"></i>
                    <span><?php echo $slot['duration_minutes']; ?> minutes consultation</span>
                </div>
                <?php if ($slot['notes']): ?>
                    <div class="detail-row">
                        <i class="fas fa-sticky-note"></i>
                        <span><?php echo htmlspecialchars($slot['notes']); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Booking Form -->
            <div class="form-card">
                <form method="POST" action="">
                    <input type="hidden" name="slot_id" value="<?php echo $slot['id']; ?>">
                    
                    <div class="form-group">
                        <label class="form-label" for="reason">
                            <i class="fas fa-comment"></i> Reason for Consultation *
                        </label>
                        <textarea 
                            id="reason" 
                            name="reason" 
                            class="form-control" 
                            placeholder="Please describe why you need this consultation. Be specific about what you want to discuss or ask..."
                            required
                            minlength="10"
                            maxlength="500"
                        ></textarea>
                        <div class="form-help">
                            Minimum 10 characters. This helps the instructor prepare for your consultation.
                        </div>
                    </div>

                    <div style="display: flex; gap: 15px; justify-content: flex-end;">
                        <a href="book_consultation.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" name="book_slot" class="btn btn-primary">
                            <i class="fas fa-check"></i> Confirm Booking
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h3>Slot Not Available</h3>
                <p>This slot is no longer available or does not exist.</p>
                <a href="book_consultation.php" class="btn btn-primary" style="margin-top: 20px;">
                    <i class="fas fa-arrow-left"></i> View Available Slots
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
