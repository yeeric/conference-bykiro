<?php
/**
 * Conference Schedule Page
 * Display all sessions scheduled for a specific date
 * Requirements: 3.1, 3.2, 3.3, 3.4, 3.5
 */

require_once '../config/database.php';
require_once '../includes/functions.php';
include '../includes/header.php';

// Get database connection
$pdo = getDBConnection();

// Get selected date from GET parameter
$selectedDate = isset($_GET['date']) ? $_GET['date'] : null;

// Fetch all unique dates with sessions for dropdown
$datesStmt = $pdo->query("SELECT DISTINCT Date FROM Session ORDER BY Date");
$dates = $datesStmt->fetchAll();

// Fetch sessions if a date is selected
$sessions = [];
if ($selectedDate) {
    // Get sessions for selected date
    $sessionsStmt = $pdo->prepare("
        SELECT SessionName, StartTime, EndTime, RoomLocation
        FROM Session
        WHERE Date = :date
        ORDER BY StartTime, SessionName
    ");
    $sessionsStmt->execute(['date' => $selectedDate]);
    $sessions = $sessionsStmt->fetchAll();
}
?>

<div class="container">
    <h2>Conference Schedule</h2>
    <p>Select a date to view all sessions scheduled for that day.</p>
    
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
            
            <?php if (count($sessions) > 0): ?>
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
                        <?php foreach ($sessions as $session): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($session['SessionName']); ?></td>
                                <td><?php echo formatTime($session['StartTime']); ?></td>
                                <td><?php echo formatTime($session['EndTime']); ?></td>
                                <td><?php echo htmlspecialchars($session['RoomLocation']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="info-text">Total sessions: <?php echo count($sessions); ?></p>
            <?php else: ?>
                <p class="empty-state">No sessions scheduled for this date.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
