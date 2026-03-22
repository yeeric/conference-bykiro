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
    <p class="home-intro">
        Manage your conference with ease. Select an option from the navigation menu above.
    </p>

    <div class="home-links">
        <h3>Quick Links</h3>
        <div class="home-links-grid">
            <div class="home-link-group">
                <h4>View Information</h4>
                <ul>
                    <li><a href="pages/attendees.php">View Attendees</a></li>
                    <li><a href="pages/schedule.php">Conference Schedule</a></li>
                    <li><a href="pages/sponsors.php">Sponsors</a></li>
                    <li><a href="pages/all_jobs.php">Job Postings</a></li>
                </ul>
            </div>

            <div class="home-link-group">
                <h4>Manage Data</h4>
                <ul>
                    <li><a href="pages/add_attendee.php">Register Attendee</a></li>
                    <li><a href="pages/add_company.php">Add Sponsor Company</a></li>
                    <li><a href="pages/edit_session.php">Edit Session</a></li>
                    <li><a href="pages/delete_company.php">Remove Company</a></li>
                </ul>
            </div>

            <div class="home-link-group">
                <h4>Reports</h4>
                <ul>
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
