<?php
/**
 * Financials Page
 * Display conference revenue from registrations and sponsorships
 * Requirements: 9.1, 9.2, 9.3, 9.4, 9.5
 */

require_once '../config/database.php';
require_once '../includes/functions.php';
include '../includes/header.php';

// Get database connection
$pdo = getDBConnection();

// Count students and calculate registration fees
$studentsStmt = $pdo->query("
    SELECT COUNT(*) AS StudentCount
    FROM Attendee 
    WHERE AttendeeType = 'Student'
");
$studentData = $studentsStmt->fetch();
$studentCount = $studentData['StudentCount'];
$studentFees = $studentCount * 50;

// Count professionals and calculate registration fees
$professionalsStmt = $pdo->query("
    SELECT COUNT(*) AS ProfessionalCount
    FROM Attendee 
    WHERE AttendeeType = 'Professional'
");
$professionalData = $professionalsStmt->fetch();
$professionalCount = $professionalData['ProfessionalCount'];
$professionalFees = $professionalCount * 100;

// Calculate sponsorship amounts by level
$sponsorshipStmt = $pdo->query("
    SELECT 
        SUM(CASE WHEN SponsorLevel = 'Platinum' THEN 1 ELSE 0 END) AS PlatinumCount,
        SUM(CASE WHEN SponsorLevel = 'Gold' THEN 1 ELSE 0 END) AS GoldCount,
        SUM(CASE WHEN SponsorLevel = 'Silver' THEN 1 ELSE 0 END) AS SilverCount,
        SUM(CASE WHEN SponsorLevel = 'Bronze' THEN 1 ELSE 0 END) AS BronzeCount,
        SUM(CASE WHEN SponsorLevel = 'Platinum' THEN 10000 ELSE 0 END) AS PlatinumTotal,
        SUM(CASE WHEN SponsorLevel = 'Gold' THEN 5000 ELSE 0 END) AS GoldTotal,
        SUM(CASE WHEN SponsorLevel = 'Silver' THEN 3000 ELSE 0 END) AS SilverTotal,
        SUM(CASE WHEN SponsorLevel = 'Bronze' THEN 1000 ELSE 0 END) AS BronzeTotal
    FROM Sponsor
");
$sponsorshipData = $sponsorshipStmt->fetch();

// Extract sponsorship data with null handling
$platinumCount = $sponsorshipData['PlatinumCount'] ?? 0;
$goldCount = $sponsorshipData['GoldCount'] ?? 0;
$silverCount = $sponsorshipData['SilverCount'] ?? 0;
$bronzeCount = $sponsorshipData['BronzeCount'] ?? 0;

$platinumTotal = $sponsorshipData['PlatinumTotal'] ?? 0;
$goldTotal = $sponsorshipData['GoldTotal'] ?? 0;
$silverTotal = $sponsorshipData['SilverTotal'] ?? 0;
$bronzeTotal = $sponsorshipData['BronzeTotal'] ?? 0;

// Calculate totals
$registrationTotal = $studentFees + $professionalFees;
$sponsorshipTotal = $platinumTotal + $goldTotal + $silverTotal + $bronzeTotal;
$grandTotal = $registrationTotal + $sponsorshipTotal;
?>

<div class="container">
    <h2>Conference Financial Summary</h2>
    <p>Total revenue from registrations and sponsorships.</p>
    
    <div class="results-section">
        <h3>Registration Income</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Count</th>
                    <th>Rate</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Students</td>
                    <td><?php echo $studentCount; ?></td>
                    <td><?php echo formatCurrency(50); ?></td>
                    <td><?php echo formatCurrency($studentFees); ?></td>
                </tr>
                <tr>
                    <td>Professionals</td>
                    <td><?php echo $professionalCount; ?></td>
                    <td><?php echo formatCurrency(100); ?></td>
                    <td><?php echo formatCurrency($professionalFees); ?></td>
                </tr>
                <tr class="subtotal-row">
                    <td colspan="3"><strong>Registration Subtotal</strong></td>
                    <td><strong><?php echo formatCurrency($registrationTotal); ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="results-section">
        <h3>Sponsorship Income</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Sponsorship Level</th>
                    <th>Count</th>
                    <th>Rate</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <span class="badge badge-platinum">Platinum</span>
                    </td>
                    <td><?php echo $platinumCount; ?></td>
                    <td><?php echo formatCurrency(10000); ?></td>
                    <td><?php echo formatCurrency($platinumTotal); ?></td>
                </tr>
                <tr>
                    <td>
                        <span class="badge badge-gold">Gold</span>
                    </td>
                    <td><?php echo $goldCount; ?></td>
                    <td><?php echo formatCurrency(5000); ?></td>
                    <td><?php echo formatCurrency($goldTotal); ?></td>
                </tr>
                <tr>
                    <td>
                        <span class="badge badge-silver">Silver</span>
                    </td>
                    <td><?php echo $silverCount; ?></td>
                    <td><?php echo formatCurrency(3000); ?></td>
                    <td><?php echo formatCurrency($silverTotal); ?></td>
                </tr>
                <tr>
                    <td>
                        <span class="badge badge-bronze">Bronze</span>
                    </td>
                    <td><?php echo $bronzeCount; ?></td>
                    <td><?php echo formatCurrency(1000); ?></td>
                    <td><?php echo formatCurrency($bronzeTotal); ?></td>
                </tr>
                <tr class="subtotal-row">
                    <td colspan="3"><strong>Sponsorship Subtotal</strong></td>
                    <td><strong><?php echo formatCurrency($sponsorshipTotal); ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="results-section grand-total">
        <h3>Grand Total</h3>
        <table class="data-table">
            <tbody>
                <tr class="total-row">
                    <td><strong>Total Conference Revenue</strong></td>
                    <td><strong><?php echo formatCurrency($grandTotal); ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
