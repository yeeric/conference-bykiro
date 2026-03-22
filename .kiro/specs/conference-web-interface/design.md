# Design Document: Conference Web Interface

## Overview

The Conference Web Interface is a multi-page PHP application that provides conference organizers with comprehensive database management capabilities. The system enables management of attendees, sessions, sponsors, hotel accommodations, committee members, and job postings through an intuitive web interface.

The application follows a simplified MVC-inspired architecture suitable for PHP multi-page applications, using PDO for database abstraction to ensure DBMS independence. The system prioritizes functionality and data integrity while maintaining a professional, organized visual presentation.

### Key Design Goals

1. **DBMS Independence**: Use PDO exclusively for all database operations to support multiple database systems
2. **Modular Organization**: Separate pages for distinct functional areas with clear navigation
3. **Data Integrity**: Maintain referential integrity through proper transaction handling and cascade operations
4. **User Experience**: Provide clear feedback for all operations and graceful handling of empty results
5. **Professional Presentation**: Use semantic HTML, CSS styling, and tabular data display

### Technology Stack

- **Backend**: PHP 7.4+ with PDO
- **Database**: MySQL (with PDO abstraction for portability)
- **Frontend**: HTML5, CSS3
- **Architecture**: Multi-page application with shared components

## Architecture

### Application Structure

The application follows a page-based architecture with shared utility components:

```
conference-web-interface/
├── conference.php              # Home page (entry point)
├── config/
│   └── database.php           # PDO connection configuration
├── includes/
│   ├── header.php             # Common header with navigation
│   ├── footer.php             # Common footer
│   └── functions.php          # Shared utility functions
├── pages/
│   ├── committee_members.php  # Display subcommittee members
│   ├── hotel_rooms.php        # List students in rooms
│   ├── schedule.php           # Conference schedule by day
│   ├── sponsors.php           # List sponsors and levels
│   ├── company_jobs.php       # Jobs by company
│   ├── all_jobs.php           # All available jobs
│   ├── attendees.php          # Attendee lists by type
│   ├── add_attendee.php       # Register new attendee
│   ├── financials.php         # Conference intake summary
│   ├── add_company.php        # Add sponsoring company
│   ├── delete_company.php     # Remove sponsoring company
│   └── edit_session.php       # Modify session details
├── css/
│   └── styles.css             # Application styling
└── images/
    └── logo.png               # Conference logo
```

### Data Flow Pattern

Each functional page follows a consistent pattern:

1. **Include Configuration**: Load database connection and shared functions
2. **Process Request**: Handle form submissions or query parameters
3. **Execute Query**: Use PDO prepared statements for database operations
4. **Render Response**: Display results in HTML with proper formatting
5. **Handle Errors**: Catch exceptions and display user-friendly messages

```
User Request → PHP Page → PDO Query → Database
                  ↓
            HTML Response ← Result Processing
```

### Database Connection Strategy

The application uses a centralized PDO connection with the following characteristics:

- **Single Connection Point**: `config/database.php` provides a reusable PDO instance
- **Error Mode**: PDO::ERRMODE_EXCEPTION for proper error handling
- **Prepared Statements**: All queries use prepared statements to prevent SQL injection
- **Transaction Support**: Critical operations use transactions for atomicity
- **Connection Pooling**: Connection is established once per request and reused



## Components and Interfaces

### 1. Database Connection Component (`config/database.php`)

**Purpose**: Provide a centralized, reusable PDO database connection.

**Interface**:
```php
function getDBConnection(): PDO
```

**Implementation Details**:
- Returns a configured PDO instance with exception error mode
- Uses environment variables or configuration constants for credentials
- Sets PDO::ATTR_ERRMODE to PDO::ERRMODE_EXCEPTION
- Sets PDO::ATTR_DEFAULT_FETCH_MODE to PDO::FETCH_ASSOC
- Handles connection failures with try-catch and user-friendly error messages

**Error Handling**:
- Catches PDOException during connection
- Displays generic error message without exposing credentials
- Logs detailed error information for debugging

### 2. Navigation Component (`includes/header.php`)

**Purpose**: Provide consistent navigation across all pages.

**Interface**:
- Included at the top of every page
- Displays application title and logo
- Renders navigation menu with links to all functional pages

