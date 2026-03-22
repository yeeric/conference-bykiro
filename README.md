# Conference Web Interface

A comprehensive PHP-based conference management system with MySQL database backend.

## Features

### Display Features
- **Committee Members** - View committee members with chair identification
- **Hotel Rooms** - Display hotel room assignments for students
- **Conference Schedule** - View sessions by date with time and location
- **Sponsors** - Display sponsors organized by level (Platinum, Gold, Silver, Bronze)

### Job Management
- **Company Jobs** - Filter job postings by company
- **All Jobs** - View all available job advertisements

### Attendee Management
- **Attendees List** - View all registered attendees (students, professionals, sponsors)
- **Add Attendee** - Registration form with automatic fee calculation
  - Students: $50
  - Professionals: $100
  - Sponsors: Free

### Financial Management
- **Financial Dashboard** - Complete revenue summary
  - Registration income breakdown
  - Sponsorship income by level
  - Grand total calculation

### Company Management
- **Add Company** - Create new sponsoring companies
- **Delete Company** - Remove companies with cascade delete to sponsors and job ads

### Session Management
- **Edit Session** - Update session details (date, time, location)

## Technology Stack

- **Backend**: PHP 8.x with PDO
- **Database**: MySQL 8.x
- **Styling**: Custom CSS with purple gradient theme
- **Architecture**: Multi-page application with shared components

## Project Structure

```
.
├── config/
│   └── database.php          # Database configuration (not in repo)
├── includes/
│   ├── header.php            # Shared header with navigation
│   ├── footer.php            # Shared footer
│   └── functions.php         # Utility functions
├── pages/
│   ├── committee_members.php # Committee member display
│   ├── hotel_rooms.php       # Hotel room assignments
│   ├── schedule.php          # Conference schedule
│   ├── sponsors.php          # Sponsor listings
│   ├── company_jobs.php      # Jobs by company
│   ├── all_jobs.php          # All job postings
│   ├── attendees.php         # Attendee listings
│   ├── add_attendee.php      # Attendee registration
│   ├── financials.php        # Financial dashboard
│   ├── add_company.php       # Add company form
│   ├── delete_company.php    # Delete company interface
│   ├── delete_company_process.php # Company deletion handler
│   └── edit_session.php      # Session editor
├── css/
│   └── styles.css            # Application styling
├── images/
│   └── logo.png              # Conference logo
├── conference.php            # Home page
└── conference_database.sql   # Database schema

```

## Installation

### Prerequisites
- PHP 8.x or higher
- MySQL 8.x or higher
- Web server (Apache/Nginx) or PHP built-in server

### Database Setup

1. Create the database:
```sql
CREATE DATABASE ConferenceDB;
```

2. Import the schema:
```bash
mysql -u root -p ConferenceDB < conference_database.sql
```

### Configuration

1. Create `config/database.php` with your database credentials:
```php
<?php
function getDBConnection() {
    $host = 'localhost';
    $dbname = 'ConferenceDB';
    $username = 'your_username';
    $password = 'your_password';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}
```

### Running the Application

#### Using PHP Built-in Server
```bash
php -S localhost:8000
```

Then open your browser to: http://localhost:8000/conference.php

#### Using Apache/Nginx
Configure your web server to point to the project directory and access via your configured domain/port.

## Security Features

- **PDO Prepared Statements** - All queries use prepared statements to prevent SQL injection
- **Transaction Support** - Multi-table operations use transactions for data integrity
- **Error Handling** - Comprehensive try-catch blocks with user-friendly error messages
- **Input Validation** - Form validation on all user inputs
- **Unique Constraints** - Email uniqueness enforced at database level

## Database Schema

### Core Tables
- `Attendee` - Registration data with auto-calculated fees
- `Student` - Student-specific data with room assignments
- `Professional` - Professional attendee data
- `Sponsor` - Sponsor attendee data with company links
- `Company` - Company information
- `Session` - Conference sessions
- `HotelRoom` - Hotel accommodations
- `CommitteeMember` - Committee member details
- `SubCommittee` - Committee structure
- `JobAd` - Job advertisements

### Key Features
- Generated columns for automatic fee calculation
- Foreign key constraints with CASCADE delete
- Proper indexing for performance
- ENUM types for data validation

## Development

### Code Standards
- PSR-12 coding style
- PDO for all database operations
- Consistent error handling
- Responsive design principles
- Empty state handling on all display pages

### Testing
The application has been thoroughly tested with:
- 64 comprehensive system verification tests
- 50 database operation tests
- 9 empty state verification tests
- 85 navigation tests
- 100% test pass rate

## Version History

### Version 1.0.0 (Initial Release)
- Complete conference management system
- All CRUD operations functional
- Purple gradient theme
- Responsive design
- Production-ready

## License

This project is for educational purposes.

## Author

Created as part of CISC332 Database Management Systems course.
