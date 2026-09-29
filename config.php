<?php
// OSCA Database Connection & Auto-Setup Config

$db_host = '127.0.0.1';
$db_user = 'root';
$db_pass = '';
$db_name = 'osca_db';

try {
    $pdo_server = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    $pdo_server->exec("CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Create seniors table matching exact guide format
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `seniors` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `no` INT DEFAULT NULL,
          `osca_id` VARCHAR(50) NOT NULL UNIQUE,
          `name_of_members` VARCHAR(255) NOT NULL,
          `last_name` VARCHAR(100) DEFAULT NULL,
          `first_name` VARCHAR(100) DEFAULT NULL,
          `middle_name` VARCHAR(100) DEFAULT NULL,
          `extension` VARCHAR(20) DEFAULT NULL,
          `dob_month` VARCHAR(20) DEFAULT NULL,
          `dob_day` INT DEFAULT NULL,
          `dob_year` INT DEFAULT NULL,
          `dob` DATE DEFAULT NULL,
          `age` INT DEFAULT NULL,
          `sex` VARCHAR(20) DEFAULT 'Male',
          `civil_status` VARCHAR(30) DEFAULT 'Married',
          `barangay` VARCHAR(100) NOT NULL DEFAULT 'Singalat',
          `address` TEXT DEFAULT NULL,
          `contact_no` VARCHAR(50) DEFAULT NULL,
          `emergency_contact` VARCHAR(150) DEFAULT NULL,
          `philhealth_no` VARCHAR(50) DEFAULT NULL,
          `pensioner_status` VARCHAR(20) DEFAULT 'No',
          `booklet_no` VARCHAR(50) DEFAULT NULL,
          `status` ENUM('Active', 'Pending', 'Suspended', 'Deceased', 'Transferred') DEFAULT 'Active',
          `remarks` TEXT DEFAULT NULL,
          `raw_data_json` LONGTEXT DEFAULT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `benefit_claims` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `osca_id` VARCHAR(50) NOT NULL,
          `senior_name` VARCHAR(255) NOT NULL,
          `benefit_type` VARCHAR(150) NOT NULL,
          `amount` VARCHAR(100) NOT NULL,
          `officer` VARCHAR(100) NOT NULL,
          `claimed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Database Connection Failed: ' . $e->getMessage()
    ]);
    exit;
}
?>
