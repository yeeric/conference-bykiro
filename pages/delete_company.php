<?php
/**
 * Delete Sponsoring Company Page
 * Remove a sponsoring company and associated records
 * Requirements: 11.1, 11.4
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

// Start session to handle success/error messages
session_start();

include '../includes/header.php';

// Get database connection
$pdo = getDBConnection();

// Initialize variables
$selectedCompanyId = $_GET['company_id'] ?? null;
$companyDetails = null;
$errorMessage = '';
$successMessage = '';

// Check for session messages
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $errorMessage = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Fetch all companies that are sponsors for dropdown
try {
    $companiesStmt = $pdo->query("
        SELECT DISTINCT c.CompanyID, c.CompanyName
        FROM Company c
        JOIN Sponsor s ON c.CompanyID = s.CompanyID
        ORDER BY c.CompanyName
    ");
    $companies = $companiesStmt->fetchAll();
} catch (PDOException $e) {
    $errorMessage = 'Error loading companies. Please try again.';
    error_log("Error fetching companies: " . $e->getMessage());
    $companies = [];
}

// If a company is selected, fetch its details and impact
if ($selectedCompanyId) {
    try {
        $detailsStmt = $pdo->prepare("
            SELECT 
                c.CompanyName,
                COUNT(DISTINCT s.AttendeeID) AS SponsorCount,
                COUNT(DISTINCT j.JobTitle) AS JobCount
            FROM Company c
            LEFT JOIN Sponsor s ON c.CompanyID = s.CompanyID
            LEFT JOIN JobAd j ON c.CompanyID = j.PostedByCompanyId
            WHERE c.CompanyID = :company_id
            GROUP BY c.CompanyID, c.CompanyName
        ");
        $detailsStmt->execute(['company_id' => $selectedCompanyId]);
        $companyDetails = $detailsStmt->fetch();
        
        if (!$companyDetails) {
            $errorMessage = 'Company not found.';
            $selectedCompanyId = null;
        }
    } catch (PDOException $e) {
        $errorMessage = 'Error loading company details. Please try again.';
        error_log("Error fetching company details: " . $e->getMessage());
        $selectedCompanyId = null;
    }
}
?>

<div class="container">
    <h2>Delete Sponsoring Company</h2>
    <p>Select a sponsoring company to remove from the conference database.</p>
    
    <?php if ($successMessage): ?>
        <?php displaySuccess($successMessage); ?>
    <?php endif; ?>
    
    <?php if ($errorMessage): ?>
        <?php displayError($errorMessage); ?>
    <?php endif; ?>
    
    <div class="form-section">
        <form method="GET" action="delete_company.php" class="selection-form">
            <div class="form-group">
                <label for="company_id">Select Company:</label>
                <select id="company_id" name="company_id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Select a Company --</option>
                    <?php foreach ($companies as $company): ?>
                        <option value="<?php echo $company['CompanyID']; ?>" 
                                <?php echo ($selectedCompanyId == $company['CompanyID']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($company['CompanyName']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
    
    <?php if ($companyDetails): ?>
        <div class="results-section">
            <h3>Company Details</h3>
            <div class="company-info">
                <p><strong>Company Name:</strong> <?php echo htmlspecialchars($companyDetails['CompanyName']); ?></p>
                <p><strong>Associated Sponsors:</strong> <?php echo $companyDetails['SponsorCount']; ?></p>
                <p><strong>Job Postings:</strong> <?php echo $companyDetails['JobCount']; ?></p>
            </div>
            
            <div class="warning-box">
                <h4>⚠️ Warning: Deletion Impact</h4>
                <p>Deleting this company will permanently remove:</p>
                <ul>
                    <li><?php echo $companyDetails['SponsorCount']; ?> sponsor attendee record(s)</li>
                    <li><?php echo $companyDetails['JobCount']; ?> job posting(s)</li>
                    <li>The company record itself</li>
                </ul>
                <p><strong>This action cannot be undone.</strong></p>
            </div>
            
            <div class="confirmation-section">
                <h4>Confirm Deletion</h4>
                <p>Are you sure you want to delete <strong><?php echo htmlspecialchars($companyDetails['CompanyName']); ?></strong>?</p>
                
                <form method="POST" action="delete_company_process.php" class="confirmation-form">
                    <input type="hidden" name="company_id" value="<?php echo $selectedCompanyId; ?>">
                    <input type="hidden" name="company_name" value="<?php echo htmlspecialchars($companyDetails['CompanyName']); ?>">
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you absolutely sure? This will permanently delete <?php echo htmlspecialchars($companyDetails['CompanyName']); ?> and all associated records.');">
                            Confirm Deletion
                        </button>
                        <a href="delete_company.php" class="btn btn-secondary">Cancel</a>
                        <a href="sponsors.php" class="btn btn-secondary">View All Sponsors</a>
                    </div>
                </form>
            </div>
        </div>
    <?php elseif ($selectedCompanyId): ?>
        <p class="empty-state">Company not found or no longer available.</p>
    <?php else: ?>
        <p class="info-text">Please select a company from the dropdown above to view details and deletion options.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