**Implementation Details**:
- Uses semantic HTML5 `<nav>` element
- Highlights current page in navigation
- Responsive menu structure
- Links organized by functional category:
  - Committee & Organization
  - Attendees & Registration
  - Sessions & Schedule
  - Sponsors & Companies
  - Jobs & Opportunities
  - Financial Reports

### 3. SubCommittee Members Display (`pages/committee_members.php`)

**Purpose**: Display members of a selected organizing sub-committee.

**Interface**:
- GET parameter: `committee_id` (optional)
- Displays dropdown to select committee
- Shows member table for selected committee

**Database Queries**:
```sql
-- Get all committees for dropdown
SELECT CommitteeID, CommitteeName FROM SubCommittee ORDER BY CommitteeName

-- Get members of selected committee
SELECT cm.FirstName, cm.LastName, 
       (cm.MemberID = sc.ChairMemberID) AS IsChair
FROM CommitteeMember cm
JOIN MemberOfCommittee moc ON cm.MemberID = moc.MemberID
JOIN SubCommittee sc ON moc.CommitteeID = sc.CommitteeID
WHERE sc.CommitteeID = :committee_id
ORDER BY IsChair DESC, cm.LastName, cm.FirstName
```

**Display Format**:
- Dropdown menu for committee selection
- HTML table with columns: First Name, Last Name, Role
- Chair indicated with "(Chair)" suffix or special styling
- Empty state message: "No members found for this committee"

### 4. Hotel Room Students Display (`pages/hotel_rooms.php`)

**Purpose**: List all students assigned to a specific hotel room.

**Interface**:
- GET parameter: `room_number` (optional)
- Displays dropdown to select room
- Shows student table and room details

**Database Queries**:
```sql
-- Get all rooms for dropdown
SELECT RoomNumber, NumberOfBeds FROM HotelRoom ORDER BY RoomNumber

-- Get students in selected room
SELECT a.FirstName, a.LastName, a.Email
FROM Attendee a
JOIN Student s ON a.AttendeeID = s.AttendeeID
WHERE s.RoomNumberStaysIn = :room_number
ORDER BY a.LastName, a.FirstName

-- Get room details
SELECT NumberOfBeds FROM HotelRoom WHERE RoomNumber = :room_number
```

**Display Format**:
- Dropdown menu for room selection
- Room details: "Room {number} - {beds} bed(s)"
- HTML table with columns: First Name, Last Name, Email
- Occupancy indicator: "{count} of {beds} beds occupied"
- Empty state message: "This room is currently empty"

### 5. Conference Schedule Display (`pages/schedule.php`)

**Purpose**: Display all sessions scheduled for a specific date.

**Interface**:
- GET parameter: `date` (optional)
- Displays date picker or dropdown for date selection
- Shows session table for selected date

**Database Queries**:
```sql
-- Get all unique dates with sessions
SELECT DISTINCT Date FROM Session ORDER BY Date

-- Get sessions for selected date
SELECT SessionName, StartTime, EndTime, RoomLocation
FROM Session
WHERE Date = :date
ORDER BY StartTime, SessionName
```

**Display Format**:
- Date selection dropdown showing available dates
- Display selected date in readable format (e.g., "October 15, 2025")
- HTML table with columns: Session Name, Start Time, End Time, Location
- Time formatted as 12-hour format (e.g., "9:00 AM")
- Empty state message: "No sessions scheduled for this date"

### 6. Sponsors List Display (`pages/sponsors.php`)

**Purpose**: Display all sponsoring companies with their sponsorship levels.

**Interface**:
- No parameters required
- Displays all sponsors grouped by level

**Database Queries**:
```sql
-- Get all sponsors with company information
SELECT c.CompanyName, s.SponsorLevel
FROM Company c
JOIN Sponsor sp ON c.CompanyID = sp.CompanyID
JOIN Attendee a ON sp.AttendeeID = a.AttendeeID
GROUP BY c.CompanyID, c.CompanyName, s.SponsorLevel
ORDER BY 
    FIELD(s.SponsorLevel, 'Platinum', 'Gold', 'Silver', 'Bronze'),
    c.CompanyName
```

