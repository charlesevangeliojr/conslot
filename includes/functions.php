<?php
/**
 * ConSlot Helper Functions
 * DCC Consultation Booking Portal
 */

// Global database instance
global $database;

/**
 * Sanitize input data
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Format date for display
 */
function formatDate($date, $format = DISPLAY_DATE_FORMAT) {
    return date($format, strtotime($date));
}

/**
 * Format time for display
 */
function formatTime($time, $format = DISPLAY_TIME_FORMAT) {
    return date($format, strtotime($time));
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime, $format = DISPLAY_DATE_FORMAT . ' ' . DISPLAY_TIME_FORMAT) {
    return date($format, strtotime($datetime));
}

/**
 * Get relative time (e.g., "2 hours ago")
 */
function getRelativeTime($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;

    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' days ago';
    } else {
        return formatDate($datetime);
    }
}

/**
 * Check if date is in the past
 */
function isPastDate($date) {
    return strtotime($date) < strtotime(date('Y-m-d'));
}

/**
 * Check if time has passed for today
 */
function isPastTime($date, $time) {
    if ($date == date('Y-m-d')) {
        return strtotime($time) < strtotime(date('H:i'));
    }
    return false;
}

/**
 * Get day of week
 */
function getDayOfWeek($date) {
    return date('l', strtotime($date));
}

/**
 * Calculate time difference in minutes
 */
function getTimeDifference($start_time, $end_time) {
    $start = strtotime($start_time);
    $end = strtotime($end_time);
    return ($end - $start) / 60;
}

/**
 * Generate pagination links
 */
function generatePagination($current_page, $total_pages, $base_url) {
    if ($total_pages <= 1) return '';

    $pagination = '<div class="pagination">';
    
    // Previous button
    if ($current_page > 1) {
        $pagination .= '<a href="' . $base_url . '?page=' . ($current_page - 1) . '" class="pagination-link">&laquo; Previous</a>';
    }

    // Page numbers
    $start_page = max(1, $current_page - floor(MAX_PAGE_LINKS / 2));
    $end_page = min($total_pages, $start_page + MAX_PAGE_LINKS - 1);
    
    if ($start_page > 1) {
        $pagination .= '<a href="' . $base_url . '?page=1" class="pagination-link">1</a>';
        if ($start_page > 2) {
            $pagination .= '<span class="pagination-ellipsis">...</span>';
        }
    }

    for ($i = $start_page; $i <= $end_page; $i++) {
        $class = ($i == $current_page) ? 'pagination-link active' : 'pagination-link';
        $pagination .= '<a href="' . $base_url . '?page=' . $i . '" class="' . $class . '">' . $i . '</a>';
    }

    if ($end_page < $total_pages) {
        if ($end_page < $total_pages - 1) {
            $pagination .= '<span class="pagination-ellipsis">...</span>';
        }
        $pagination .= '<a href="' . $base_url . '?page=' . $total_pages . '" class="pagination-link">' . $total_pages . '</a>';
    }

    // Next button
    if ($current_page < $total_pages) {
        $pagination .= '<a href="' . $base_url . '?page=' . ($current_page + 1) . '" class="pagination-link">Next &raquo;</a>';
    }

    $pagination .= '</div>';
    return $pagination;
}

/**
 * Flash message functions
 */
function setFlashMessage($type, $message) {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = ['type' => $type, 'message' => $message];
}

function getFlashMessages() {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

function displayFlashMessages() {
    $messages = getFlashMessages();
    $output = '';
    
    foreach ($messages as $message) {
        $output .= '<div class="alert alert-' . $message['type'] . ' alert-dismissible fade show" role="alert">';
        $output .= htmlspecialchars($message['message']);
        $output .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        $output .= '</div>';
    }
    
    return $output;
}

/**
 * Redirect with message
 */
function redirect($url, $type = null, $message = null) {
    if ($type && $message) {
        setFlashMessage($type, $message);
    }
    header('Location: ' . $url);
    exit();
}

/**
 * Check if user has permission
 */
function hasPermission($role) {
    global $current_user;
    return $current_user && $current_user['role'] === $role;
}

/**
 * Get user statistics
 */
function getUserStats($user_id) {
    global $database;
    
    $stats = [
        'total_bookings' => 0,
        'confirmed_bookings' => 0,
        'completed_bookings' => 0,
        'cancelled_bookings' => 0
    ];
    
    $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
            FROM bookings 
            WHERE student_id = ?";
    
    $result = $database->getSingle($sql, [$user_id]);
    
    if ($result) {
        $stats['total_bookings'] = (int)$result['total'];
        $stats['confirmed_bookings'] = (int)$result['confirmed'];
        $stats['completed_bookings'] = (int)$result['completed'];
        $stats['cancelled_bookings'] = (int)$result['cancelled'];
    }
    
    return $stats;
}

/**
 * Get instructor statistics
 */
function getInstructorStats($instructor_id) {
    global $database;
    
    $stats = [
        'total_slots' => 0,
        'active_slots' => 0,
        'total_bookings' => 0,
        'confirmed_bookings' => 0
    ];
    
    // Slot statistics
    $slot_sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_active = 1 AND date >= CURDATE() THEN 1 ELSE 0 END) as active
                 FROM consultation_slots 
                 WHERE instructor_id = ?";
    
    $slot_result = $database->getSingle($slot_sql, [$instructor_id]);
    
    if ($slot_result) {
        $stats['total_slots'] = (int)$slot_result['total'];
        $stats['active_slots'] = (int)$slot_result['active'];
    }
    
    // Booking statistics
    $booking_sql = "SELECT 
                       COUNT(*) as total,
                       SUM(CASE WHEN b.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed
                    FROM bookings b
                    JOIN consultation_slots cs ON b.slot_id = cs.id
                    WHERE cs.instructor_id = ?";
    
    $booking_result = $database->getSingle($booking_sql, [$instructor_id]);
    
    if ($booking_result) {
        $stats['total_bookings'] = (int)$booking_result['total'];
        $stats['confirmed_bookings'] = (int)$booking_result['confirmed'];
    }
    
    return $stats;
}

/**
 * Log activity
 */
function logActivity($user_id, $action, $details = '') {
    global $database;
    
    $sql = "INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)";
    
    $params = [
        $user_id,
        $action,
        $details,
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ];
    
    // Note: This requires an activity_logs table to be created
    // For now, we'll just log to error log for debugging
    if (DEBUG_MODE) {
        error_log("Activity: User $user_id - $action - $details");
    }
}

/**
 * Validate CSRF token
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateToken();
    }
    return $_SESSION['csrf_token'];
}
?>
