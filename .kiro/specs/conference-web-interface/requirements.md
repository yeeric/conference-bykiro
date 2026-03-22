# Requirements Document

## Introduction

This document specifies the requirements for a web-based conference database management system. The system enables conference organizers to manage attendees, sessions, sponsors, hotel accommodations, and job postings through a multi-page web interface connected to a relational database.

## Glossary

- **Web_Application**: The PHP-based web interface for conference management
- **Database**: The relational database storing conference data (attendees, sessions, sponsors, etc.)
- **Organizer**: A conference organizer using the system
- **Attendee**: A person registered for the conference (Student, Professional, or Sponsor)
- **Student**: An attendee type with a $50 registration fee who may be assigned a hotel room
- **Professional**: An attendee type with a $100 registration fee
- **Sponsor**: An attendee type with $0 registration fee, associated with a company and sponsorship level
- **Session**: A scheduled conference event with date, time, location, and speakers
- **SubCommittee**: An organizing committee group with members and a chair
- **Company**: An organization that may sponsor the conference or post job advertisements
- **Sponsorship_Level**: The tier of company sponsorship (Platinum, Gold, Silver, Bronze)
- **Hotel_Room**: A room with a specific number of beds where students may be housed
- **Job_Ad**: A job posting by a company with title, location, and pay rate
- **PDO**: PHP Data Objects, a database abstraction layer for DBMS independence
- **Home_Page**: The main entry point page named "conference" (HTML or PHP)

## Requirements

### Requirement 1: Display SubCommittee Members

**User Story:** As an organizer, I want to view members of a specific organizing sub-committee, so that I can identify who is responsible for different conference areas.

#### Acceptance Criteria

1. THE Web_Application SHALL display a dropdown menu listing all available SubCommittees
2. WHEN an Organizer selects a SubCommittee from the dropdown, THE Web_Application SHALL display all members of that SubCommittee in a table
3. THE Web_Application SHALL display member first name, last name, and indicate the chair of the SubCommittee
4. IF no members exist for the selected SubCommittee, THEN THE Web_Application SHALL display a message indicating no members are found

### Requirement 2: List Students in Hotel Rooms

**User Story:** As an organizer, I want to see which students are staying in a specific hotel room, so that I can manage room assignments and occupancy.

#### Acceptance Criteria

1. THE Web_Application SHALL provide a mechanism to select a Hotel_Room by room number
2. WHEN an Organizer selects a Hotel_Room, THE Web_Application SHALL display all Students assigned to that room in a table
3. THE Web_Application SHALL display student first name, last name, and email for each Student in the room
4. THE Web_Application SHALL display the number of beds in the selected Hotel_Room
5. IF no Students are assigned to the selected Hotel_Room, THEN THE Web_Application SHALL display a message indicating the room is empty

### Requirement 3: Display Conference Schedule

**User Story:** As an organizer, I want to view the conference schedule for a specific day, so that I can see all sessions planned for that date.

#### Acceptance Criteria

1. THE Web_Application SHALL provide a mechanism to select a conference date
2. WHEN an Organizer selects a date, THE Web_Application SHALL display all Sessions scheduled for that date in a table
3. THE Web_Application SHALL display session name, start time, end time, and room location for each Session
4. THE Web_Application SHALL sort Sessions by start time in ascending order
5. IF no Sessions are scheduled for the selected date, THEN THE Web_Application SHALL display a message indicating no sessions are found

### Requirement 4: List Sponsors and Sponsorship Levels

**User Story:** As an organizer, I want to see all sponsors and their sponsorship levels, so that I can acknowledge their contributions appropriately.

#### Acceptance Criteria

1. THE Web_Application SHALL display all Companies that are sponsors in a table
2. THE Web_Application SHALL display company name and Sponsorship_Level for each sponsor
3. THE Web_Application SHALL group or sort sponsors by Sponsorship_Level (Platinum, Gold, Silver, Bronze)
4. IF no sponsors exist, THEN THE Web_Application SHALL display a message indicating no sponsors are found

### Requirement 5: List Jobs by Company

**User Story:** As an organizer, I want to view all job postings from a specific company, so that I can help attendees find relevant opportunities.

#### Acceptance Criteria

1. THE Web_Application SHALL provide a mechanism to select a Company from available companies
2. WHEN an Organizer selects a Company, THE Web_Application SHALL display all Job_Ads posted by that Company in a table
3. THE Web_Application SHALL display job title, location, city, province, and pay rate for each Job_Ad
4. IF the selected Company has no Job_Ads, THEN THE Web_Application SHALL display a message indicating no jobs are available

### Requirement 6: List All Available Jobs

**User Story:** As an organizer, I want to see all job postings from all companies, so that I can provide a comprehensive job board to attendees.

#### Acceptance Criteria

1. THE Web_Application SHALL display all Job_Ads from all Companies in a table
2. THE Web_Application SHALL display company name, job title, location, city, province, and pay rate for each Job_Ad
3. THE Web_Application SHALL sort Job_Ads by company name or allow filtering
4. IF no Job_Ads exist, THEN THE Web_Application SHALL display a message indicating no jobs are available

### Requirement 7: Show Attendee Lists by Type

**User Story:** As an organizer, I want to view separate lists of students, professionals, and sponsors, so that I can understand the composition of conference attendees.

#### Acceptance Criteria