**Display Format**:
- HTML table with columns: Company Name, Sponsorship Level
- Grouped or color-coded by sponsorship level
- Level indicators with visual distinction (colors, badges)
- Empty state message: "No sponsors registered"

### 7. Company Jobs Display (`pages/company_jobs.php`)

**Purpose**: Display all job postings from a selected company.

**Interface**:
- GET parameter: `company_id` (optional)
- Displays dropdown to select company
- Shows job listings table

**Database Queries**:
```sql
-- Get all companies with job postings
SELECT DISTINCT c.CompanyID, c.CompanyName
FROM Company c
JOIN JobAd j ON c.CompanyID = j.PostedByCompanyId
ORDER BY c.CompanyName

-- Get jobs for selected company
SELECT JobTitle, Location, City, Province, PayRate
FROM JobAd
WHERE PostedByCompanyId = :company_id
ORDER BY JobTitle
```

**Display Format**:
- Dropdown menu for company selection
- Company name as heading
- HTML table with columns: Job Title, Location, City, Province, Pay Rate
- Pay rate formatted as currency (e.g., "$55.00/hour")
- Empty state message: "This company has no job postings"

### 8. All Jobs Display (`pages/all_jobs.php`)

**Purpose**: Display all available job postings from all companies.

**Interface**:
- No parameters required
- Displays comprehensive job listing

**Database Queries**:
```sql
-- Get all jobs with company information
SELECT c.CompanyName, j.JobTitle, j.Location, j.City, j.Province, j.PayRate
FROM JobAd j
JOIN Company c ON j.PostedByCompanyId = c.CompanyID
ORDER BY c.CompanyName, j.JobTitle
```

**Display Format**:
- HTML table with columns: Company, Job Title, Location, City, Province, Pay Rate
- Pay rate formatted as currency
- Sortable or filterable by company
- Empty state message: "No job postings available"

### 9. Attendee Lists Display (`pages/attendees.php`)

**Purpose**: Display separate lists of students, professionals, and sponsors.

**Interface**:
- No parameters required
- Displays three separate tables

**Database Queries**:
```sql
-- Get all students with room assignments
SELECT a.FirstName, a.LastName, a.Email, s.RoomNumberStaysIn
FROM Attendee a
JOIN Student s ON a.AttendeeID = s.AttendeeID
ORDER BY a.LastName, a.FirstName

-- Get all professionals
SELECT FirstName, LastName, Email
FROM Attendee
WHERE AttendeeType = 'Professional'
ORDER BY LastName, FirstName

-- Get all sponsors with company information
SELECT a.FirstName, a.LastName, a.Email, c.CompanyName, sp.SponsorLevel
FROM Attendee a
JOIN Sponsor sp ON a.AttendeeID = sp.AttendeeID
JOIN Company c ON sp.CompanyID = c.CompanyID
ORDER BY a.LastName, a.FirstName
```

**Display Format**:
- Three separate sections with headings
- Students table: First Name, Last Name, Email, Room Number
- Professionals table: First Name, Last Name, Email
- Sponsors table: First Name, Last Name, Email, Company, Sponsorship Level
- Empty state message for each type: "No {type} attendees registered"

### 10. Add Attendee Form (`pages/add_attendee.php`)

**Purpose**: Register a new conference attendee.

**Interface**:
- POST parameters: `first_name`, `last_name`, `email`, `attendee_type`
- Conditional parameters based on type:
  - Student: `room_number`
  - Sponsor: `company_id`, `sponsor_level`

**Database Queries**:
```sql
-- Check for duplicate email
SELECT COUNT(*) FROM Attendee WHERE Email = :email

-- Insert new attendee
INSERT INTO Attendee (FirstName, LastName, Email, AttendeeType)
VALUES (:first_name, :last_name, :email, :attendee_type)

-- Insert student subtype
INSERT INTO Student (AttendeeID, RoomNumberStaysIn)
VALUES (:attendee_id, :room_number)

-- Insert professional subtype
INSERT INTO Professional (AttendeeID)
VALUES (:attendee_id)

-- Insert sponsor subtype
INSERT INTO Sponsor (AttendeeID, SponsorLevel, CompanyID)
VALUES (:attendee_id, :sponsor_level, :company_id)

-- Get available rooms for dropdown
SELECT RoomNumber, NumberOfBeds FROM HotelRoom ORDER BY RoomNumber

-- Get companies for dropdown
SELECT CompanyID, CompanyName FROM Company ORDER BY CompanyName
```

