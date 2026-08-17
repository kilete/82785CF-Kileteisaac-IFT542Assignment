-- ============================================================
-- IFT542 Student Registration Web Application - Database Schema
-- ============================================================
-- Import order: schema.sql first, then seed.sql
-- Run in phpMyAdmin or: mysql -u root -p < schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS student_registration
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE student_registration;

-- ------------------------------------------------------------
-- Users table (students + admins share one auth table)
-- Passwords are stored as Argon2id hashes (see includes/auth.php)
-- ------------------------------------------------------------
CREATE TABLE users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    matric_no       VARCHAR(20)  NULL UNIQUE,              -- NULL for admin accounts
    full_name       VARCHAR(100) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    role            ENUM('student','admin') NOT NULL DEFAULT 'student',
    failed_attempts INT UNSIGNED NOT NULL DEFAULT 0,        -- for lockout control
    locked_until    DATETIME NULL,                          -- temporary lockout
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                     ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Courses
-- ------------------------------------------------------------
CREATE TABLE courses (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_code  VARCHAR(20)  NOT NULL UNIQUE,
    title        VARCHAR(150) NOT NULL,
    units        TINYINT UNSIGNED NOT NULL DEFAULT 3,
    capacity     INT UNSIGNED NOT NULL DEFAULT 50,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Enrolments (course registration) - the CSRF-protected action
-- ------------------------------------------------------------
CREATE TABLE enrolments (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED NOT NULL,
    course_id    INT UNSIGNED NOT NULL,
    status       ENUM('active','dropped') NOT NULL DEFAULT 'active',
    registered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_course (user_id, course_id),
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Document uploads (transcripts, ID, etc.)
-- ------------------------------------------------------------
CREATE TABLE documents (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name   VARCHAR(255) NOT NULL,   -- randomised name on disk, never trust original
    mime_type     VARCHAR(100) NOT NULL,
    size_bytes    INT UNSIGNED NOT NULL,
    uploaded_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Security audit log (Task 3: who/what/when, no secrets)
-- ------------------------------------------------------------
CREATE TABLE audit_log (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_type  VARCHAR(50) NOT NULL,        -- e.g. LOGIN_FAIL, LOGIN_SUCCESS, ACCESS_DENIED, VALIDATION_REJECTED
    username    VARCHAR(150) NULL,           -- email/matric attempted, never password
    ip_address  VARCHAR(45) NULL,
    detail      VARCHAR(255) NULL,           -- short, no secrets/PII beyond identifier
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
