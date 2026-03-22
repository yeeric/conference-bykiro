<?php
/**
 * Add Sponsoring Company Page
 * Register a new sponsoring company with representative
 * Requirements: 10.1, 10.5
 */

require_once '../config/database.php';
require_once '../includes/functions.php';
include '../includes/header.php';

// Get database connection
$pdo = getDBConnection();

// Initialize variables
$successMessage = '';
$errorMessage = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $companyName = trim($_POST['company_name'] ?? '');
    $sponsorLevel = $_POST['sponsor_level'] ?? '';
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Validate required fields
    if (empty($companyName) || empty($sponsorLevel) || empty($firstName) || empty($lastName) || empty($email)) {
        $errorMessage = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Please enter a valid email address.';
    } else {
        try {
            // Check for duplicate sponsor email
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM Attendee WHERE Email = :email");
            $checkStmt->execute(['email' => $email]);
            $emailExists = $checkStmt->fetchColumn();
            
            if ($emailExists > 0) {
                $errorMessage = 'This email address is already registered.';
            } else {
                // Begin transaction
                $pdo->beginTransaction();
                
                try {
                    // Insert new company
                    $insertCompanyStmt = $pdo->prepare("
                        INSERT INTO Company (CompanyName)
                        VALUES (:company_name)
                    ");
                    $insertCompanyStmt->execute(['company_name' => $companyName]);
                    
                    // Get the newly created CompanyID
                    $companyId = $pdo->lastInsertId();
                    
                    // Insert sponsor attendee
                    $insertAttendeeStmt = $pdo->prepare("
                        INSERT INTO Attendee (FirstName, LastName, Email, AttendeeType)
                        VALUES (:first_name, :last_name, :email, 'Sponsor')
                    ");
                    $insertAttendeeStmt->execute([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email
                    ]);
                    
                    // Get the newly created AttendeeID
                    $attendeeId = $pdo->lastInsertId();
                    
                    // Insert sponsor subtype linking both IDs
                    $insertSponsorStmt = $pdo->prepare("
                        INSERT INTO Sponsor (AttendeeID, SponsorLevel, CompanyID)
                        VALUES (:attendee_id, :sponsor_level, :company_id)
                    ");
                    $insertSponsorStmt->execute([
                        'attendee_id' => $attendeeId,
                        'sponsor_level' => $sponsorLevel,
                        'company_id' => $companyId
                    ]);
                    
                    // Commit transaction
                    $pdo->commit();
                    
                    $successMessage = "Sponsoring company $companyName successfully added with $sponsorLevel sponsorship.";
                    
                    // Clear form data
                    $companyName = $sponsorLevel = $firstName = $lastName = $email = '';
                } catch (PDOException $e) {
                    // Rollback transaction on error
                    $pdo->rollBack();
                    $errorMessage = 'Registration failed. Please try again.';
                    error_log("Company registration error: " . $e->getMessage());
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
    <h2>Add Sponsoring Company</h2>
    <p>Complete the form below to register a new sponsoring company and representative.</p>
    
    <?php if ($successMessage): ?>
        <?php displaySuccess($successMessage); ?>
    <?php endif; ?>
    
    <?php if ($errorMessage): ?>
        <?php displayError($errorMessage); ?>
    <?php endif; ?>
    
    <form method="POST" action="add_company.php" class="attendee-form">
        <div class="form-section">
            <h3>Company Information</h3>
            
            <div class="form-group">
                <label for="company_name">Company Name: <span class="required">*</span></label>
                <input type="text" id="company_name" name="company_name" 
                       value="<?php echo isset($companyName) ? htmlspecialchars($companyName) : ''; ?>" 
                       required class="form-control">
            </div>
            
            <div class="form-group">
                <label for="sponsor_level">Sponsorship Level: <span class="required">*</span></label>
                <select id="sponsor_level" name="sponsor_level" required class="form-control">
                    <option value="">-- Select Level --</option>
                    <option value="Platinum" <?php echo (isset($sponsorLevel) && $sponsorLevel === 'Platinum') ? 'selected' : ''; ?>>
                        Platinum ($10,000)
                    </option>
                    <option value="Gold" <?php echo (isset($sponsorLevel) && $sponsorLevel === 'Gold') ? 'selected' : ''; ?>>
                        Gold ($5,000)
                    </option>
                    <option value="Silver" <?php echo (isset($sponsorLevel) && $sponsorLevel === 'Silver') ? 'selected' : ''; ?>>
                        Silver ($3,000)
                    </option>
                    <option value="Bronze" <?php echo (isset($sponsorLevel) && $sponsorLevel === 'Bronze') ? 'selected' : ''; ?>>
                        Bronze ($1,000)
                    </option>
                </select>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Representative Information</h3>
            
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
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Add Sponsoring Company</button>
            <a href="sponsors.php" class="btn btn-secondary">View All Sponsors</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
