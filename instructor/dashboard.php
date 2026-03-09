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

// Initialize stats to prevent errors
$stats = [
    'total_consultations' => 0,
    'completed_consultations' => 0,
    'pending_consultations' => 0,
    'total_reviews' => 0,
    'average_rating' => 0
];

// Get instructor statistics with error handling
try {
    $statsQuery = $conn->prepare("SELECT 
        COUNT(c.id) as total_consultations,
        COUNT(CASE WHEN c.status = 'completed' THEN 1 END) as completed_consultations,
        COUNT(CASE WHEN c.status = 'pending' THEN 1 END) as pending_consultations,
        COUNT(r.id) as total_reviews,
        AVG(r.rating) as average_rating
        FROM instructors i 
        LEFT JOIN consultations c ON i.id = c.instructor_id 
        LEFT JOIN reviews r ON c.id = r.consultation_id 
        WHERE i.id = ?");
    if ($statsQuery) {
        $statsQuery->bind_param("i", $userId);
        $statsQuery->execute();
        $result = $statsQuery->get_result()->fetch_assoc();
        if ($result) {
            $stats = $result;
        }
        $statsQuery->close();
    }
} catch (Exception $e) {
    error_log("Instructor stats query error: " . $e->getMessage());
}

// Initialize recent consultations array
$recentConsultations = [];

// Get recent consultations with error handling
try {
    $recentQuery = $conn->prepare("SELECT c.*, 
        CONCAT(s.first_name, ' ', s.last_name) as student_name,
        s.course
        FROM consultations c 
        JOIN students s ON c.student_id = s.id 
        WHERE c.instructor_id = ? 
        ORDER BY c.created_at DESC 
        LIMIT 5");
    if ($recentQuery) {
        $recentQuery->bind_param("i", $userId);
        $recentQuery->execute();
        $recentConsultations = $recentQuery->get_result();
        $recentQuery->close();
    }
} catch (Exception $e) {
    error_log("Instructor recent consultations query error: " . $e->getMessage());
}
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
                <div class="stat-number"><?php echo $stats['total_consultations']; ?></div>
                <div class="stat-label">Total Consultations</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['completed_consultations']; ?></div>
                <div class="stat-label">Completed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['pending_consultations']; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_reviews']; ?></div>
                <div class="stat-label">Reviews</div>
                <?php if ($stats['average_rating']): ?>
                    <div class="rating">
                        <?php echo number_format($stats['average_rating'], 1); ?> 
                        <i class="fas fa-star"></i>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Consultations -->
        <div class="recent-consultations">
            <h3>Recent Consultations</h3>
            <?php if ($recentConsultations->num_rows > 0): ?>
                <?php while ($consultation = $recentConsultations->fetch_assoc()): ?>
                    <div class="consultation-item">
                        <div>
                            <strong><?php echo htmlspecialchars($consultation['title']); ?></strong>
                            <br>
                            <small>with <?php echo htmlspecialchars($consultation['student_name']); ?> (<?php echo htmlspecialchars($consultation['student_id']); ?>)</small>
                            <br>
                            <small><?php echo date('M d, Y', strtotime($consultation['consultation_date'])); ?> at <?php echo date('h:i A', strtotime($consultation['consultation_time'])); ?></small>
                        </div>
                        <span class="status-badge status-<?php echo $consultation['status']; ?>">
                            <?php echo ucfirst($consultation['status']); ?>
                        </span>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No consultations yet. Students will start booking consultations soon!</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
$statsQuery->close();
$recentQuery->close();
$conn->close();
?>
