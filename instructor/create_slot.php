<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Get date from URL parameter if available
$preselectedDate = $_GET['date'] ?? '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $slotDate = $_POST['slot_date'] ?? '';
    $startTime = $_POST['start_time'] ?? '';
    $durationMinutes = (int)($_POST['duration_minutes'] ?? 20);
    $maxStudents = (int)($_POST['max_students'] ?? 1);
    $notes = $_POST['notes'] ?? '';
    
    // Calculate end time automatically
    $endTime = '';
    if ($startTime && $durationMinutes && $maxStudents) {
        $start = new DateTime("2000-01-01 $startTime");
        $totalMinutes = $durationMinutes * $maxStudents;
        $end = $start->add(new DateInterval("PT{$totalMinutes}M"));
        $endTime = $end->format('H:i');
    }

    // Basic validation
    $errors = [];
    
    if (empty($slotDate)) {
        $errors[] = "Slot date is required";
    }
    
    if (empty($startTime)) {
        $errors[] = "Start time is required";
    }
    
    if (empty($durationMinutes)) {
        $errors[] = "Duration is required";
    }
    
    if ($durationMinutes < 5 || $durationMinutes > 120) {
        $errors[] = "Duration must be between 5 and 120 minutes";
    }
    
    if ($maxStudents < 1 || $maxStudents > 10) {
        $errors[] = "Maximum students must be between 1 and 10";
    }
    
    // Check if calculated end time is reasonable
    if ($startTime && $durationMinutes && $maxStudents) {
        $totalMinutes = $durationMinutes * $maxStudents;
        if ($totalMinutes > 480) { // Max 8 hours total
            $errors[] = "Total consultation time ({$totalMinutes} minutes) is too long. Maximum 8 hours allowed.";
        }
    }
    
    // Check if slot date is not in the past
    if (strtotime($slotDate) < strtotime(date('Y-m-d'))) {
        $errors[] = "Slot date cannot be in the past";
    }
    
    // If no errors, insert into database
    if (empty($errors)) {
        try {
            // Check if table exists first
            $tableCheck = $conn->query("SHOW TABLES LIKE 'consultation_slots'");
            if ($tableCheck->num_rows == 0) {
                // Create table if it doesn't exist
                $createTable = "CREATE TABLE `consultation_slots` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `instructor_id` int(11) NOT NULL,
                  `slot_date` date NOT NULL,
                  `start_time` time NOT NULL,
                  `end_time` time NOT NULL,
                  `duration_minutes` int(11) DEFAULT 20,
                  `max_students` int(11) DEFAULT 1,
                  `booked_students` int(11) DEFAULT 0,
                  `status` enum('open','full','closed') DEFAULT 'open',
                  `notes` text DEFAULT NULL,
                  `created_at` timestamp NULL DEFAULT current_timestamp(),
                  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
                
                $conn->query($createTable);
            }
            
            $insertQuery = $conn->prepare("INSERT INTO consultation_slots 
                (instructor_id, slot_date, start_time, end_time, duration_minutes, max_students, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            $insertQuery->bind_param("issssis", 
                $userId, $slotDate, $startTime, $endTime, $durationMinutes, 
                $maxStudents, $notes);
            
            if ($insertQuery->execute()) {
                $success = "Consultation slot created successfully!";
                // Clear form data
                $_POST = [];
            } else {
                $errors[] = "Failed to create consultation slot. Error: " . $insertQuery->error;
            }
            
            $insertQuery->close();
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Consultation Slot - ConSlot</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .page-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
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
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-help {
            font-size: 14px;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
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
        
        .btn-secondary:hover {
            background: #5a6268;
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
        
        .time-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .duration-display {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 6px;
            margin-top: 10px;
            font-size: 14px;
            color: #495057;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <!-- Navigation -->
        <div class="navigation">
            <a href="dashboard.php">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <a href="debug.php" style="margin-left: 20px;">
                <i class="fas fa-bug"></i> Debug Info
            </a>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-calendar-plus"></i> Create Consultation Slot</h1>
            <p>Set up a new time slot for student consultations</p>
        </div>

        <!-- Success/Error Messages -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Create Slot Form -->
        <div class="form-card">
            <form method="POST" action="">
                <!-- Slot Date -->
                <div class="form-group">
                    <label class="form-label" for="slot_date">
                        <i class="fas fa-calendar"></i> Slot Date *
                    </label>
                    <input type="date" 
                           id="slot_date" 
                           name="slot_date" 
                           class="form-control" 
                           required
                           min="<?php echo date('Y-m-d'); ?>"
                           value="<?php echo htmlspecialchars($preselectedDate ?: ($_POST['slot_date'] ?? '')); ?>">
                    <div class="form-help">Select the date for this consultation slot</div>
                </div>

                <!-- Consultation Duration and Maximum Students -->
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="duration_minutes">
                            <i class="fas fa-hourglass-half"></i> Consultation Duration (minutes) *
                        </label>
                        <input type="number" 
                               id="duration_minutes" 
                               name="duration_minutes" 
                               class="form-control" 
                               min="5" 
                               max="120" 
                               step="5"
                               required
                               value="<?php echo htmlspecialchars($_POST['duration_minutes'] ?? '20'); ?>">
                        <div class="form-help">Length of each individual consultation (5-120 minutes)</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="max_students">
                            <i class="fas fa-users"></i> Maximum Students *
                        </label>
                        <input type="number" 
                               id="max_students" 
                               name="max_students" 
                               class="form-control" 
                               min="1" 
                               max="10" 
                               required
                               value="<?php echo htmlspecialchars($_POST['max_students'] ?? '1'); ?>">
                        <div class="form-help">How many students can book this slot</div>
                    </div>
                </div>

                <!-- Time Slot -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-clock"></i> Time Slot *
                    </label>
                    <div class="time-inputs">
                        <div>
                            <input type="time" 
                                   id="start_time" 
                                   name="start_time" 
                                   class="form-control" 
                                   required
                                   value="<?php echo htmlspecialchars($_POST['start_time'] ?? ''); ?>">
                            <div class="form-help">Start Time</div>
                        </div>
                        <div>
                            <input type="time" 
                                   id="end_time" 
                                   name="end_time" 
                                   class="form-control" 
                                   readonly
                                   value="">
                            <div class="form-help">End Time (Auto-calculated)</div>
                        </div>
                    </div>
                    <div id="duration-display" class="duration-display">
                        <span id="duration-text">Please set duration, max students, and start time</span>
                    </div>
                </div>

                <!-- Notes -->
                <div class="form-group">
                    <label class="form-label" for="notes">
                        <i class="fas fa-sticky-note"></i> Notes (Optional)
                    </label>
                    <textarea id="notes" 
                              name="notes" 
                              class="form-control" 
                              rows="4" 
                              placeholder="Add any additional information about this consultation slot..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                    <div class="form-help">Any additional information for students</div>
                </div>

                <!-- Form Actions -->
                <div style="display: flex; gap: 15px; justify-content: flex-end;">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Slot
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Calculate and display end time automatically
        function calculateEndTime() {
            const startTime = document.getElementById('start_time').value;
            const duration = document.getElementById('duration_minutes').value;
            const maxStudents = document.getElementById('max_students').value;
            const endTimeInput = document.getElementById('end_time');
            const durationDisplay = document.getElementById('duration-text');
            
            if (startTime && duration && maxStudents) {
                const start = new Date(`2000-01-01T${startTime}`);
                const totalMinutes = parseInt(duration) * parseInt(maxStudents);
                
                // Calculate end time
                const end = new Date(start.getTime() + (totalMinutes * 60000));
                const endTime = end.toTimeString().slice(0, 5);
                
                // Set end time field
                endTimeInput.value = endTime;
                
                // Display calculation details
                const hours = Math.floor(totalMinutes / 60);
                const minutes = totalMinutes % 60;
                
                let totalTimeText = '';
                if (hours > 0) {
                    totalTimeText = `${hours} hour${hours > 1 ? 's' : ''}`;
                    if (minutes > 0) {
                        totalTimeText += ` ${minutes} minute${minutes > 1 ? 's' : ''}`;
                    }
                } else {
                    totalTimeText = `${minutes} minute${minutes > 1 ? 's' : ''}`;
                }
                
                durationDisplay.innerHTML = `
                    <strong>Per Student:</strong> ${duration} minutes<br>
                    <strong>Total Slot:</strong> ${totalTimeText}<br>
                    <strong>End Time:</strong> ${endTime}<br>
                    <strong>Max Students:</strong> ${maxStudents}
                `;
            } else {
                endTimeInput.value = '';
                durationDisplay.textContent = 'Please set duration and max students';
            }
        }
        
        // Add event listeners for all relevant inputs
        document.getElementById('start_time').addEventListener('change', calculateEndTime);
        document.getElementById('duration_minutes').addEventListener('change', calculateEndTime);
        document.getElementById('max_students').addEventListener('change', calculateEndTime);
        
        // Initialize calculation
        calculateEndTime();
        
        // Set minimum date to today
        document.getElementById('slot_date').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>

<?php
// Close database connection
if (isset($conn)) {
    $conn->close();
}
?>
