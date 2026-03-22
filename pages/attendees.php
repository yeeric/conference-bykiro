<?php
/**
 * Attendees Page
 * Display separate lists of students, professionals, and sponsors
 * Requirements: 7.1, 7.2, 7.3, 7.4, 7.5
 */

require_once '../config/database.php';
include '../includes/header.php';

// Get database connection
$pdo = getDBConnection();

// Fetch all students with room assignments
$studentsStmt = $pdo->query("
    SELECT a.FirstName, a.LastName, a.Email, s.RoomNumberStaysIn
    FROM Attendee a
    JOIN Student s ON a.AttendeeID = s.AttendeeID
    ORDER BY a.LastName, a.FirstName
");
$students = $studentsStmt->fetchAll();

// Fetch all professionals
$professionalsStmt = $pdo->query("
    SELECT FirstName, LastName, Email
    FROM Attendee
    WHERE AttendeeType = 'Professional'
    ORDER BY LastName, FirstName
");
$professionals = $professionalsStmt->fetchAll();

// Fetch all sponsors with company and level information
$sponsorsStmt = $pdo->query("
    SELECT a.FirstName, a.LastName, a.Email, c.CompanyName, sp.SponsorLevel
    FROM Attendee a
    JOIN Sponsor sp ON a.AttendeeID = sp.AttendeeID
    JOIN Company c ON sp.CompanyID = c.CompanyID
    ORDER BY a.LastName, a.FirstName
");
$sponsors = $sponsorsStmt->fetchAll();
?>

<div class="container">
    <h2>Conference Attendees</h2>
    <p>View all registered attendees organized by type.</p>
    
    <!-- Students Section -->
    <div class="results-section">
        <h3>Students</h3>
        <?php if (count($students) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Room Number</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['FirstName']); ?></td>
                            <td><?php echo htmlspecialchars($student['LastName']); ?></td>
                            <td><?php echo htmlspecialchars($student['Email']); ?></td>
                            <td><?php echo $student['RoomNumberStaysIn'] ? htmlspecialchars($student['RoomNumberStaysIn']) : 'Not assigned'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="info-text">Total students: <?php echo count($students); ?></p>
        <?php else: ?>
            <p class="empty-state">No student attendees registered.</p>
        <?php endif; ?>
    </div>
    
    <!-- Professionals Section -->
    <div class="results-section">
        <h3>Professionals</h3>
        <?php if (count($professionals) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($professionals as $professional): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($professional['FirstName']); ?></td>
                            <td><?php echo htmlspecialchars($professional['LastName']); ?></td>
                            <td><?php echo htmlspecialchars($professional['Email']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="info-text">Total professionals: <?php echo count($professionals); ?></p>
        <?php else: ?>
            <p class="empty-state">No professional attendees registered.</p>
        <?php endif; ?>
    </div>
    
    <!-- Sponsors Section -->
    <div class="results-section">
        <h3>Sponsors</h3>
        <?php if (count($sponsors) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Company</th>
                        <th>Sponsorship Level</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sponsors as $sponsor): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($sponsor['FirstName']); ?></td>
                            <td><?php echo htmlspecialchars($sponsor['LastName']); ?></td>
                            <td><?php echo htmlspecialchars($sponsor['Email']); ?></td>
                            <td><?php echo htmlspecialchars($sponsor['CompanyName']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo strtolower($sponsor['SponsorLevel']); ?>">
                                    <?php echo htmlspecialchars($sponsor['SponsorLevel']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="info-text">Total sponsors: <?php echo count($sponsors); ?></p>
        <?php else: ?>
            <p class="empty-state">No sponsor attendees registered.</p>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
