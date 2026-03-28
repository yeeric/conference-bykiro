<?php
/**
 * Attendees Page
 * Display separate lists of students, professionals, and sponsors
 * Plus registration form as a tab
 * Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 8.1, 8.3, 8.4
 */

require_once '../config/database.php';
require_once '../includes/functions.php';
include '../includes/header.php';

// Get database connection
$pdo = getDBConnection();

// ── Handle registration form submission ──
$successMessage = '';
$errorMessage = '';
$showRegisterTab = false;

// Fetch available hotel rooms for student dropdown
$roomsStmt = $pdo->query("
    SELECT RoomNumber, NumberOfBeds
    FROM HotelRoom
    ORDER BY RoomNumber
");
$rooms = $roomsStmt->fetchAll();

// Fetch available companies for sponsor dropdown
$companiesStmt = $pdo->query("
    SELECT CompanyID, CompanyName
    FROM Company
    ORDER BY CompanyName
");
$companies = $companiesStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $showRegisterTab = true;
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $attendeeType = $_POST['attendee_type'] ?? '';

    if (empty($firstName) || empty($lastName) || empty($email) || empty($attendeeType)) {
        $errorMessage = 'All required fields must be filled.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Please enter a valid email address.';
    } else {
        try {
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM Attendee WHERE Email = :email");
            $checkStmt->execute(['email' => $email]);
            $emailExists = $checkStmt->fetchColumn();

            if ($emailExists > 0) {
                $errorMessage = 'This email address is already registered.';
            } else {
                if ($attendeeType === 'Sponsor') {
                    $companyId = $_POST['company_id'] ?? '';
                    $sponsorLevel = $_POST['sponsor_level'] ?? '';
                    if (empty($companyId) || empty($sponsorLevel)) {
                        $errorMessage = 'Company and sponsorship level are required for sponsors.';
                    }
                }

                if (empty($errorMessage)) {
                    $pdo->beginTransaction();
                    try {
                        $insertAttendeeStmt = $pdo->prepare("
                            INSERT INTO Attendee (FirstName, LastName, Email, AttendeeType)
                            VALUES (:first_name, :last_name, :email, :attendee_type)
                        ");
                        $insertAttendeeStmt->execute([
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                            'email' => $email,
                            'attendee_type' => $attendeeType
                        ]);
                        $attendeeId = $pdo->lastInsertId();

                        if ($attendeeType === 'Student') {
                            $roomNumber = !empty($_POST['room_number']) ? (int)$_POST['room_number'] : null;
                            $insertStudentStmt = $pdo->prepare("
                                INSERT INTO Student (AttendeeID, RoomNumberStaysIn)
                                VALUES (:attendee_id, :room_number)
                            ");
                            $insertStudentStmt->execute([
                                'attendee_id' => $attendeeId,
                                'room_number' => $roomNumber
                            ]);
                        } elseif ($attendeeType === 'Professional') {
                            $insertProfessionalStmt = $pdo->prepare("
                                INSERT INTO Professional (AttendeeID)
                                VALUES (:attendee_id)
                            ");
                            $insertProfessionalStmt->execute(['attendee_id' => $attendeeId]);
                        } elseif ($attendeeType === 'Sponsor') {
                            $companyId = (int)$_POST['company_id'];
                            $sponsorLevel = $_POST['sponsor_level'];
                            $insertSponsorStmt = $pdo->prepare("
                                INSERT INTO Sponsor (AttendeeID, SponsorLevel, CompanyID)
                                VALUES (:attendee_id, :sponsor_level, :company_id)
                            ");
                            $insertSponsorStmt->execute([
                                'attendee_id' => $attendeeId,
                                'sponsor_level' => $sponsorLevel,
                                'company_id' => $companyId
                            ]);
                        }

                        $pdo->commit();
                        $successMessage = "Attendee $firstName $lastName successfully registered.";
                        $firstName = $lastName = $email = $attendeeType = '';
                    } catch (PDOException $e) {
                        $pdo->rollBack();
                        $errorMessage = 'Registration failed. Please try again.';
                        error_log("Attendee registration error: " . $e->getMessage());
                    }
                }
            }
        } catch (PDOException $e) {
            $errorMessage = 'Database error. Please try again.';
            error_log("Database error: " . $e->getMessage());
        }
    }
}

// ── Fetch attendee lists (after possible insert) ──
$studentsStmt = $pdo->query("
    SELECT a.FirstName, a.LastName, a.Email, s.RoomNumberStaysIn
    FROM Attendee a
    JOIN Student s ON a.AttendeeID = s.AttendeeID
    ORDER BY a.LastName, a.FirstName
");
$students = $studentsStmt->fetchAll();

$professionalsStmt = $pdo->query("
    SELECT FirstName, LastName, Email
    FROM Attendee
    WHERE AttendeeType = 'Professional'
    ORDER BY LastName, FirstName
");
$professionals = $professionalsStmt->fetchAll();

$sponsorsStmt = $pdo->query("
    SELECT a.FirstName, a.LastName, a.Email, c.CompanyName, sp.SponsorLevel
    FROM Attendee a
    JOIN Sponsor sp ON a.AttendeeID = sp.AttendeeID
    JOIN Company c ON sp.CompanyID = c.CompanyID
    ORDER BY a.LastName, a.FirstName
");
$sponsors = $sponsorsStmt->fetchAll();

$totalAttendees = count($students) + count($professionals) + count($sponsors);

// Check if we should auto-open register tab via query param
if (isset($_GET['tab']) && $_GET['tab'] === 'register') {
    $showRegisterTab = true;
}
?>

<div class="container">
    <h2>Conference Attendees</h2>
    <p>View all registered attendees organized by type. <strong><?php echo $totalAttendees; ?></strong> total attendees.</p>

    <div class="tabs">
        <button class="tab-btn <?php echo !$showRegisterTab ? 'active' : ''; ?>" data-tab="students">
            Students
            <span class="tab-count"><?php echo count($students); ?></span>
        </button>
        <button class="tab-btn" data-tab="professionals">
            Professionals
            <span class="tab-count"><?php echo count($professionals); ?></span>
        </button>
        <button class="tab-btn" data-tab="sponsors">
            Sponsors
            <span class="tab-count"><?php echo count($sponsors); ?></span>
        </button>
        <button class="tab-btn tab-btn--register <?php echo $showRegisterTab ? 'active' : ''; ?>" data-tab="register">
            + Register
        </button>
    </div>

    <!-- Students Tab -->
    <div class="tab-panel <?php echo !$showRegisterTab ? 'active' : ''; ?>" id="tab-students">
        <div class="results-section">
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
                                <td><?php echo $student['RoomNumberStaysIn'] ? htmlspecialchars($student['RoomNumberStaysIn']) : '<span class="text-muted">Not assigned</span>'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="info-text">Total students: <?php echo count($students); ?></p>
            <?php else: ?>
                <p class="empty-state">No student attendees registered.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Professionals Tab -->
    <div class="tab-panel" id="tab-professionals">
        <div class="results-section">
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
    </div>

    <!-- Sponsors Tab -->
    <div class="tab-panel" id="tab-sponsors">
        <div class="results-section">
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

    <!-- Register Tab -->
    <div class="tab-panel <?php echo $showRegisterTab ? 'active' : ''; ?>" id="tab-register">
        <?php if ($successMessage): ?>
            <?php displaySuccess($successMessage); ?>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <?php displayError($errorMessage); ?>
        <?php endif; ?>

        <form method="POST" action="attendees.php" class="attendee-form">
            <div class="form-section">
                <h3>Basic Information</h3>

                <div class="form-group">
                    <label for="first_name">First Name: <span class="required">*</span></label>
                    <input type="text" id="first_name" name="first_name"
                           value="<?php echo isset($firstName) ? htmlspecialchars($firstName) : ''; ?>"
                           required class="form-control">
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name: <span class="required">*</span></label>
                    <input type="text" id="last_name" name="last_name"
                           value="<?php echo isset($lastName) ? htmlspecialchars($lastName) : ''; ?>"
                           required class="form-control">
                </div>

                <div class="form-group">
                    <label for="email">Email: <span class="required">*</span></label>
                    <input type="email" id="email" name="email"
                           value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                           required class="form-control">
                </div>
            </div>

            <div class="form-section">
                <h3>Attendee Type <span class="required">*</span></h3>

                <div class="form-group radio-group">
                    <label class="radio-label">
                        <input type="radio" name="attendee_type" value="Student"
                               <?php echo (isset($attendeeType) && $attendeeType === 'Student') ? 'checked' : ''; ?>
                               onchange="updateTypeFields()" required>
                        Student ($50 registration fee)
                    </label>

                    <label class="radio-label">
                        <input type="radio" name="attendee_type" value="Professional"
                               <?php echo (isset($attendeeType) && $attendeeType === 'Professional') ? 'checked' : ''; ?>
                               onchange="updateTypeFields()" required>
                        Professional ($100 registration fee)
                    </label>

                    <label class="radio-label">
                        <input type="radio" name="attendee_type" value="Sponsor"
                               <?php echo (isset($attendeeType) && $attendeeType === 'Sponsor') ? 'checked' : ''; ?>
                               onchange="updateTypeFields()" required>
                        Sponsor (No registration fee)
                    </label>
                </div>
            </div>

            <!-- Student-specific fields -->
            <div id="student-fields" class="form-section conditional-fields" style="display: none;">
                <h3>Student Information</h3>

                <div class="form-group">
                    <label for="room_number">Hotel Room (optional):</label>
                    <select id="room_number" name="room_number" class="form-control">
                        <option value="">-- No Room Assignment --</option>
                        <?php foreach ($rooms as $room): ?>
                            <option value="<?php echo htmlspecialchars($room['RoomNumber']); ?>">
                                Room <?php echo htmlspecialchars($room['RoomNumber']); ?>
                                (<?php echo htmlspecialchars($room['NumberOfBeds']); ?> bed<?php echo $room['NumberOfBeds'] > 1 ? 's' : ''; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Sponsor-specific fields -->
            <div id="sponsor-fields" class="form-section conditional-fields" style="display: none;">
                <h3>Sponsor Information</h3>

                <div class="form-group">
                    <label for="company_id">Company: <span class="required">*</span></label>
                    <select id="company_id" name="company_id" class="form-control">
                        <option value="">-- Select Company --</option>
                        <?php foreach ($companies as $company): ?>
                            <option value="<?php echo htmlspecialchars($company['CompanyID']); ?>">
                                <?php echo htmlspecialchars($company['CompanyName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="sponsor_level">Sponsorship Level: <span class="required">*</span></label>
                    <select id="sponsor_level" name="sponsor_level" class="form-control">
                        <option value="">-- Select Level --</option>
                        <option value="Platinum">Platinum ($10,000)</option>
                        <option value="Gold">Gold ($5,000)</option>
                        <option value="Silver">Silver ($3,000)</option>
                        <option value="Bronze">Bronze ($1,000)</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Register Attendee</button>
            </div>
        </form>
    </div>
</div>

<script>
// Tab switching
document.querySelectorAll('.tab-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
        document.querySelectorAll('.tab-panel').forEach(function(p) { p.classList.remove('active'); });
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
    });
});

// Attendee type conditional fields
function updateTypeFields() {
    var attendeeType = document.querySelector('input[name="attendee_type"]:checked');
    var studentFields = document.getElementById('student-fields');
    var sponsorFields = document.getElementById('sponsor-fields');

    studentFields.style.display = 'none';
    sponsorFields.style.display = 'none';

    document.getElementById('company_id').removeAttribute('required');
    document.getElementById('sponsor_level').removeAttribute('required');

    if (attendeeType) {
        if (attendeeType.value === 'Student') {
            studentFields.style.display = 'block';
        } else if (attendeeType.value === 'Sponsor') {
            sponsorFields.style.display = 'block';
            document.getElementById('company_id').setAttribute('required', 'required');
            document.getElementById('sponsor_level').setAttribute('required', 'required');
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    updateTypeFields();
});
</script>

<?php include '../includes/footer.php'; ?>