1. THE Web_Application SHALL display three separate tables for Students, Professionals, and Sponsors
2. THE Web_Application SHALL display first name, last name, and email for each Attendee in all three tables
3. WHERE the Attendee is a Student, THE Web_Application SHALL display the assigned Hotel_Room number
4. WHERE the Attendee is a Sponsor, THE Web_Application SHALL display the Company name and Sponsorship_Level
5. IF any attendee type has no registrations, THEN THE Web_Application SHALL display a message for that type indicating no attendees are found

### Requirement 8: Add New Attendee

**User Story:** As an organizer, I want to register a new attendee, so that I can expand the conference participant list.

#### Acceptance Criteria

1. THE Web_Application SHALL provide a form to input attendee first name, last name, email, and attendee type
2. WHEN an Organizer submits the form with valid data, THE Web_Application SHALL create a new Attendee record in the Database
3. WHERE the attendee type is Student, THE Web_Application SHALL provide a mechanism to assign the Student to a Hotel_Room
4. WHERE the attendee type is Sponsor, THE Web_Application SHALL provide a mechanism to select a Company and Sponsorship_Level
5. WHEN a new Attendee is successfully created, THE Web_Application SHALL display a confirmation message
6. IF the email already exists in the Database, THEN THE Web_Application SHALL display an error message indicating duplicate email

### Requirement 9: Show Conference Financial Intake

**User Story:** As an organizer, I want to see the total financial intake from registrations and sponsorships, so that I can track conference revenue.

#### Acceptance Criteria

1. THE Web_Application SHALL calculate and display the total registration fees from all Students ($50 each)
2. THE Web_Application SHALL calculate and display the total registration fees from all Professionals ($100 each)
3. THE Web_Application SHALL calculate and display the total sponsorship amounts from all Sponsors (Platinum=$10,000, Gold=$5,000, Silver=$3,000, Bronze=$1,000)
4. THE Web_Application SHALL display the grand total of all registration and sponsorship income
5. THE Web_Application SHALL display these amounts in a clear tabular or summary format

### Requirement 10: Add New Sponsoring Company

**User Story:** As an organizer, I want to add a new sponsoring company, so that I can register new sponsors for the conference.

#### Acceptance Criteria

1. THE Web_Application SHALL provide a form to input company name, Sponsorship_Level, and sponsor representative details (first name, last name, email)
2. WHEN an Organizer submits the form with valid data, THE Web_Application SHALL create a new Company record in the Database
3. WHEN a new Company is created, THE Web_Application SHALL create an associated Sponsor Attendee record linked to that Company
4. WHEN a new sponsoring company is successfully created, THE Web_Application SHALL display a confirmation message
5. IF the sponsor email already exists in the Database, THEN THE Web_Application SHALL display an error message

### Requirement 11: Delete Sponsoring Company

**User Story:** As an organizer, I want to remove a sponsoring company, so that I can handle sponsor withdrawals or cancellations.

#### Acceptance Criteria

1. THE Web_Application SHALL provide a mechanism to select a Company that is a sponsor
2. WHEN an Organizer confirms deletion of a Company, THE Web_Application SHALL remove the Company record from the Database
3. WHEN a Company is deleted, THE Web_Application SHALL remove all associated Sponsor Attendee records due to cascade deletion
4. WHEN a Company is successfully deleted, THE Web_Application SHALL display a confirmation message
5. IF the Company has associated Job_Ads, THEN THE Web_Application SHALL remove those Job_Ads due to cascade deletion

### Requirement 12: Switch Session Day, Time, or Location

**User Story:** As an organizer, I want to change a session's date, time, or location, so that I can accommodate schedule changes and room conflicts.

#### Acceptance Criteria

1. THE Web_Application SHALL provide a mechanism to select a Session from available sessions
2. THE Web_Application SHALL display the current Session details (name, date, start time, end time, room location)
3. THE Web_Application SHALL provide a form to modify the Session date, start time, end time, and room location
4. WHEN an Organizer submits the form with valid data, THE Web_Application SHALL update the Session record in the Database
5. WHEN a Session is successfully updated, THE Web_Application SHALL display a confirmation message with the new details

### Requirement 13: Database Abstraction

**User Story:** As a system administrator, I want the application to work with multiple database management systems, so that I have flexibility in deployment environments.

#### Acceptance Criteria

1. THE Web_Application SHALL use PDO for all Database connections and queries
2. THE Web_Application SHALL NOT use mysqli or other DBMS-specific APIs
3. THE Web_Application SHALL handle Database connection errors gracefully with appropriate error messages

### Requirement 14: Multi-Page Navigation

**User Story:** As an organizer, I want to navigate between different features on separate pages, so that I can access functionality in an organized manner.

#### Acceptance Criteria

1. THE Web_Application SHALL consist of multiple web pages, not a single page
2. THE Web_Application SHALL have a Home_Page named "conference" (HTML or PHP extension)
3. THE Home_Page SHALL provide navigation links or menus to access all other functionality
4. WHEN an Organizer accesses the Web_Application, THE Home_Page SHALL be the entry point
5. THE Web_Application SHALL allow navigation to all features without requiring direct URL access to other pages

### Requirement 15: Visual Presentation

**User Story:** As an organizer, I want a visually organized interface, so that I can efficiently use the system.

#### Acceptance Criteria

1. WHERE data is displayed in tabular format, THE Web_Application SHALL use proper HTML table elements
2. THE Web_Application SHALL use proper HTML tags for headings, paragraphs, and lists
3. THE Web_Application SHALL include at least one image for visual appeal
4. THE Web_Application SHALL use CSS for styling to create a professional appearance
5. THE Web_Application SHALL ensure all pages have consistent visual styling
