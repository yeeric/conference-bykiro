<?php
/**
 * Sponsors Page
 * Display all sponsoring companies with their sponsorship levels
 * Requirements: 4.1, 4.2, 4.3, 4.4
 */

require_once '../config/database.php';
include '../includes/header.php';

// Get database connection
$pdo = getDBConnection();

// Fetch all sponsors with company information
$sponsorsStmt = $pdo->query("
    SELECT c.CompanyName, s.SponsorLevel
    FROM Company c
    JOIN Sponsor s ON c.CompanyID = s.CompanyID
    GROUP BY c.CompanyID, c.CompanyName, s.SponsorLevel
    ORDER BY 
        FIELD(s.SponsorLevel, 'Platinum', 'Gold', 'Silver', 'Bronze'),
        c.CompanyName
");
$sponsors = $sponsorsStmt->fetchAll();
?>

<div class="container">
    <h2>Conference Sponsors</h2>
    <p>View all sponsoring companies and their sponsorship levels.</p>
    
    <?php if (count($sponsors) > 0): ?>
        <div class="results-section">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Company Name</th>
                        <th>Sponsorship Level</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sponsors as $sponsor): ?>
                        <tr>
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
        </div>
    <?php else: ?>
        <p class="empty-state">No sponsors registered.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
