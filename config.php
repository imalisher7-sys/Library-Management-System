<?php
// XAMPP default settings — change if your MySQL password is different
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'blockshelf_db');

function getDB(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            ensureSchema($pdo);
        } catch (PDOException $e) {
            throw new PDOException(mapDbError($e), (int) $e->getCode(), $e);
        }
    }
    return $pdo;
}

function mapDbError(PDOException $e): string
{
    $msg = $e->getMessage();
    if (str_contains($msg, 'Unknown database')) {
        return 'Database not found. Open http://localhost/lirary/install.php first.';
    }
    if (str_contains($msg, 'Access denied')) {
        return 'MySQL login failed. Check DB_USER and DB_PASS in config.php.';
    }
    if (str_contains($msg, 'Connection refused') || str_contains($msg, 'actively refused')) {
        return 'MySQL is not running. Start MySQL in XAMPP Control Panel.';
    }
    return 'Database connection failed. Make sure Apache and MySQL are running in XAMPP.';
}

function ensureSchema(PDO $pdo): void
{
    $tables = $pdo->query("SHOW TABLES LIKE 'member'")->fetchAll();
    if (count($tables) === 0) {
        throw new PDOException('Tables not found. Open http://localhost/lirary/install.php first.');
    }

    $columns = $pdo->query("SHOW COLUMNS FROM member LIKE 'Password'")->fetchAll();
    if (count($columns) === 0) {
        $pdo->exec('ALTER TABLE member ADD COLUMN Password varchar(255) DEFAULT NULL AFTER Phone_No');
    }
}

function jsonResponse(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function readJsonBody(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '', true);
    if (!is_array($data)) {
        jsonResponse(['success' => false, 'message' => 'Invalid request data.'], 400);
    }
    return $data;
}

function requireLogin(): array
{
    if (empty($_SESSION['user_id'])) {
        jsonResponse(['success' => false, 'message' => 'Please log in first.'], 401);
    }
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
    ];
}
