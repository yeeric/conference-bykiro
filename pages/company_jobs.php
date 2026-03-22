<?php
/**
 * Company Jobs Page
 * Display job postings filtered by company with currency formatting
 * Requirements: 5.1, 5.2, 5.3, 5.4
 */

require_once '../config/database.php';
require_once '../includes/functions.php';
include '../includes/header.php';

// Get database connection
$pdo = getDBConnection();

// Get selected company ID from GET parameter
$selectedCompanyId = isset($_GET['company_id']) ? (int)$_GET['company_id'] : null;

// Fetch all companies with job postings for dropdown
$companiesStmt = $pdo->query("
    SELECT DISTINCT c.CompanyID, c.CompanyName
    FROM Company c
    JOIN JobAd j ON c.CompanyID = j.PostedByCompanyId
    ORDER BY c.CompanyName
");
$companies = $companiesStmt->fetchAll();

// Fetch jobs for selected company
$jobs = [];
$companyName = null;
if ($selectedCompanyId) {
    // Get company name
    $companyStmt = $pdo->prepare("SELECT CompanyName FROM Company WHERE CompanyID = :company_id");
    $companyStmt->execute(['company_id' => $selectedCompanyId]);
    $companyData = $companyStmt->fetch();
    if ($companyData) {
        $companyName = $companyData['CompanyName'];
    }
    
    // Get jobs for selected company
    $jobsStmt = $pdo->prepare("
        SELECT JobTitle, Location, City, Province, PayRate
        FROM JobAd
        WHERE PostedByCompanyId = :company_id
        ORDER BY JobTitle
    ");
    $jobsStmt->execute(['company_id' => $selectedCompanyId]);
    $jobs = $jobsStmt->fetchAll();
}
?>

<div class="container">
    <h2>Company Job Postings</h2>
    <p>Select a company to view their job postings.</p>
    
    <form method="GET" action="company_jobs.php" class="form-inline">
        <div class="form-group">
            <label for="company_id">Select Company:</label>
            <select name="company_id" id="company_id" class="form-control" onchange="this.form.submit()">
                <option value="">-- Choose a Company --</option>
                <?php foreach ($companies as $company): ?>
                    <option value="<?php echo htmlspecialchars($company['CompanyID']); ?>"
                            <?php echo ($selectedCompanyId == $company['CompanyID']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($company['CompanyName']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
    
    <?php if ($selectedCompanyId && $companyName): ?>
        <div class="results-section">
            <h3><?php echo htmlspecialchars($companyName); ?> - Job Postings</h3>
            
            <?php if (count($jobs) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Location</th>
                            <th>City</th>
                            <th>Province</th>
                            <th>Pay Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $job): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($job['JobTitle']); ?></td>
                                <td><?php echo htmlspecialchars($job['Location']); ?></td>
                                <td><?php echo htmlspecialchars($job['City']); ?></td>
                                <td><?php echo htmlspecialchars($job['Province']); ?></td>
                                <td>$<?php echo formatCurrency($job['PayRate']); ?>/hour</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="info-text">Total job postings: <?php echo count($jobs); ?></p>
            <?php else: ?>
                <p class="empty-state">This company has no job postings.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
