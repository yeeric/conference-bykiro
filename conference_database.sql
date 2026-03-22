-- ============================================================
-- CISC 332 Assignment 1 - Conference Database
-- Student: Michelle Ye
-- Student ID: 20431594
-- MySQL Script for conferenceDB
-- ============================================================

DROP DATABASE IF EXISTS ConferenceDB;
CREATE DATABASE ConferenceDB;
USE ConferenceDB;

-- ============================================================
-- BASE TABLES
-- ============================================================

-- Company table (referenced by Sponsors)
CREATE TABLE Company (
    CompanyID INT PRIMARY KEY AUTO_INCREMENT,
    CompanyName VARCHAR(100) NOT NULL
);

-- ============================================================
-- COMMITTEE MEMBER
-- ============================================================

CREATE TABLE CommitteeMember (
    MemberID INT PRIMARY KEY AUTO_INCREMENT,
    FirstName VARCHAR(100) NOT NULL,
    LastName VARCHAR(100) NOT NULL
);

-- SubCommittee table (referenced by Sponsors)
CREATE TABLE SubCommittee (
    CommitteeID INT PRIMARY KEY AUTO_INCREMENT,
    CommitteeName VARCHAR(100) NOT NULL,
    ChairMemberID INT NOT NULL,
    FOREIGN KEY (ChairMemberID) REFERENCES CommitteeMember(MemberID)
);

-- Hotel Room table
CREATE TABLE HotelRoom (
    RoomNumber INT PRIMARY KEY,
    NumberOfBeds INT NOT NULL
);

-- Session table
CREATE TABLE Session (
    SessionID INT PRIMARY KEY AUTO_INCREMENT,
    SessionName VARCHAR(150) NOT NULL,
    Date DATE NOT NULL,
    StartTime TIME NOT NULL,
    EndTime TIME NOT NULL,
    RoomLocation VARCHAR(100) NOT NULL
);

-- ============================================================
-- ATTENDEE HIERARCHY (ISA / Specialization)
-- ============================================================

-- Attendee (supertype)
CREATE TABLE Attendee (
    AttendeeID INT PRIMARY KEY AUTO_INCREMENT,
    FirstName VARCHAR(50) NOT NULL,
    LastName VARCHAR(50) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    AttendeeType ENUM('Student', 'Professional', 'Sponsor') NOT NULL,
    -- Fee is derived: Student=$50, Professional=$100, Sponsor=$0
    Fee DECIMAL(10,2) GENERATED ALWAYS AS (
        CASE AttendeeType
            WHEN 'Student' THEN 50.00
            WHEN 'Professional' THEN 100.00
            WHEN 'Sponsor' THEN 0.00
        END
    ) STORED
);

-- Student subtype
-- RoomNumberStaysIn: Student N:1 HotelRoom
-- (Many students can stay in one room, each student stays in at most one room)
CREATE TABLE Student (
    AttendeeID INT PRIMARY KEY,
    RoomNumberStaysIn INT,

    FOREIGN KEY (AttendeeID) REFERENCES Attendee(AttendeeID)
        ON DELETE CASCADE,

    FOREIGN KEY (RoomNumberStaysIn) REFERENCES HotelRoom(RoomNumber)
        ON DELETE SET NULL
);

-- Professional subtype
CREATE TABLE Professional (
    AttendeeID INT PRIMARY KEY,
    FOREIGN KEY (AttendeeID) REFERENCES Attendee(AttendeeID)
        ON DELETE CASCADE
);

-- Sponsor subtype
CREATE TABLE Sponsor (
    AttendeeID INT PRIMARY KEY,
    SponsorLevel ENUM('Platinum', 'Gold', 'Silver', 'Bronze') NOT NULL,
    EmailsSent INT NOT NULL DEFAULT 0,
    -- MaxEmails is derived from SponsorLevel:
    -- Platinum($10,000)=5, Gold($5,000)=4, Silver($3,000)=3, Bronze($1,000)=0
    MaxEmailsAllowed INT GENERATED ALWAYS AS (
        CASE SponsorLevel
            WHEN 'Platinum' THEN 5
            WHEN 'Gold' THEN 4
            WHEN 'Silver' THEN 3
            WHEN 'Bronze' THEN 0
        END
    ) STORED,
    CompanyID INT NOT NULL,
    FOREIGN KEY (AttendeeID) REFERENCES Attendee(AttendeeID)
        ON DELETE CASCADE,
    FOREIGN KEY (CompanyID) REFERENCES Company(CompanyID)
        ON DELETE CASCADE
        
);

