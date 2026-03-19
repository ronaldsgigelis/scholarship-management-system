CREATE DATABASE IF NOT EXISTS stipendiju_sistema
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE stipendiju_sistema;

CREATE TABLE IF NOT EXISTS `groups` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `group_name` VARCHAR(100) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_groups_group_name` (`group_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `students` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `group_id` INT UNSIGNED NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `personal_code` VARCHAR(50) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_students_personal_code` (`personal_code`),
    KEY `idx_students_group_id` (`group_id`),
    CONSTRAINT `fk_students_group`
        FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `subjects` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `subject_name` VARCHAR(150) NOT NULL,
    `category_type` ENUM('P', 'V') NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_subjects_subject_name` (`subject_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `group_subjects` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `group_id` INT UNSIGNED NOT NULL,
    `subject_id` INT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_group_subjects_group_subject` (`group_id`, `subject_id`),
    KEY `idx_group_subjects_subject_id` (`subject_id`),
    CONSTRAINT `fk_group_subjects_group`
        FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT `fk_group_subjects_subject`
        FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `stipend_periods` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `year` SMALLINT UNSIGNED NOT NULL,
    `period` VARCHAR(50) NOT NULL,
    `period_group` VARCHAR(50) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_stipend_periods_year_period_group` (`year`, `period`, `period_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `student_stipend_records` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT UNSIGNED NOT NULL,
    `group_id` INT UNSIGNED NOT NULL,
    `period_id` INT UNSIGNED NOT NULL,
    `average_grade` DECIMAL(4,2) NOT NULL DEFAULT 0.00,
    `failed_subjects_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `absences` INT UNSIGNED NOT NULL DEFAULT 0,
    `base_stipend` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    `activity_bonus` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    `total_stipend` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_student_stipend_records_student_period` (`student_id`, `period_id`),
    KEY `idx_student_stipend_records_group_id` (`group_id`),
    KEY `idx_student_stipend_records_period_id` (`period_id`),
    CONSTRAINT `fk_student_stipend_records_student`
        FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT `fk_student_stipend_records_group`
        FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT `fk_student_stipend_records_period`
        FOREIGN KEY (`period_id`) REFERENCES `stipend_periods` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `student_grades` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `stipend_record_id` INT UNSIGNED NOT NULL,
    `subject_id` INT UNSIGNED NOT NULL,
    `grade` DECIMAL(4,2) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_student_grades_record_subject` (`stipend_record_id`, `subject_id`),
    KEY `idx_student_grades_subject_id` (`subject_id`),
    CONSTRAINT `fk_student_grades_stipend_record`
        FOREIGN KEY (`stipend_record_id`) REFERENCES `student_stipend_records` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT `fk_student_grades_subject`
        FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `activity_bonus_records` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `stipend_record_id` INT UNSIGNED NOT NULL,
    `activity_description` VARCHAR(255) NOT NULL,
    `bonus_amount` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    `period_start` DATE NOT NULL,
    `period_end` DATE NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_activity_bonus_records_stipend_record_id` (`stipend_record_id`),
    CONSTRAINT `fk_activity_bonus_records_stipend_record`
        FOREIGN KEY (`stipend_record_id`) REFERENCES `student_stipend_records` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
