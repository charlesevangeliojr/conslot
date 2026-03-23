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

// Get booking ID from URL
$bookingId = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

$error = '';
$success = '';

if ($bookingId > 0) {
    try {
        // Verify the booking belongs to this student and can be cancelled
        $checkQuery = $conn->prepare("SELECT cb.id, cb.slot_id, cb.booking_status 
            FROM consultation_bookings cb
            WHERE cb.id = ? AND cb.student_id = ? AND cb.booking_status IN ('pending', 'approved')");
        
        if ($checkQuery) {
            $checkQuery->bind_param("ii", $bookingId, $studentId);
            $checkQuery->execute();
            $booking = $checkQuery->get_result()->fetch_assoc();
            $checkQuery->close();
            
            if ($booking) {
                // Update booking status to cancelled
                $updateQuery = $conn->prepare("UPDATE consultation_bookings 
                    SET booking_status = 'cancelled' 
                    WHERE id = ?");
                
                if ($updateQuery) {
                    $updateQuery->bind_param("i", $bookingId);
                    
                    if ($updateQuery->execute()) {
                        // Decrease booked count in slot
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
                        
                        $success = "Your booking has been cancelled successfully.";
                    } else {
                        $error = "Failed to cancel booking. Please try again.";
                    }
                    
                    $updateQuery->close();
                }
            } else {
                $error = "Booking not found or cannot be cancelled.";
            }
        }
    } catch (Exception $e) {
        error_log("Cancel booking error: " . $e->getMessage());
        $error = "An error occurred while cancelling your booking.";
    }
} else {
    $error = "Invalid booking ID.";
}

// Close connection
if (isset($conn)) {
    $conn->close();
}

// Redirect back to bookings page with message
if ($success) {
    header("Location: book_consultation.php?message=" . urlencode($success) . "&type=success");
} else {
    header("Location: book_consultation.php?message=" . urlencode($error) . "&type=error");
}
exit;
?>