-- ============================================================
-- SPEAKER (can also be an Attendee)
-- ============================================================

CREATE TABLE Speaker (
    SpeakerID INT PRIMARY KEY AUTO_INCREMENT,
    FirstName VARCHAR(50) NOT NULL,
    LastName VARCHAR(50) NOT NULL,
    AttendeeID INT NULL,
    FOREIGN KEY (AttendeeID) REFERENCES Attendee(AttendeeID)
        ON DELETE SET NULL
);

-- ============================================================
-- JOB AD (posted by Company, 1:N)
-- ============================================================

CREATE TABLE JobAd (
    JobTitle VARCHAR(150) NOT NULL,
    Location VARCHAR(150) NOT NULL,
    City VARCHAR(100) NOT NULL,
    Province VARCHAR(100) NOT NULL,
    PayRate DECIMAL(10,2) NOT NULL,
    PostedByCompanyId INT NOT NULL,
    PRIMARY KEY (PostedByCompanyId, JobTitle),
    FOREIGN KEY (PostedByCompanyId) REFERENCES Company(CompanyID)
        ON DELETE CASCADE
);

-- ============================================================
-- RELATIONSHIP TABLES (M:N)
-- ============================================================

-- Attends: Attendee M:N Session
CREATE TABLE Attends (
    AttendeeID INT NOT NULL,
    SessionID INT NOT NULL,
    PRIMARY KEY (AttendeeID, SessionID),
    FOREIGN KEY (AttendeeID) REFERENCES Attendee(AttendeeID)
        ON DELETE CASCADE,
    FOREIGN KEY (SessionID) REFERENCES Session(SessionID)
        ON DELETE CASCADE
);

-- SpeaksAt: Speaker N:M Session
-- (All sessions have a speaker; a speaker can speak at many sessions)
CREATE TABLE SpeaksAt (
    SpeakerID INT NOT NULL,
    SessionID INT NOT NULL,
    PRIMARY KEY (SpeakerID, SessionID),
    FOREIGN KEY (SpeakerID) REFERENCES Speaker(SpeakerID)
        ON DELETE CASCADE,
    FOREIGN KEY (SessionID) REFERENCES Session(SessionID)
        ON DELETE CASCADE
);


-- MemberOf: CommitteeMember M:N SubCommittee
-- (Committee members can be associated with SubCommittee)
CREATE TABLE MemberOfCommittee (
    MemberID INT NOT NULL,
    CommitteeID INT NOT NULL,
    PRIMARY KEY (MemberID, CommitteeID),
    FOREIGN KEY (MemberID) REFERENCES CommitteeMember(MemberID)
        ON DELETE CASCADE,
    FOREIGN KEY (CommitteeID) REFERENCES SubCommittee(CommitteeID)
        ON DELETE CASCADE
);

-- ============================================================
-- SAMPLE DATA
-- ============================================================

-- ------------------------------------------------------------
-- Company (8 rows)
-- ------------------------------------------------------------
INSERT INTO Company (CompanyName) VALUES
    ('Shopify'),                    -- 1
    ('OpenText'),                   -- 2
    ('Celestica'),                  -- 3
    ('Constellation Software'),     -- 4
    ('BlackBerry'),                 -- 5
    ('Kinaxis'),                    -- 6
    ('D2L'),                        -- 7
    ('Mitel Networks');             -- 8

-- ------------------------------------------------------------
-- HotelRoom (8 rows)
-- ------------------------------------------------------------
INSERT INTO HotelRoom (RoomNumber, NumberOfBeds) VALUES
    (101, 1),
    (102, 2),
    (103, 2),
    (201, 1),
    (202, 2),
    (203, 1),
    (301, 3),
    (302, 2);

-- ------------------------------------------------------------
-- Session (8 rows)
-- ------------------------------------------------------------
INSERT INTO Session (SessionID, SessionName, Date, StartTime, EndTime, RoomLocation) VALUES
    (1, 'Keynote: Future of AI',         '2025-10-15', '09:00:00', '10:30:00', 'Main Hall'),
    (2, 'Machine Learning Workshop',     '2025-10-15', '11:00:00', '12:30:00', 'Room A'),
    (3, 'Database Systems Panel',        '2025-10-15', '14:00:00', '15:30:00', 'Room B'),
    (4, 'Networking Event',              '2025-10-15', '16:00:00', '18:00:00', 'Banquet Hall'),
    (5, 'Cloud Architecture Seminar',    '2025-10-16', '09:00:00', '10:30:00', 'Room A'),
    (6, 'Cybersecurity Trends',          '2025-10-16', '11:00:00', '12:30:00', 'Room B'),
    (7, 'Open Source Software Talk',     '2025-10-16', '14:00:00', '15:30:00', 'Main Hall'),
    (8, 'Career Fair & Closing Remarks', '2025-10-16', '16:00:00', '18:00:00', 'Banquet Hall');

