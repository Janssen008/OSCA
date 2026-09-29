-- ========================================================
-- OSCA SENIOR CITIZEN VERIFICATION SYSTEM DATABASE SCHEMA
-- Exactly aligned with OSCA Masterlist Guide Header Layout:
-- [No. | Name of Members | Date of Birth (Month, Day, Year) | Sex | ID Number | Philhealth Number | Remarks]
-- ========================================================

CREATE DATABASE IF NOT EXISTS `osca_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `osca_db`;

-- --------------------------------------------------------
-- Table structure for `seniors`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `seniors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `no` INT DEFAULT NULL,
  `osca_id` VARCHAR(50) NOT NULL UNIQUE,
  `name_of_members` VARCHAR(255) NOT NULL,
  `last_name` VARCHAR(100) DEFAULT NULL,
  `first_name` VARCHAR(100) DEFAULT NULL,
  `middle_name` VARCHAR(100) DEFAULT NULL,
  `dob_month` VARCHAR(20) DEFAULT NULL,
  `dob_day` INT DEFAULT NULL,
  `dob_year` INT DEFAULT NULL,
  `dob` DATE DEFAULT NULL,
  `age` INT DEFAULT NULL,
  `sex` VARCHAR(20) DEFAULT 'Male',
  `philhealth_no` VARCHAR(50) DEFAULT NULL,
  `barangay` VARCHAR(100) DEFAULT 'Singalat',
  `status` ENUM('Active', 'Pending', 'Suspended', 'Deceased', 'Transferred') DEFAULT 'Active',
  `remarks` TEXT DEFAULT NULL,
  `pensioner_status` VARCHAR(20) DEFAULT 'No',
  `raw_data_json` LONGTEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `benefit_claims`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `benefit_claims` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `osca_id` VARCHAR(50) NOT NULL,
  `senior_name` VARCHAR(255) NOT NULL,
  `benefit_type` VARCHAR(150) NOT NULL,
  `amount` VARCHAR(100) NOT NULL,
  `officer` VARCHAR(100) NOT NULL,
  `claimed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Initial Data matching exact guide layout
-- --------------------------------------------------------
INSERT INTO `seniors` 
(`no`, `osca_id`, `name_of_members`, `last_name`, `first_name`, `middle_name`, `dob_month`, `dob_day`, `dob_year`, `dob`, `age`, `sex`, `philhealth_no`, `barangay`, `status`, `remarks`)
VALUES
(1, '22-1678', 'Angway, Teresita Navarro', 'Angway', 'Teresita', 'Navarro', 'January', 12, 1962, '1962-01-12', 64, 'Female', '', 'Singalat', 'Active', ''),
(2, '18-1044', 'Angeles, Magno Cabrera', 'Angeles', 'Magno', 'Cabrera', 'January', 29, 1959, '1959-01-29', 67, 'Male', '', 'Singalat', 'Active', ''),
(3, '24-2589', 'Balbin,Juan Malagbangan', 'Balbin', 'Juan', 'Malagbangan', 'January', 27, 1956, '1956-01-27', 70, 'Male', '21-175322207-4', 'Singalat', 'Active', ''),
(4, '1617', 'De Belen ,Paulina Dela Cruz', 'De Belen', 'Paulina', 'Dela Cruz', 'January', 1, 1946, '1946-01-01', 80, 'Female', '21750439811', 'Singalat', 'Active', ''),
(5, '23-2179', 'Fajardo, Ferdinand Calimlim', 'Fajardo', 'Ferdinand', 'Calimlim', 'January', 26, 1963, '1963-01-26', 63, 'Male', '21-175309524-2', 'Singalat', 'Active', ''),
(6, '24-2575', 'Guerrero,Bienvenido Domingo', 'Guerrero', 'Bienvenido', 'Domingo', 'January', 22, 1964, '1964-01-22', 62, 'Male', '23-001808323-5', 'Singalat', 'Active', ''),
(7, '18-0894', 'Lagasca, Hilario Pimentel', 'Lagasca', 'Hilario', 'Pimentel', 'January', 14, 1960, '1960-01-14', 66, 'Male', '', 'Singalat', 'Active', '')
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`);
