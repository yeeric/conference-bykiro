<?php
/**
 * Delete Company Processing Script
 * Handles the actual deletion of a sponsoring company
 * Requirements: 11.2, 11.3, 11.5
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: delete_company.php');
    exit;
}

// Get POST parameters
$companyId = $_POST['company_id'] ?? null;
$companyName = $_POST['company_name'] ?? '';

// Validate input
if (!$companyId) {
    $_SESSION['error_message'] = 'Invalid company ID provided.';
    header('Location: delete_company.php');
    exit;
}

// Get database connection
$pdo = getDBConnection();

try {
    // Begin transaction
    $pdo->beginTransaction();
    
    // Get deletion impact details before deleting
    $impactStmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT s.AttendeeID) AS SponsorCount,
            COUNT(DISTINCT j.JobTitle) AS JobCount
        FROM Company c
        LEFT JOIN Sponsor s ON c.CompanyID = s.CompanyID
        LEFT JOIN JobAd j ON c.CompanyID = j.PostedByCompanyId
        WHERE c.CompanyID = :company_id
        GROUP BY c.CompanyID
    ");
    $impactStmt->execute(['company_id' => $companyId]);
    $impact = $impactStmt->fetch();
    
    $sponsorCount = $impact ? $impact['SponsorCount'] : 0;
    $jobCount = $impact ? $impact['JobCount'] : 0;
    
    // Get all sponsor attendee IDs before deletion
    $attendeeStmt = $pdo->prepare("
        SELECT AttendeeID FROM Sponsor WHERE CompanyID = :company_id
    ");
    $attendeeStmt->execute(['company_id' => $companyId]);
    $attendeeIds = $attendeeStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Delete the company (cascades to sponsors and job ads)
    $deleteStmt = $pdo->prepare("DELETE FROM Company WHERE CompanyID = :company_id");
    $deleteStmt->execute(['company_id' => $companyId]);
    
    // Check if deletion was successful
    if ($deleteStmt->rowCount() === 0) {
        throw new Exception('Company not found or already deleted.');
    }
    
    // Delete associated attendee records (Requirement 11.3)
    if (!empty($attendeeIds)) {
        $placeholders = implode(',', array_fill(0, count($attendeeIds), '?'));
        $attendeeDeleteStmt = $pdo->prepare("DELETE FROM Attendee WHERE AttendeeID IN ($placeholders)");
        $attendeeDeleteStmt->execute($attendeeIds);
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Set success message with deletion details
    $totalRecords = 1 + $sponsorCount + $jobCount; // Company + sponsors + jobs
    $successMessage = "Company '{$companyName}' successfully deleted. ";
    $successMessage .= "Total records removed: {$totalRecords} ";
    $successMessage .= "({$sponsorCount} sponsor(s), {$jobCount} job posting(s), 1 company record).";
    
    // Store success message in session
    session_start();
    $_SESSION['success_message'] = $successMessage;
    
    // Redirect back to delete company page
    header('Location: delete_company.php');
    exit;
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Log error
    error_log("Error deleting company: " . $e->getMessage());
    
    // Set error message
    session_start();
    $_SESSION['error_message'] = 'Error deleting company. Please try again.';
    
    // Redirect back
    header('Location: delete_company.php');
    exit;
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Log error
    error_log("Error deleting company: " . $e->getMessage());
    
    // Set error message
    session_start();
    $_SESSION['error_message'] = $e->getMessage();
    
    // Redirect back
    header('Location: delete_company.php');
    exit;
}
