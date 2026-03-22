<?php
/**
 * Hotel Rooms Page
 * Display students assigned to hotel rooms with occupancy information
 * Requirements: 2.1, 2.2, 2.3, 2.4, 2.5
 */

require_once '../config/database.php';
include '../includes/header.php';

// Get database connection
$pdo = getDBConnection();

// Get selected room number from GET parameter
$selectedRoomNumber = isset($_GET['room_number']) ? (int)$_GET['room_number'] : null;

// Fetch all rooms for dropdown
$roomsStmt = $pdo->query("SELECT RoomNumber, NumberOfBeds FROM HotelRoom ORDER BY RoomNumber");
$rooms = $roomsStmt->fetchAll();

// Fetch students and room details if a room is selected
$students = [];
$roomDetails = null;
if ($selectedRoomNumber) {
    // Get room details
    $roomDetailsStmt = $pdo->prepare("SELECT NumberOfBeds FROM HotelRoom WHERE RoomNumber = :room_number");
    $roomDetailsStmt->execute(['room_number' => $selectedRoomNumber]);
    $roomDetails = $roomDetailsStmt->fetch();
    
    // Get students assigned to selected room
    $studentsStmt = $pdo->prepare("
        SELECT a.FirstName, a.LastName, a.Email
        FROM Attendee a
        JOIN Student s ON a.AttendeeID = s.AttendeeID
        WHERE s.RoomNumberStaysIn = :room_number
        ORDER BY a.LastName, a.FirstName
    ");
    $studentsStmt->execute(['room_number' => $selectedRoomNumber]);
    $students = $studentsStmt->fetchAll();
}
?>

<div class="container">
    <h2>Hotel Rooms</h2>
    <p>Select a hotel room to view students assigned and occupancy information.</p>
    
    <form method="GET" action="hotel_rooms.php" class="form-inline">
        <div class="form-group">
            <label for="room_number">Select Room:</label>
            <select name="room_number" id="room_number" class="form-control" onchange="this.form.submit()">
                <option value="">-- Choose a Room --</option>
                <?php foreach ($rooms as $room): ?>
                    <option value="<?php echo htmlspecialchars($room['RoomNumber']); ?>"
                            <?php echo ($selectedRoomNumber == $room['RoomNumber']) ? 'selected' : ''; ?>>
                        Room <?php echo htmlspecialchars($room['RoomNumber']); ?> (<?php echo htmlspecialchars($room['NumberOfBeds']); ?> bed<?php echo $room['NumberOfBeds'] != 1 ? 's' : ''; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
    
    <?php if ($selectedRoomNumber && $roomDetails): ?>
        <div class="results-section">
            <h3>Room <?php echo htmlspecialchars($selectedRoomNumber); ?> - <?php echo htmlspecialchars($roomDetails['NumberOfBeds']); ?> bed<?php echo $roomDetails['NumberOfBeds'] != 1 ? 's' : ''; ?></h3>
            
            <?php if (count($students) > 0): ?>
                <p class="info-text">Occupancy: <?php echo count($students); ?> of <?php echo htmlspecialchars($roomDetails['NumberOfBeds']); ?> bed<?php echo $roomDetails['NumberOfBeds'] != 1 ? 's' : ''; ?> occupied</p>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['FirstName']); ?></td>
                                <td><?php echo htmlspecialchars($student['LastName']); ?></td>
                                <td><?php echo htmlspecialchars($student['Email']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty-state">This room is currently empty.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
