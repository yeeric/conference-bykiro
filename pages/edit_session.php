<?php
/**
 * Edit Session Page
 * Modify session date, time, or location
 * Requirements: 12.1, 12.2
 */

require_once '../config/database.php';
require_once '../includes/functions.php';
include '../includes/header.php';

// Get database connection
$pdo = getDBConnection();

// Initialize variables
$selectedSessionId = $_GET['session_id'] ?? null;
$sessionDetails = null;
$successMessage = '';
$errorMessage = '';

// Fetch all sessions for dropdown
try {
    $sessionsStmt = $pdo->query("
        SELECT SessionID, SessionName, Date, StartTime
        FROM Session
        ORDER BY Date, StartTime
    ");
    $sessions = $sessionsStmt->fetchAll();
} catch (PDOException $e) {
    $errorMessage = 'Error loading sessions. Please try again.';
    error_log("Error fetching sessions: " . $e->getMessage());
    $sessions = [];
}

// If a session is selected, fetch its current details
if ($selectedSessionId) {
    try {
        $detailsStmt = $pdo->prepare("
            SELECT SessionName, Date, StartTime, EndTime, RoomLocation
            FROM Session
            WHERE SessionID = :session_id
        ");
        $detailsStmt->execute(['session_id' => $selectedSessionId]);
        $sessionDetails = $detailsStmt->fetch();
        
        if (!$sessionDetails) {
            $errorMessage = 'Session not found.';
            $selectedSessionId = null;
        }
    } catch (PDOException $e) {
        $errorMessage = 'Error loading session details. Please try again.';
        error_log("Error fetching session details: " . $e->getMessage());
        $selectedSessionId = null;
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $sessionId = $_POST['session_id'] ?? '';
    $date = trim($_POST['date'] ?? '');
    $startTime = trim($_POST['start_time'] ?? '');
    $endTime = trim($_POST['end_time'] ?? '');
    $roomLocation = trim($_POST['room_location'] ?? '');
    
    // Validate required fields
    if (empty($sessionId) || empty($date) || empty($startTime) || empty($endTime) || empty($roomLocation)) {
        $errorMessage = 'All fields are required.';
    } elseif ($endTime <= $startTime) {
        $errorMessage = 'End time must be after start time.';
    } else {
        try {
            // Update session
            $updateStmt = $pdo->prepare("
                UPDATE Session
                SET Date = :date, StartTime = :start_time, EndTime = :end_time, RoomLocation = :room_location
                WHERE SessionID = :session_id
            ");
            $updateStmt->execute([
                'date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'room_location' => $roomLocation,
                'session_id' => $sessionId
            ]);
            
            // Fetch updated session details
            $detailsStmt = $pdo->prepare("
                SELECT SessionName, Date, StartTime, EndTime, RoomLocation
                FROM Session
                WHERE SessionID = :session_id
            ");
            $detailsStmt->execute(['session_id' => $sessionId]);
            $sessionDetails = $detailsStmt->fetch();
            
            $successMessage = "Session '" . htmlspecialchars($sessionDetails['SessionName']) . "' updated successfully.";
            $selectedSessionId = $sessionId;
        } catch (PDOException $e) {
            $errorMessage = 'Update failed. Please try again.';
            error_log("Session update error: " . $e->getMessage());
        }
    }
}
?>

<div class="container">
    <h2>Edit Session</h2>
    <p>Select a session to modify its date, time, or location.</p>
    
    <?php if ($successMessage): ?>
        <?php displaySuccess($successMessage); ?>
    <?php endif; ?>
    
    <?php if ($errorMessage): ?>
        <?php displayError($errorMessage); ?>
    <?php endif; ?>
    
    <div class="form-section">
        <form method="GET" action="edit_session.php" class="selection-form">
            <div class="form-group">
                <label for="session_id">Select Session:</label>
                <select id="session_id" name="session_id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Select a Session --</option>
                    <?php foreach ($sessions as $session): ?>
                        <option value="<?php echo $session['SessionID']; ?>" 
                                <?php echo ($selectedSessionId == $session['SessionID']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($session['SessionName']); ?> 
                            (<?php echo formatDate($session['Date']); ?> at <?php echo formatTime($session['StartTime']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
    
    <?php if ($sessionDetails): ?>
        <div class="results-section">
            <h3>Current Session Details</h3>
            <div class="session-info">
                <p><strong>Session Name:</strong> <?php echo htmlspecialchars($sessionDetails['SessionName']); ?></p>
                <p><strong>Date:</strong> <?php echo formatDate($sessionDetails['Date']); ?></p>
                <p><strong>Start Time:</strong> <?php echo formatTime($sessionDetails['StartTime']); ?></p>
                <p><strong>End Time:</strong> <?php echo formatTime($sessionDetails['EndTime']); ?></p>
                <p><strong>Room Location:</strong> <?php echo htmlspecialchars($sessionDetails['RoomLocation']); ?></p>
            </div>
            
            <h3>Edit Session Details</h3>
            <form method="POST" action="edit_session.php" class="attendee-form">
                <input type="hidden" name="session_id" value="<?php echo $selectedSessionId; ?>">
                
                <div class="form-group">
                    <label for="date">Date: <span class="required">*</span></label>
                    <input type="date" id="date" name="date" 
                           value="<?php echo htmlspecialchars($sessionDetails['Date']); ?>" 
                           required class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="start_time">Start Time: <span class="required">*</span></label>
                    <input type="time" id="start_time" name="start_time" 
                           value="<?php echo htmlspecialchars($sessionDetails['StartTime']); ?>" 
                           required class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="end_time">End Time: <span class="required">*</span></label>
                    <input type="time" id="end_time" name="end_time" 
                           value="<?php echo htmlspecialchars($sessionDetails['EndTime']); ?>" 
                           required class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="room_location">Room Location: <span class="required">*</span></label>
                    <input type="text" id="room_location" name="room_location" 
                           value="<?php echo htmlspecialchars($sessionDetails['RoomLocation']); ?>" 
                           required class="form-control">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Session</button>
                    <a href="schedule.php" class="btn btn-secondary">View Schedule</a>
                </div>
            </form>
        </div>
    <?php elseif ($selectedSessionId): ?>
        <p class="empty-state">Session not found or no longer available.</p>
    <?php else: ?>
        <p class="info-text">Please select a session from the dropdown above to view and edit its details.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