**Form Fields**:
- First Name (text, required)
- Last Name (text, required)
- Email (email, required)
- Attendee Type (radio buttons: Student, Professional, Sponsor)
- Conditional fields (shown via JavaScript):
  - If Student: Room Number (dropdown, optional)
  - If Sponsor: Company (dropdown, required), Sponsorship Level (dropdown, required)

**Validation**:
- All required fields must be filled
- Email must be unique in database
- Email format validation
- Sponsor must have company and level selected

**Transaction Handling**:
- Use PDO transaction for multi-table insert
- Rollback on any error
- Commit only if all inserts succeed

**Display Format**:
- Form with clear labels and input fields
- Dynamic field display based on attendee type
- Success message: "Attendee {name} successfully registered"
- Error messages for validation failures or duplicate email

### 11. Financial Intake Display (`pages/financials.php`)

**Purpose**: Display total conference revenue from registrations and sponsorships.

**Interface**:
- No parameters required
- Displays financial summary

**Database Queries**:
```sql
-- Count students and calculate registration fees
SELECT COUNT(*) AS StudentCount, (COUNT(*) * 50) AS StudentFees
FROM Attendee WHERE AttendeeType = 'Student'

-- Count professionals and calculate registration fees
SELECT COUNT(*) AS ProfessionalCount, (COUNT(*) * 100) AS ProfessionalFees
FROM Attendee WHERE AttendeeType = 'Professional'

-- Calculate sponsorship amounts by level
SELECT 
    SUM(CASE WHEN SponsorLevel = 'Platinum' THEN 10000 ELSE 0 END) AS PlatinumTotal,
    SUM(CASE WHEN SponsorLevel = 'Gold' THEN 5000 ELSE 0 END) AS GoldTotal,
    SUM(CASE WHEN SponsorLevel = 'Silver' THEN 3000 ELSE 0 END) AS SilverTotal,
    SUM(CASE WHEN SponsorLevel = 'Bronze' THEN 1000 ELSE 0 END) AS BronzeTotal
FROM Sponsor
```

**Calculation Logic**:
- Student registration: $50 per student
- Professional registration: $100 per professional
- Platinum sponsorship: $10,000 per sponsor
- Gold sponsorship: $5,000 per sponsor
- Silver sponsorship: $3,000 per sponsor
- Bronze sponsorship: $1,000 per sponsor
- Grand total: Sum of all registration and sponsorship amounts

**Display Format**:
- HTML table with breakdown:
  - Registration Income section
    - Students: {count} × $50 = ${total}
    - Professionals: {count} × $100 = ${total}
    - Subtotal: ${registration_total}
  - Sponsorship Income section
    - Platinum: {count} × $10,000 = ${total}
    - Gold: {count} × $5,000 = ${total}
    - Silver: {count} × $3,000 = ${total}
    - Bronze: {count} × $1,000 = ${total}
    - Subtotal: ${sponsorship_total}
  - Grand Total: ${grand_total}
- Currency formatted with commas and two decimal places

### 12. Add Sponsoring Company Form (`pages/add_company.php`)

**Purpose**: Register a new sponsoring company with representative.

**Interface**:
- POST parameters: `company_name`, `sponsor_level`, `first_name`, `last_name`, `email`

**Database Queries**:
```sql
-- Check for duplicate sponsor email
SELECT COUNT(*) FROM Attendee WHERE Email = :email

-- Insert new company
INSERT INTO Company (CompanyName) VALUES (:company_name)

-- Insert sponsor attendee
INSERT INTO Attendee (FirstName, LastName, Email, AttendeeType)
VALUES (:first_name, :last_name, :email, 'Sponsor')

-- Insert sponsor subtype
INSERT INTO Sponsor (AttendeeID, SponsorLevel, CompanyID)
VALUES (:attendee_id, :sponsor_level, :company_id)
```

