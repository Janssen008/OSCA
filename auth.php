<?php
// OSCA Authentication API
error_reporting(0);
ini_set('display_errors', '0');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

require_once 'config.php';

// Auto-create users table + default admin account
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `osca_users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(80) NOT NULL UNIQUE,
            `password_hash` VARCHAR(255) NOT NULL,
            `full_name` VARCHAR(150) NOT NULL DEFAULT 'Administrator',
            `role` ENUM('admin','officer','viewer') DEFAULT 'officer',
            `auth_token` VARCHAR(128) DEFAULT NULL,
            `token_expires` DATETIME DEFAULT NULL,
            `last_login` DATETIME DEFAULT NULL,
            `is_active` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Insert default admin if not exists
    $check = $pdo->query("SELECT COUNT(*) FROM osca_users")->fetchColumn();
    if ($check == 0) {
        $stmt = $pdo->prepare("INSERT INTO osca_users (username, password_hash, full_name, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['admin',    password_hash('admin123', PASSWORD_BCRYPT), 'System Administrator', 'admin']);
        $stmt->execute(['officer',  password_hash('osca2024', PASSWORD_BCRYPT), 'OSCA Officer',         'officer']);
        $stmt->execute(['viewer',   password_hash('viewer123', PASSWORD_BCRYPT), 'Read-Only Viewer',    'viewer']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'User table setup failed: ' . $e->getMessage()]);
    exit;
}

$action    = $_GET['action'] ?? '';
$authToken = $_SERVER['HTTP_X_AUTH_TOKEN'] ?? $_GET['token'] ?? '';
$input     = json_decode(file_get_contents('php://input'), true) ?? [];

// ─── Helper: generate secure token ───────────────────────────────────────────
function generateToken() {
    return bin2hex(random_bytes(32));
}

// ─── Helper: verify token from header/query ───────────────────────────────────
function verifyToken($pdo, $token) {
    if (!$token) return null;
    $stmt = $pdo->prepare("SELECT * FROM osca_users WHERE auth_token = ? AND token_expires > NOW() AND is_active = 1");
    $stmt->execute([$token]);
    return $stmt->fetch() ?: null;
}

switch ($action) {

    // ── LOGIN ─────────────────────────────────────────────────────────────────
    case 'login':
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        if (!$username || !$password) {
            echo json_encode(['status' => 'error', 'message' => 'Username and password are required.']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM osca_users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid username or password.']);
            exit;
        }

        // Generate token — expires in 8 hours
        $token   = generateToken();
        $expires = date('Y-m-d H:i:s', time() + (8 * 3600));

        $pdo->prepare("UPDATE osca_users SET auth_token = ?, token_expires = ?, last_login = NOW() WHERE id = ?")
            ->execute([$token, $expires, $user['id']]);

        echo json_encode([
            'status'    => 'success',
            'token'     => $token,
            'expires'   => $expires,
            'user'      => [
                'id'        => $user['id'],
                'username'  => $user['username'],
                'full_name' => $user['full_name'],
                'role'      => $user['role'],
            ]
        ]);
        break;

    // ── VERIFY TOKEN ──────────────────────────────────────────────────────────
    case 'verify':
        $user = verifyToken($pdo, $authToken);
        if ($user) {
            echo json_encode([
                'status' => 'success',
                'user'   => [
                    'id'        => $user['id'],
                    'username'  => $user['username'],
                    'full_name' => $user['full_name'],
                    'role'      => $user['role'],
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid or expired session. Please log in again.']);
        }
        break;

    // ── LOGOUT ────────────────────────────────────────────────────────────────
    case 'logout':
        if ($authToken) {
            $pdo->prepare("UPDATE osca_users SET auth_token = NULL, token_expires = NULL WHERE auth_token = ?")
                ->execute([$authToken]);
        }
        echo json_encode(['status' => 'success', 'message' => 'Logged out successfully.']);
        break;

    // ── CHANGE PASSWORD ───────────────────────────────────────────────────────
    case 'change_password':
        $user = verifyToken($pdo, $authToken);
        if (!$user) { echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']); exit; }

        $oldPass = $input['old_password'] ?? '';
        $newPass = $input['new_password'] ?? '';
        if (!$oldPass || !$newPass || strlen($newPass) < 6) {
            echo json_encode(['status' => 'error', 'message' => 'New password must be at least 6 characters.']);
            exit;
        }
        if (!password_verify($oldPass, $user['password_hash'])) {
            echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect.']);
            exit;
        }

        $pdo->prepare("UPDATE osca_users SET password_hash = ? WHERE id = ?")
            ->execute([password_hash($newPass, PASSWORD_BCRYPT), $user['id']]);
        echo json_encode(['status' => 'success', 'message' => 'Password changed successfully.']);
        break;

    // ── GET USERS (admin only) ────────────────────────────────────────────────
    case 'get_users':
        $user = verifyToken($pdo, $authToken);
        if (!$user || $user['role'] !== 'admin') { echo json_encode(['status' => 'error', 'message' => 'Admin access required.']); exit; }
        $rows = $pdo->query("SELECT id, username, full_name, role, last_login, is_active, created_at FROM osca_users ORDER BY id")->fetchAll();
        echo json_encode(['status' => 'success', 'data' => $rows]);
        break;

    // ── ADD USER (admin only) ─────────────────────────────────────────────────
    case 'add_user':
        $user = verifyToken($pdo, $authToken);
        if (!$user || $user['role'] !== 'admin') { echo json_encode(['status' => 'error', 'message' => 'Admin access required.']); exit; }
        $newUsername  = trim($input['username'] ?? '');
        $newPassword  = $input['password'] ?? '';
        $newFullName  = trim($input['full_name'] ?? 'OSCA Staff');
        $newRole      = in_array($input['role'] ?? '', ['admin','officer','viewer']) ? $input['role'] : 'officer';
        if (!$newUsername || !$newPassword || strlen($newPassword) < 6) {
            echo json_encode(['status' => 'error', 'message' => 'Username and password (min 6 chars) are required.']);
            exit;
        }
        try {
            $pdo->prepare("INSERT INTO osca_users (username, password_hash, full_name, role) VALUES (?,?,?,?)")
                ->execute([$newUsername, password_hash($newPassword, PASSWORD_BCRYPT), $newFullName, $newRole]);
            echo json_encode(['status' => 'success', 'message' => "User '$newUsername' created successfully."]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Username already exists.']);
        }
        break;

    // ── TOGGLE USER ACTIVE ────────────────────────────────────────────────────
    case 'toggle_user':
        $user = verifyToken($pdo, $authToken);
        if (!$user || $user['role'] !== 'admin') { echo json_encode(['status' => 'error', 'message' => 'Admin access required.']); exit; }
        $targetId = (int)($input['id'] ?? 0);
        if ($targetId === (int)$user['id']) { echo json_encode(['status' => 'error', 'message' => 'Cannot deactivate your own account.']); exit; }
        $pdo->prepare("UPDATE osca_users SET is_active = NOT is_active WHERE id = ?")->execute([$targetId]);
        echo json_encode(['status' => 'success', 'message' => 'User status updated.']);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Unknown auth action.']);
}
?>