-- ------------------------------------------------------------
-- CommitteeMember (8 rows)
-- Must be inserted BEFORE SubCommittee (FK dependency)
-- ------------------------------------------------------------
INSERT INTO CommitteeMember (MemberID, FirstName, LastName) VALUES
    (1, 'Sarah',   'Chen'),
    (2, 'James',   'Lee'),
    (3, 'Maria',   'Garcia'),
    (4, 'John',    'Doe'),
    (5, 'Jane',    'Doe'),
    (6, 'Priya',   'Patel'),
    (7, 'Liam',    'Nguyen'),
    (8, 'Fatima',  'Al-Hassan');

-- ------------------------------------------------------------
-- SubCommittee (6 rows)
-- ChairMemberID must reference an existing CommitteeMember
-- ------------------------------------------------------------
INSERT INTO SubCommittee (CommitteeName, ChairMemberID) VALUES
    ('Program Committee',       1),
    ('Registration Committee',  2),
    ('Sponsorship Committee',   3),
    ('Logistics Committee',     4),
    ('Technical Review Committee', 6),
    ('Volunteer Committee',     7);

-- ------------------------------------------------------------
-- Attendee + subtypes
-- RoomNumberStaysIn must reference an existing HotelRoom
-- Inserted in three batches by type, then subtype rows follow
-- ------------------------------------------------------------

-- Students (AttendeeID 1–6)
INSERT INTO Attendee (AttendeeID, FirstName, LastName, Email, AttendeeType) VALUES
    (1,  'Alice',   'Johnson',  'alice.j@example.com',       'Student'),
    (2,  'Bob',     'Smith',    'bob.s@example.com',         'Student'),
    (3,  'Chloe',   'Martinez', 'chloe.m@example.com',       'Student'),
    (4,  'Daniel',  'Kim',      'daniel.k@example.com',      'Student'),
    (5,  'Ethan',   'Nguyen',   'ethan.n@example.com',       'Student'),
    (6,  'Fatima',  'Hassan',   'fatima.h@example.com',      'Student');

INSERT INTO Student (AttendeeID, RoomNumberStaysIn) VALUES (1, 102), (2, 102), (3, 301), (4, 301), (5, 302), (6, NULL);

-- Professionals (AttendeeID 7–12)
INSERT INTO Attendee (AttendeeID, FirstName, LastName, Email, AttendeeType) VALUES
    (7,  'Carol',   'Williams', 'carol.w@example.com',       'Professional'),
    (8,  'Dave',    'Brown',    'dave.b@example.com',         'Professional'),
    (9,  'Grace',   'Park',     'grace.p@example.com',        'Professional'),
    (10, 'Henry',   'Turner',   'henry.t@example.com',        'Professional'),
    (11, 'Isabelle','Roy',      'isabelle.r@example.com',     'Professional'),
    (12, 'Jordan',  'White',    'jordan.w@example.com',       'Professional');

INSERT INTO Professional (AttendeeID) VALUES (7), (8), (9), (10), (11), (12);

-- Sponsors (AttendeeID 13–18)
-- CompanyID must reference an existing Company
INSERT INTO Attendee (AttendeeID, FirstName, LastName, Email, AttendeeType) VALUES
    (13, 'Eve',     'Davis',    'eve.d@techcorp.com',         'Sponsor'),
    (14, 'Frank',   'Miller',   'frank.m@datasystems.com',    'Sponsor'),
    (15, 'George',  'Tanaka',   'george.t@blackberry.com',    'Sponsor'),
    (16, 'Hannah',  'Singh',    'hannah.s@kinaxis.com',       'Sponsor'),
    (17, 'Ivan',    'Petrov',   'ivan.p@d2l.com',             'Sponsor'),
    (18, 'Julia',   'Chen',     'julia.c@mitel.com',          'Sponsor');

INSERT INTO Sponsor (AttendeeID, SponsorLevel, EmailsSent, CompanyID) VALUES
    (13, 'Platinum', 2, 1),
    (14, 'Gold',     1, 2),
    (15, 'Silver',   3, 5),
    (16, 'Gold',     2, 6),
    (17, 'Bronze',   0, 7),
    (18, 'Silver',   1, 8);