**Form Fields**:
- Company Name (text, required)
- Sponsorship Level (dropdown: Platinum, Gold, Silver, Bronze, required)
- Representative First Name (text, required)
- Representative Last Name (text, required)
- Representative Email (email, required)

**Validation**:
- All fields required
- Email must be unique
- Email format validation

**Transaction Handling**:
- Use PDO transaction for multi-table insert
- Insert company first, get generated CompanyID
- Insert attendee, get generated AttendeeID
- Insert sponsor linking both IDs
- Rollback on any error
- Commit only if all inserts succeed

**Display Format**:
- Form with clear labels
- Success message: "Sponsoring company {name} successfully added with {level} sponsorship"
- Error messages for validation failures

### 13. Delete Sponsoring Company (`pages/delete_company.php`)

**Purpose**: Remove a sponsoring company and associated records.

**Interface**:
- GET parameter: `company_id` (for display)
- POST parameter: `company_id` (for deletion confirmation)

**Database Queries**:
```sql
-- Get companies that are sponsors
SELECT DISTINCT c.CompanyID, c.CompanyName, sp.SponsorLevel
FROM Company c
JOIN Sponsor sp ON c.CompanyID = sp.CompanyID
ORDER BY c.CompanyName

-- Get company details for confirmation
SELECT c.CompanyName, COUNT(j.JobTitle) AS JobCount, COUNT(sp.AttendeeID) AS SponsorCount
FROM Company c
LEFT JOIN JobAd j ON c.CompanyID = j.PostedByCompanyId
LEFT JOIN Sponsor sp ON c.CompanyID = sp.CompanyID
WHERE c.CompanyID = :company_id
GROUP BY c.CompanyID, c.CompanyName

-- Delete company (cascades to sponsors and job ads)
DELETE FROM Company WHERE CompanyID = :company_id
```

**Cascade Behavior**:
- Deleting Company cascades to:
  - Sponsor records (ON DELETE CASCADE)
  - Attendee records for sponsors (ON DELETE CASCADE from Sponsor)
  - JobAd records (ON DELETE CASCADE)

**Display Format**:
- Dropdown to select company
- Confirmation page showing:
  - Company name
  - Number of associated sponsors
  - Number of associated job postings
  - Warning message about cascade deletion
- Confirmation button to proceed
- Success message: "Company {name} and {count} associated records deleted"

**Safety Measures**:
- Two-step process: select then confirm
- Display impact of deletion before confirming
- Use POST for actual deletion (not GET)

### 14. Edit Session Form (`pages/edit_session.php`)

**Purpose**: Modify session date, time, or location.

**Interface**:
- GET parameter: `session_id` (for display)
- POST parameters: `session_id`, `date`, `start_time`, `end_time`, `room_location`

**Database Queries**:
```sql
-- Get all sessions for dropdown
SELECT SessionID, SessionName, Date, StartTime
FROM Session
ORDER BY Date, StartTime

-- Get current session details
SELECT SessionName, Date, StartTime, EndTime, RoomLocation
FROM Session
WHERE SessionID = :session_id

-- Update session
UPDATE Session
SET Date = :date, StartTime = :start_time, EndTime = :end_time, RoomLocation = :room_location
WHERE SessionID = :session_id
```

**Form Fields**:
- Session selection (dropdown)
- Current details display (read-only)
- New Date (date picker, required)
- New Start Time (time picker, required)
- New End Time (time picker, required)
- New Room Location (text, required)

**Validation**:
- All fields required
- End time must be after start time
- Date must be valid conference date

**Display Format**:
- Dropdown to select session
- Current details shown in separate section
- Edit form with pre-filled values
- Success message: "Session '{name}' updated successfully"
- Display new details after update



## Data Models

### Entity Relationship Overview

The application interacts with the following database entities:

```
Attendee (supertype)
├── Student (subtype) → HotelRoom
├── Professional (subtype)
└── Sponsor (subtype) → Company

Session ← SpeaksAt → Speaker
Session ← Attends → Attendee

CommitteeMember ← MemberOfCommittee → SubCommittee
SubCommittee → CommitteeMember (chair)

Company → JobAd
Company → Sponsor
```

### Core Data Models

