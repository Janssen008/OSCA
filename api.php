<?php
// OSCA Local Database REST API
error_reporting(0);
ini_set('display_errors', '0');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';

$action = $_GET['action'] ?? '';
$inputData = json_decode(file_get_contents('php://input'), true);

switch ($action) {
    case 'get_seniors':
        try {
            $stmt = $pdo->query("SELECT * FROM `seniors` ORDER BY `no` ASC, `id` ASC");
            $rows = $stmt->fetchAll();
            
            $formatted = array_map(function($r) {
                $raw = !empty($r['raw_data_json']) ? json_decode($r['raw_data_json'], true) : null;
                return [
                    'no' => $r['no'] ?? null,
                    'id' => $r['osca_id'] ?? '',
                    'name_of_members' => $r['name_of_members'] ?? '',
                    'firstname' => $r['first_name'] ?? '',
                    'middlename' => $r['middle_name'] ?? '',
                    'lastname' => $r['last_name'] ?? '',
                    'extension' => $r['extension'] ?? '',
                    'dob_month' => $r['dob_month'] ?? '',
                    'dob_day' => $r['dob_day'] ?? null,
                    'dob_year' => $r['dob_year'] ?? null,
                    'dob' => $r['dob'] ?? '',
                    'age' => (int)($r['age'] ?? 0),
                    'gender' => $r['sex'] ?? 'Male',
                    'sex' => $r['sex'] ?? 'Male',
                    'civilstatus' => $r['civil_status'] ?? 'Married',
                    'barangay' => $r['barangay'] ?? 'Singalat',
                    'address' => $r['address'] ?? '',
                    'contact' => $r['contact_no'] ?? '',
                    'emergency' => $r['emergency_contact'] ?? '',
                    'philhealth' => $r['philhealth_no'] ?? '',
                    'pensioner' => $r['pensioner_status'] ?? 'No',
                    'booklet' => $r['booklet_no'] ?? '',
                    'status' => $r['status'] ?? 'Active',
                    'remarks' => $r['remarks'] ?? '',
                    '_rawObject' => $raw
                ];
            }, $rows);
            
            echo json_encode(['status' => 'success', 'data' => $formatted]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'save_senior':
        try {
            if (!$inputData) throw new Exception('Invalid JSON input.');
            
            $no = isset($inputData['no']) ? (int)$inputData['no'] : null;
            $osca_id = $inputData['id'] ?? '';
            $first_name = $inputData['firstname'] ?? '';
            $middle_name = $inputData['middlename'] ?? '';
            $last_name = $inputData['lastname'] ?? '';
            $extension = $inputData['extension'] ?? '';
            $name_of_members = $inputData['name_of_members'] ?? trim("$last_name, $first_name $middle_name");
            $dob_month = $inputData['dob_month'] ?? '';
            $dob_day = isset($inputData['dob_day']) ? (int)$inputData['dob_day'] : null;
            $dob_year = isset($inputData['dob_year']) ? (int)$inputData['dob_year'] : null;
            $dob = !empty($inputData['dob']) ? $inputData['dob'] : null;
            $age = (int)($inputData['age'] ?? 0);
            $sex = $inputData['gender'] ?? ($inputData['sex'] ?? 'Male');
            $civil_status = $inputData['civilstatus'] ?? 'Married';
            $barangay = $inputData['barangay'] ?? 'Singalat';
            $address = $inputData['address'] ?? '';
            $contact_no = $inputData['contact'] ?? '';
            $emergency_contact = $inputData['emergency'] ?? '';
            $philhealth_no = $inputData['philhealth'] ?? '';
            $pensioner_status = $inputData['pensioner'] ?? 'No';
            $booklet_no = $inputData['booklet'] ?? '';
            $status = $inputData['status'] ?? 'Active';
            $remarks = $inputData['remarks'] ?? '';
            $raw_json = isset($inputData['_rawObject']) ? json_encode($inputData['_rawObject']) : null;

            $sql = "INSERT INTO `seniors` 
                (`no`, `osca_id`, `name_of_members`, `first_name`, `middle_name`, `last_name`, `extension`, `dob_month`, `dob_day`, `dob_year`, `dob`, `age`, `sex`, `civil_status`, `barangay`, `address`, `contact_no`, `emergency_contact`, `philhealth_no`, `pensioner_status`, `booklet_no`, `status`, `remarks`, `raw_data_json`)
                VALUES 
                (:no, :osca_id, :name_of_members, :first_name, :middle_name, :last_name, :extension, :dob_month, :dob_day, :dob_year, :dob, :age, :sex, :civil_status, :barangay, :address, :contact_no, :emergency_contact, :philhealth_no, :pensioner_status, :booklet_no, :status, :remarks, :raw_json)
                ON DUPLICATE KEY UPDATE 
                `no` = VALUES(`no`),
                `name_of_members` = VALUES(`name_of_members`),
                `first_name` = VALUES(`first_name`),
                `middle_name` = VALUES(`middle_name`),
                `last_name` = VALUES(`last_name`),
                `extension` = VALUES(`extension`),
                `dob_month` = VALUES(`dob_month`),
                `dob_day` = VALUES(`dob_day`),
                `dob_year` = VALUES(`dob_year`),
                `dob` = VALUES(`dob`),
                `age` = VALUES(`age`),
                `sex` = VALUES(`sex`),
                `civil_status` = VALUES(`civil_status`),
                `barangay` = VALUES(`barangay`),
                `address` = VALUES(`address`),
                `contact_no` = VALUES(`contact_no`),
                `emergency_contact` = VALUES(`emergency_contact`),
                `philhealth_no` = VALUES(`philhealth_no`),
                `pensioner_status` = VALUES(`pensioner_status`),
                `booklet_no` = VALUES(`booklet_no`),
                `status` = VALUES(`status`),
                `remarks` = VALUES(`remarks`),
                `raw_data_json` = VALUES(`raw_data_json`)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':no' => $no,
                ':osca_id' => $osca_id,
                ':name_of_members' => $name_of_members,
                ':first_name' => $first_name,
                ':middle_name' => $middle_name,
                ':last_name' => $last_name,
                ':extension' => $extension,
                ':dob_month' => $dob_month,
                ':dob_day' => $dob_day,
                ':dob_year' => $dob_year,
                ':dob' => $dob,
                ':age' => $age,
                ':sex' => $sex,
                ':civil_status' => $civil_status,
                ':barangay' => $barangay,
                ':address' => $address,
                ':contact_no' => $contact_no,
                ':emergency_contact' => $emergency_contact,
                ':philhealth_no' => $philhealth_no,
                ':pensioner_status' => $pensioner_status,
                ':booklet_no' => $booklet_no,
                ':status' => $status,
                ':remarks' => $remarks,
                ':raw_json' => $raw_json
            ]);

            echo json_encode(['status' => 'success', 'message' => 'Record saved successfully in MySQL!']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'import_batch':
        try {
            if (!isset($inputData['items']) || !is_array($inputData['items'])) {
                throw new Exception('Invalid batch data payload.');
            }

            $pdo->beginTransaction();
            
            $sql = "INSERT INTO `seniors` 
                (`no`, `osca_id`, `name_of_members`, `first_name`, `middle_name`, `last_name`, `extension`, `dob_month`, `dob_day`, `dob_year`, `dob`, `age`, `sex`, `barangay`, `address`, `philhealth_no`, `status`, `remarks`, `raw_data_json`)
                VALUES 
                (:no, :osca_id, :name_of_members, :first_name, :middle_name, :last_name, :extension, :dob_month, :dob_day, :dob_year, :dob, :age, :sex, :barangay, :address, :philhealth_no, :status, :remarks, :raw_json)
                ON DUPLICATE KEY UPDATE 
                `no` = VALUES(`no`),
                `name_of_members` = VALUES(`name_of_members`),
                `first_name` = VALUES(`first_name`),
                `middle_name` = VALUES(`middle_name`),
                `last_name` = VALUES(`last_name`),
                `dob_month` = VALUES(`dob_month`),
                `dob_day` = VALUES(`dob_day`),
                `dob_year` = VALUES(`dob_year`),
                `dob` = VALUES(`dob`),
                `age` = VALUES(`age`),
                `sex` = VALUES(`sex`),
                `barangay` = VALUES(`barangay`),
                `philhealth_no` = VALUES(`philhealth_no`),
                `status` = VALUES(`status`),
                `remarks` = VALUES(`remarks`),
                `raw_data_json` = VALUES(`raw_data_json`)";
            
            $stmt = $pdo->prepare($sql);

            $importedCount = 0;
            foreach ($inputData['items'] as $item) {
                $first_name = $item['firstname'] ?? '';
                $last_name = $item['lastname'] ?? '';
                $middle_name = $item['middlename'] ?? '';
                $name_of_members = $item['name_of_members'] ?? trim("$last_name, $first_name $middle_name");
                $dob = !empty($item['dob']) ? $item['dob'] : null;
                $raw_json = isset($item['_rawObject']) ? json_encode($item['_rawObject']) : null;

                $stmt->execute([
                    ':no' => isset($item['no']) ? (int)$item['no'] : null,
                    ':osca_id' => $item['id'],
                    ':name_of_members' => $name_of_members,
                    ':first_name' => $first_name,
                    ':middle_name' => $middle_name,
                    ':last_name' => $last_name,
                    ':extension' => $item['extension'] ?? '',
                    ':dob_month' => $item['dob_month'] ?? null,
                    ':dob_day' => isset($item['dob_day']) ? (int)$item['dob_day'] : null,
                    ':dob_year' => isset($item['dob_year']) ? (int)$item['dob_year'] : null,
                    ':dob' => $dob,
                    ':age' => (int)($item['age'] ?? 0),
                    ':sex' => $item['gender'] ?? ($item['sex'] ?? 'Male'),
                    ':barangay' => $item['barangay'] ?? 'Singalat',
                    ':address' => $item['address'] ?? '',
                    ':philhealth_no' => $item['philhealth'] ?? '',
                    ':status' => $item['status'] ?? 'Active',
                    ':remarks' => $item['remarks'] ?? '',
                    ':raw_json' => $raw_json
                ]);
                $importedCount++;
            }

            $pdo->commit();
            echo json_encode(['status' => 'success', 'count' => $importedCount, 'message' => "Imported $importedCount records into phpMyAdmin / MySQL!"]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'delete_senior':
        try {
            $osca_id = $_GET['id'] ?? $inputData['id'] ?? '';
            if (!$osca_id) throw new Exception('Missing OSCA ID.');

            $stmt = $pdo->prepare("DELETE FROM `seniors` WHERE `osca_id` = :id");
            $stmt->execute([':id' => $osca_id]);

            echo json_encode(['status' => 'success', 'message' => 'Record deleted from MySQL.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'get_claims':
        try {
            $stmt = $pdo->query("SELECT * FROM `benefit_claims` ORDER BY `id` DESC");
            $rows = $stmt->fetchAll();
            $formatted = array_map(function($r) {
                return [
                    'date' => $r['claimed_at'],
                    'id' => $r['osca_id'],
                    'name' => $r['senior_name'],
                    'type' => $r['benefit_type'],
                    'amount' => $r['amount'],
                    'officer' => $r['officer']
                ];
            }, $rows);
            echo json_encode(['status' => 'success', 'data' => $formatted]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'save_claim':
        try {
            if (!$inputData) throw new Exception('Invalid claim data.');
            $stmt = $pdo->prepare("INSERT INTO `benefit_claims` (`osca_id`, `senior_name`, `benefit_type`, `amount`, `officer`) VALUES (:osca_id, :senior_name, :benefit_type, :amount, :officer)");
            $stmt->execute([
                ':osca_id' => $inputData['id'],
                ':senior_name' => $inputData['name'],
                ':benefit_type' => $inputData['type'],
                ':amount' => $inputData['amount'],
                ':officer' => $inputData['officer']
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Benefit claim saved in MySQL!']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid API action.']);
        break;
}
?>
