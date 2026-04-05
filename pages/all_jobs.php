<?php
/**
 * All Jobs Page
 * Display all job postings from all companies
 * Requirements: 6.1, 6.2, 6.3, 6.4
 */

require_once '../config/database.php';
require_once '../includes/functions.php';
include '../includes/header.php';

// Get database connection
$pdo = getDBConnection();

// Fetch all jobs with company information
$jobsStmt = $pdo->query("
    SELECT c.CompanyName, j.JobTitle, j.Location, j.City, j.Province, j.PayRate
    FROM JobAd j
    JOIN Company c ON j.PostedByCompanyId = c.CompanyID
    ORDER BY c.CompanyName, j.JobTitle
");
$jobs = $jobsStmt->fetchAll();
?>

<div class="container">
    <h2>All Job Postings</h2>
    <p>Browse all available job opportunities from conference sponsors and companies.</p>
    
    <?php if (count($jobs) > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Company</th>
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
                        <td><?php echo htmlspecialchars($job['CompanyName']); ?></td>
                        <td><?php echo htmlspecialchars($job['JobTitle']); ?></td>
                        <td><?php echo htmlspecialchars($job['Location']); ?></td>
                        <td><?php echo htmlspecialchars($job['City']); ?></td>
                        <td><?php echo htmlspecialchars($job['Province']); ?></td>
                        <td><?php echo formatCurrency($job['PayRate']); ?>/hour</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="info-text">Total job postings: <?php echo count($jobs); ?></p>
    <?php else: ?>
        <p class="empty-state">No job postings available.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
