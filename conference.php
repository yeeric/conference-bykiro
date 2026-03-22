<?php
/**
 * Conference Management System - Home Page
 * Entry point for the application
 */

require_once 'includes/functions.php';
include 'includes/header.php';
?>

<div class="text-center">
    <h2>Welcome to the Conference Management System</h2>
    <p style="font-size: 1.2rem; margin: 2rem 0;">
        Manage your conference with ease. Select an option from the navigation menu above.
    </p>
    
    <div style="margin: 3rem 0;">
        <h3>Quick Links</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
            <div style="padding: 1.5rem; background: #f9f9f9; border-radius: 8px;">
                <h4 style="color: #667eea;">View Information</h4>
                <ul style="list-style: none; padding: 1rem 0;">
                    <li><a href="pages/attendees.php">View Attendees</a></li>
                    <li><a href="pages/schedule.php">Conference Schedule</a></li>
                    <li><a href="pages/sponsors.php">Sponsors</a></li>
                    <li><a href="pages/all_jobs.php">Job Postings</a></li>
                </ul>
            </div>
            
            <div style="padding: 1.5rem; background: #f9f9f9; border-radius: 8px;">
                <h4 style="color: #667eea;">Manage Data</h4>
                <ul style="list-style: none; padding: 1rem 0;">
                    <li><a href="pages/add_attendee.php">Register Attendee</a></li>
                    <li><a href="pages/add_company.php">Add Sponsor Company</a></li>
                    <li><a href="pages/edit_session.php">Edit Session</a></li>
                    <li><a href="pages/delete_company.php">Remove Company</a></li>
                </ul>
            </div>
            
            <div style="padding: 1.5rem; background: #f9f9f9; border-radius: 8px;">
                <h4 style="color: #667eea;">Reports</h4>
                <ul style="list-style: none; padding: 1rem 0;">
                    <li><a href="pages/financials.php">Financial Summary</a></li>
                    <li><a href="pages/committee_members.php">Committee Members</a></li>
                    <li><a href="pages/hotel_rooms.php">Hotel Rooms</a></li>
                    <li><a href="pages/company_jobs.php">Jobs by Company</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