#### Attendee Model
```php
class Attendee {
    int $attendeeId;
    string $firstName;
    string $lastName;
    string $email;
    string $attendeeType; // 'Student', 'Professional', 'Sponsor'
    float $fee; // Generated: Student=$50, Professional=$100, Sponsor=$0
}
```

#### Student Model (extends Attendee)
```php
class Student {
    int $attendeeId;
    ?int $roomNumberStaysIn; // Nullable
}
```

#### Professional Model (extends Attendee)
```php
class Professional {
    int $attendeeId;
}
```

#### Sponsor Model (extends Attendee)
```php
class Sponsor {
    int $attendeeId;
    string $sponsorLevel; // 'Platinum', 'Gold', 'Silver', 'Bronze'
    int $emailsSent;
    int $maxEmailsAllowed; // Generated based on level
    int $companyId;
}
```

#### Session Model
```php
class Session {
    int $sessionId;
    string $sessionName;
    string $date; // DATE format
    string $startTime; // TIME format
    string $endTime; // TIME format
    string $roomLocation;
}
```

#### Company Model
```php
class Company {
    int $companyId;
    string $companyName;
}
```

#### JobAd Model
```php
class JobAd {
    string $jobTitle;
    string $location;
    string $city;
    string $province;
    float $payRate;
    int $postedByCompanyId;
}
```

#### HotelRoom Model
```php
class HotelRoom {
    int $roomNumber;
    int $numberOfBeds;
}
```

#### CommitteeMember Model
```php
class CommitteeMember {
    int $memberId;
    string $firstName;
    string $lastName;
}
```

#### SubCommittee Model
```php
class SubCommittee {
    int $committeeId;
    string $committeeName;
    int $chairMemberId;
}
```

### Data Transfer Objects (DTOs)

For complex queries that join multiple tables, the application uses DTOs:

#### AttendeeListDTO
```php
class AttendeeListDTO {
    string $firstName;
    string $lastName;
    string $email;
    ?int $roomNumber; // For students
    ?string $companyName; // For sponsors
    ?string $sponsorLevel; // For sponsors
}
```

#### SessionScheduleDTO
```php
class SessionScheduleDTO {
    string $sessionName;
    string $startTime;
    string $endTime;
    string $roomLocation;
}
```

#### CommitteeMemberDTO
```php
class CommitteeMemberDTO {
    string $firstName;
    string $lastName;
    bool $isChair;
}
```

#### FinancialSummaryDTO
```php
class FinancialSummaryDTO {
    int $studentCount;
    float $studentFees;
    int $professionalCount;
    float $professionalFees;
    float $platinumTotal;
    float $goldTotal;
    float $silverTotal;
    float $bronzeTotal;
    float $registrationTotal;
    float $sponsorshipTotal;
    float $grandTotal;
}
```

### Data Validation Rules

#### Attendee Validation
- Email must be unique across all attendees
- Email must match valid email format
- First name and last name required (non-empty)
- Attendee type must be one of: Student, Professional, Sponsor

#### Student Validation
- Room number must exist in HotelRoom table (if provided)
- Room assignment is optional

#### Sponsor Validation
- Company ID must exist in Company table
- Sponsor level must be one of: Platinum, Gold, Silver, Bronze
- Both company and level are required

#### Session Validation
- End time must be after start time
- Date must be valid date format
- Room location required (non-empty)

#### Company Validation
- Company name required (non-empty)
- Company name should be unique (business rule)

#### JobAd Validation
- Job title required (non-empty)
- Pay rate must be positive number
- Company ID must exist in Company table

### Database Constraints

The application relies on the following database constraints:

1. **Primary Keys**: All tables have primary keys for unique identification
2. **Foreign Keys**: Enforce referential integrity between related tables
3. **Unique Constraints**: Email in Attendee table must be unique
4. **NOT NULL Constraints**: Required fields cannot be null
5. **ENUM Constraints**: AttendeeType and SponsorLevel limited to specific values
6. **Generated Columns**: Fee and MaxEmailsAllowed computed automatically
7. **Cascade Deletes**: 
   - Deleting Company cascades to Sponsor and JobAd
   - Deleting Attendee cascades to Student/Professional/Sponsor subtypes
   - Deleting Session cascades to Attends and SpeaksAt relationships

