<?php
/**
 * Shared utility functions for the conference application
 */

/**
 * Sanitize output for HTML display
 * 
 * @param string $text Text to sanitize
 * @return string Sanitized text
 */
function sanitize($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency value
 * 
 * @param float $amount Amount to format
 * @return string Formatted currency string
 */
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Format time in 12-hour format
 * 
 * @param string $time Time in HH:MM:SS format
 * @return string Formatted time (e.g., "9:00 AM")
 */
function formatTime($time) {
    return date('g:i A', strtotime($time));
}

/**
 * Format date in readable format
 * 
 * @param string $date Date in YYYY-MM-DD format
 * @return string Formatted date (e.g., "October 15, 2025")
 */
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

/**
 * Display success message
 * 
 * @param string $message Success message to display
 */
function displaySuccess($message) {
    echo '<div class="message success">' . sanitize($message) . '</div>';
}

/**
 * Display error message
 * 
 * @param string $message Error message to display
 */
function displayError($message) {
    echo '<div class="message error">' . sanitize($message) . '</div>';
}

/**
 * Display info message
 * 
 * @param string $message Info message to display
 */
function displayInfo($message) {
    echo '<div class="message info">' . sanitize($message) . '</div>';
}
