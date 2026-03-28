<?php
/**
 * Conference Management System - Home Page
 * Entry point for the application
 */

require_once 'includes/functions.php';
include 'includes/header.php';
?>

<div class="hero-section">
    <div class="hero-badge">2025 Annual Conference</div>
    <h2 class="hero-title">Conference Management System</h2>
    <p class="hero-subtitle">
        Your all-in-one platform for managing attendees, sessions, sponsors, and everything in between.
    </p>
    <div class="hero-actions">
        <a href="pages/schedule.php" class="btn btn-primary btn-lg">View Schedule</a>
        <a href="pages/attendees.php?tab=register" class="btn btn-secondary btn-lg">Register Now</a>
    </div>
</div>

<div class="stats-bar">
    <div class="stat-item">
        <span class="stat-icon">&#128101;</span>
        <div class="stat-text">
            <span class="stat-label">Attendees</span>
            <span class="stat-value">Manage</span>
        </div>
    </div>
    <div class="stat-item">
        <span class="stat-icon">&#128197;</span>
        <div class="stat-text">
            <span class="stat-label">Sessions</span>
            <span class="stat-value">Schedule</span>
        </div>
    </div>
    <div class="stat-item">
        <span class="stat-icon">&#127942;</span>
        <div class="stat-text">
            <span class="stat-label">Sponsors</span>
            <span class="stat-value">Partners</span>
        </div>
    </div>
    <div class="stat-item">
        <span class="stat-icon">&#128188;</span>
        <div class="stat-text">
            <span class="stat-label">Jobs</span>
            <span class="stat-value">Postings</span>
        </div>
    </div>
</div>

<div class="home-links">
    <h3 class="section-heading">Quick Links</h3>
    <div class="home-links-grid">
        <div class="home-link-group card-hover">
            <div class="card-icon card-icon-blue">&#128269;</div>
            <h4>View Information</h4>
            <ul>
                <li><a href="pages/attendees.php"><span class="link-arrow">&rarr;</span> View Attendees</a></li>
                <li><a href="pages/schedule.php"><span class="link-arrow">&rarr;</span> Conference Schedule</a></li>
                <li><a href="pages/sponsors.php"><span class="link-arrow">&rarr;</span> Sponsors</a></li>
                <li><a href="pages/all_jobs.php"><span class="link-arrow">&rarr;</span> Job Postings</a></li>
            </ul>
        </div>

        <div class="home-link-group card-hover">
            <div class="card-icon card-icon-green">&#9998;</div>
            <h4>Manage Data</h4>
            <ul>
                <li><a href="pages/attendees.php?tab=register"><span class="link-arrow">&rarr;</span> Register Attendee</a></li>
                <li><a href="pages/schedule.php?tab=edit"><span class="link-arrow">&rarr;</span> Edit Session</a></li>
                <li><a href="pages/sponsors.php"><span class="link-arrow">&rarr;</span> Manage Sponsors</a></li>
            </ul>
        </div>

        <div class="home-link-group card-hover">
            <div class="card-icon card-icon-purple">&#128202;</div>
            <h4>Reports</h4>
            <ul>
                <li><a href="pages/financials.php"><span class="link-arrow">&rarr;</span> Financial Summary</a></li>
                <li><a href="pages/committee_members.php"><span class="link-arrow">&rarr;</span> Committee Members</a></li>
                <li><a href="pages/hotel_rooms.php"><span class="link-arrow">&rarr;</span> Hotel Rooms</a></li>
            </ul>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
