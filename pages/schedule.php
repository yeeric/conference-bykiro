<?php
/**
 * Conference Schedule Page
 * Display all sessions scheduled for a specific date + edit session tab
 * Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 12.1, 12.2
 */

require_once '../config/database.php';
require_once '../includes/functions.php';
include '../includes/header.php';

// Get database connection
$pdo = getDBConnection();

// ── Handle edit session form submission ──
$successMessage = '';
$errorMessage = '';
$showEditTab = false;
$selectedSessionId = null;
$sessionDetails = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $showEditTab = true;
    $sessionId = $_POST['session_id'] ?? '';
    $date = trim($_POST['date'] ?? '');
    $startTime = trim($_POST['start_time'] ?? '');
    $endTime = trim($_POST['end_time'] ?? '');
    $roomLocation = trim($_POST['room_location'] ?? '');

    if (empty($sessionId) || empty($date) || empty($startTime) || empty($endTime) || empty($roomLocation)) {
        $errorMessage = 'All fields are required.';
        $selectedSessionId = $sessionId;
    } elseif ($endTime <= $startTime) {
        $errorMessage = 'End time must be after start time.';
        $selectedSessionId = $sessionId;
    } else {
        try {
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
            $selectedSessionId = $sessionId;
        }
    }
}

// Handle edit tab via GET (session selection or query param)
if (isset($_GET['tab']) && $_GET['tab'] === 'edit') {
    $showEditTab = true;
}
if (isset($_GET['session_id'])) {
    $showEditTab = true;
    $selectedSessionId = $_GET['session_id'];
}

// If a session is selected for editing, fetch its details
if ($selectedSessionId && !$sessionDetails) {
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

// ── Fetch schedule data ──
$selectedDate = isset($_GET['date']) ? $_GET['date'] : null;

$datesStmt = $pdo->query("SELECT DISTINCT Date FROM Session ORDER BY Date");
$dates = $datesStmt->fetchAll();

$scheduleSessions = [];
if ($selectedDate) {
    $sessionsStmt = $pdo->prepare("
        SELECT SessionName, StartTime, EndTime, RoomLocation
        FROM Session
        WHERE Date = :date
        ORDER BY StartTime, SessionName
    ");
    $sessionsStmt->execute(['date' => $selectedDate]);
    $scheduleSessions = $sessionsStmt->fetchAll();
}

// Fetch all sessions for edit dropdown
try {
    $allSessionsStmt = $pdo->query("
        SELECT SessionID, SessionName, Date, StartTime
        FROM Session
        ORDER BY Date, StartTime
    ");
    $allSessions = $allSessionsStmt->fetchAll();
} catch (PDOException $e) {
    $allSessions = [];
}
?>

<div class="container">
    <h2>Conference Schedule</h2>
    <p>View sessions by date or edit session details.</p>

    <div class="tabs">
        <button class="tab-btn <?php echo !$showEditTab ? 'active' : ''; ?>" data-tab="schedule">
            Schedule
        </button>
        <button class="tab-btn tab-btn--register <?php echo $showEditTab ? 'active' : ''; ?>" data-tab="edit">
            Edit Session
        </button>
    </div>

    <!-- Schedule Tab -->
    <div class="tab-panel <?php echo !$showEditTab ? 'active' : ''; ?>" id="tab-schedule">
        <form method="GET" action="schedule.php" class="form-inline">
            <div class="form-group">
                <label for="date">Select Date:</label>
                <select name="date" id="date" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Choose a Date --</option>
                    <?php foreach ($dates as $dateRow): ?>
                        <option value="<?php echo htmlspecialchars($dateRow['Date']); ?>"
                                <?php echo ($selectedDate == $dateRow['Date']) ? 'selected' : ''; ?>>
                            <?php echo formatDate($dateRow['Date']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <?php if ($selectedDate): ?>
            <div class="results-section">
                <h3>Schedule for <?php echo formatDate($selectedDate); ?></h3>

                <?php if (count($scheduleSessions) > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Session Name</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($scheduleSessions as $session): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($session['SessionName']); ?></td>
                                    <td><?php echo formatTime($session['StartTime']); ?></td>
                                    <td><?php echo formatTime($session['EndTime']); ?></td>
                                    <td><?php echo htmlspecialchars($session['RoomLocation']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p class="info-text">Total sessions: <?php echo count($scheduleSessions); ?></p>
                <?php else: ?>
                    <p class="empty-state">No sessions scheduled for this date.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Edit Session Tab -->
    <div class="tab-panel <?php echo $showEditTab ? 'active' : ''; ?>" id="tab-edit">
        <?php if ($successMessage): ?>
            <?php displaySuccess($successMessage); ?>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <?php displayError($errorMessage); ?>
        <?php endif; ?>

        <div class="form-section">
            <form method="GET" action="schedule.php" class="selection-form">
                <input type="hidden" name="tab" value="edit">
                <div class="form-group">
                    <label for="session_id">Select Session:</label>
                    <select id="session_id" name="session_id" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Select a Session --</option>
                        <?php foreach ($allSessions as $session): ?>
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
                <form method="POST" action="schedule.php" class="attendee-form">
                    <input type="hidden" name="session_id" value="<?php echo $selectedSessionId; ?>">

                    <div class="form-group">
                        <label for="edit_date">Date: <span class="required">*</span></label>
                        <input type="date" id="edit_date" name="date"
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
                    </div>
                </form>
            </div>
        <?php elseif ($selectedSessionId): ?>
            <p class="empty-state">Session not found or no longer available.</p>
        <?php else: ?>
            <p class="info-text">Please select a session from the dropdown above to view and edit its details.</p>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.tab-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
        document.querySelectorAll('.tab-panel').forEach(function(p) { p.classList.remove('active'); });
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
    });
});
</script>

<?php include '../includes/footer.php'; ?>