-- ------------------------------------------------------------
-- Speaker (8 rows)
-- AttendeeID must reference an existing Attendee (or be NULL)
-- ------------------------------------------------------------
INSERT INTO Speaker (SpeakerID, FirstName, LastName, AttendeeID) VALUES
    (1, 'Carol',    'Williams', 7),     -- Also attendee #7
    (2, 'Fei-Fei', 'Li',       NULL),  -- External speaker
    (3, 'Dave',     'Brown',    8),     -- Also attendee #8
    (4, 'Yann',     'LeCun',    NULL),  -- External speaker
    (5, 'Grace',    'Park',     9),     -- Also attendee #9
    (6, 'Henry',    'Turner',   10),    -- Also attendee #10
    (7, 'Linus',    'Torvalds', NULL),  -- External speaker
    (8, 'Isabelle', 'Roy',      11);    -- Also attendee #11

-- ------------------------------------------------------------
-- JobAd (8 rows)
-- PostedByCompanyId must reference an existing Company
-- PK is (PostedByCompanyId, JobTitle) — must be unique per company
-- ------------------------------------------------------------
INSERT INTO JobAd (JobTitle, Location, City, Province, PayRate, PostedByCompanyID) VALUES
    ('Research Assistant',       'University Lab',    'Kingston',   'Ontario',           25.00, 1),
    ('Data Analyst',             'Downtown Office',   'Toronto',    'Ontario',           40.00, 2),
    ('Software Developer',       'Remote',            'Vancouver',  'British Columbia',  55.00, 3),
    ('Security Engineer',        'Head Office',       'Waterloo',   'Ontario',           70.00, 5),
    ('Product Manager',          'Remote',            'Ottawa',     'Ontario',           80.00, 6),
    ('UX Designer',              'Downtown Office',   'Kitchener',  'Ontario',           50.00, 7),
    ('DevOps Engineer',          'Remote',            'Toronto',    'Ontario',           75.00, 4),
    ('Machine Learning Engineer','Innovation Hub',    'Montreal',   'Quebec',            90.00, 8);

-- ------------------------------------------------------------
-- MemberOfCommittee (8 rows)
-- Both MemberID and CommitteeID must reference existing rows
-- ------------------------------------------------------------
INSERT INTO MemberOfCommittee (MemberID, CommitteeID) VALUES
    (1, 1),
    (2, 1),
    (3, 1),
    (2, 2),
    (3, 2),
    (4, 2),
    (3, 3),
    (4, 3),
    (5, 3),
    (4, 4),
    (5, 4),
    (6, 4),
    (6, 5),
    (7, 5),
    (8, 5),
    (7, 6),
    (8, 6),
    (1, 6);

-- ------------------------------------------------------------
-- Attends (Attendee <-> Session) — broad coverage
-- ------------------------------------------------------------
INSERT INTO Attends (AttendeeID, SessionID) VALUES
    (1,  1), (1,  2), (1,  3),
    (2,  1), (2,  3), (2,  6),
    (3,  1), (3,  4), (3,  7),
    (4,  2), (4,  5), (4,  8),
    (5,  1), (5,  2), (5,  7),
    (6,  3), (6,  6), (7,  1), 
    (7,  2), (7,  4),
    (8,  1), (8,  5),
    (9,  2), (9,  3), (9,  6),
    (10, 1), (10, 7), (10, 8),
    (11, 4), (11, 5),
    (12, 1), (12, 8),
    (13, 1), (13, 4),
    (14, 1), (14, 4),
    (15, 1), (15, 7),
    (16, 1), (16, 8),
    (17, 4), (17, 8),
    (18, 1), (18, 4);

-- ------------------------------------------------------------
-- SpeaksAt (Speaker <-> Session)
-- ------------------------------------------------------------
INSERT INTO SpeaksAt (SpeakerID, SessionID) VALUES
    (1, 2),     -- Carol speaks at ML Workshop
    (2, 1),     -- Fei-Fei Li speaks at Keynote
    (2, 3),     -- Fei-Fei Li speaks at DB Panel
    (3, 5),     -- Dave speaks at Cloud Seminar
    (4, 6),     -- Yann LeCun speaks at Cybersecurity Trends
    (5, 3),     -- Grace Park speaks at DB Panel
    (6, 7),     -- Henry speaks at Open Source Talk
    (7, 7),     -- Linus speaks at Open Source Talk
    (8, 4),     -- Isabelle speaks at Networking Event
    (1, 8);     -- Carol also speaks at Career Fair
