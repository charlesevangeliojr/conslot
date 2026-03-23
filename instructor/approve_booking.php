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

$instructorId = $_SESSION['user_id'];

// Get parameters
$bookingId = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

$message = '';
$type = 'error';

if ($bookingId > 0 && in_array($action, ['approve', 'reject'])) {
    try {
        // Verify the booking belongs to a slot owned by this instructor
        $checkQuery = $conn->prepare("SELECT cb.id, cb.booking_status, cb.student_id, cs.id as slot_id, cs.slot_date
            FROM consultation_bookings cb
            JOIN consultation_slots cs ON cb.slot_id = cs.id
            WHERE cb.id = ? AND cs.instructor_id = ? AND cb.booking_status = 'pending'");
        
        if ($checkQuery) {
            $checkQuery->bind_param("ii", $bookingId, $instructorId);
            $checkQuery->execute();
            $booking = $checkQuery->get_result()->fetch_assoc();
            $checkQuery->close();
            
            if ($booking) {
                $newStatus = ($action === 'approve') ? 'approved' : 'rejected';
                
                // Update booking status
                $updateQuery = $conn->prepare("UPDATE consultation_bookings 
                    SET booking_status = ? 
                    WHERE id = ?");
                
                if ($updateQuery) {
                    $updateQuery->bind_param("si", $newStatus, $bookingId);
                    
                    if ($updateQuery->execute()) {
                        // If rejected, decrease booked count in slot
                        if ($action === 'reject') {
                            $slotId = $booking['slot_id'];
                            $updateSlot = $conn->prepare("UPDATE consultation_slots 
                                SET booked_students = GREATEST(0, booked_students - 1),
                                    status = CASE WHEN status = 'full' THEN 'open' ELSE status END
                                WHERE id = ?");
                            
                            if ($updateSlot) {
                                $updateSlot->bind_param("i", $slotId);
                                $updateSlot->execute();
                                $updateSlot->close();
                            }
                        }
                        
                        $message = "Booking " . ($action === 'approve' ? "approved" : "rejected") . " successfully!";
                        $type = "success";
                    } else {
                        $message = "Failed to update booking status.";
                    }
                    
                    $updateQuery->close();
                }
            } else {
                $message = "Booking not found or already processed.";
            }
        }
    } catch (Exception $e) {
        error_log("Approve/reject booking error: " . $e->getMessage());
        $message = "An error occurred while processing the booking.";
    }
} else {
    $message = "Invalid request parameters.";
}

// Close connection
if (isset($conn)) {
    $conn->close();
}

// Redirect back to dashboard
header("Location: dashboard.php?message=" . urlencode($message) . "&type=" . $type);
exit;
?>
