<?php
/**
 * Add Attendee Page
 * Register a new conference attendee with dynamic fields based on type
 * Requirements: 8.1, 8.3, 8.4
 */

require_once '../config/database.php';
require_once '../includes/functions.php';
include '../includes/header.php';

// Get database connection
$pdo = getDBConnection();

// Initialize variables
$successMessage = '';
$errorMessage = '';

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

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $attendeeType = $_POST['attendee_type'] ?? '';
    
    // Validate required fields
    if (empty($firstName) || empty($lastName) || empty($email) || empty($attendeeType)) {
        $errorMessage = 'All required fields must be filled.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Please enter a valid email address.';
    } else {
        try {
            // Check for duplicate email
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM Attendee WHERE Email = :email");
            $checkStmt->execute(['email' => $email]);
            $emailExists = $checkStmt->fetchColumn();
            
            if ($emailExists > 0) {
                $errorMessage = 'This email address is already registered.';
            } else {
                // Validate type-specific fields
                if ($attendeeType === 'Sponsor') {
                    $companyId = $_POST['company_id'] ?? '';
                    $sponsorLevel = $_POST['sponsor_level'] ?? '';
                    
                    if (empty($companyId) || empty($sponsorLevel)) {
                        $errorMessage = 'Company and sponsorship level are required for sponsors.';
                    }
                }
                
                if (empty($errorMessage)) {
                    // Begin transaction
                    $pdo->beginTransaction();
                    
                    try {
                        // Insert into Attendee table
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
                        
                        // Get the newly created AttendeeID
                        $attendeeId = $pdo->lastInsertId();
                        
                        // Insert into appropriate subtype table
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
                        
                        // Commit transaction
                        $pdo->commit();
                        
                        $successMessage = "Attendee $firstName $lastName successfully registered.";
                        
                        // Clear form data
                        $firstName = $lastName = $email = $attendeeType = '';
                    } catch (PDOException $e) {
                        // Rollback transaction on error
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
?>

<div class="container">
    <h2>Register New Attendee</h2>
    <p>Complete the form below to register a new conference attendee.</p>
    
    <?php if ($successMessage): ?>
        <?php displaySuccess($successMessage); ?>
    <?php endif; ?>
    
    <?php if ($errorMessage): ?>
        <?php displayError($errorMessage); ?>
    <?php endif; ?>
    
    <form method="POST" action="add_attendee.php" class="attendee-form">
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
            <a href="attendees.php" class="btn btn-secondary">View All Attendees</a>
        </div>
    </form>
</div>

<script>
/**
 * Update visible fields based on selected attendee type
 */
function updateTypeFields() {
    // Get selected attendee type
    const attendeeType = document.querySelector('input[name="attendee_type"]:checked');
    
    // Get conditional field sections
    const studentFields = document.getElementById('student-fields');
    const sponsorFields = document.getElementById('sponsor-fields');
    
    // Hide all conditional fields
    studentFields.style.display = 'none';
    sponsorFields.style.display = 'none';
    
    // Clear required attributes
    document.getElementById('company_id').removeAttribute('required');
    document.getElementById('sponsor_level').removeAttribute('required');
    
    // Show relevant fields based on type
    if (attendeeType) {
        if (attendeeType.value === 'Student') {
            studentFields.style.display = 'block';
        } else if (attendeeType.value === 'Sponsor') {
            sponsorFields.style.display = 'block';
            // Add required attributes for sponsor fields
            document.getElementById('company_id').setAttribute('required', 'required');
            document.getElementById('sponsor_level').setAttribute('required', 'required');
        }
    }
}

// Initialize field visibility on page load
document.addEventListener('DOMContentLoaded', function() {
    updateTypeFields();
});
</script>

<?php include '../includes/footer.php'; ?>
