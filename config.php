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
        autoInstallDatabase();
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

function autoInstallDatabase(): void
{
    static $ran = false;
    if ($ran) {
        return;
    }
    $ran = true;

    $root = new PDO(
        'mysql:host=' . DB_HOST . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
        ]
    );

    $stmt = $root->prepare('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?');
    $stmt->execute([DB_NAME]);
    $exists = (bool) $stmt->fetch(PDO::FETCH_ASSOC);

    if ($exists) {
        $root->exec('USE `' . DB_NAME . '`');
        $tables = $root->query("SHOW TABLES LIKE 'member'")->fetchAll();
        if (count($tables) > 0) {
            return;
        }
    }

    $sqlFile = __DIR__ . '/blockshelf_db.sql';
    if (!is_readable($sqlFile)) {
        throw new PDOException('Setup file blockshelf_db.sql is missing from the project folder.');
    }

    $root->exec(file_get_contents($sqlFile));
}

function mapDbError(PDOException $e): string
{
    $msg = $e->getMessage();
    if (str_contains($msg, 'Access denied')) {
        return 'MySQL login failed. Check DB_USER and DB_PASS in config.php.';
    }
    if (str_contains($msg, 'Connection refused') || str_contains($msg, 'actively refused')) {
        return 'MySQL is not running. Start MySQL in XAMPP Control Panel.';
    }
    if (str_contains($msg, 'Unknown database')) {
        return 'Database setup failed. Make sure MySQL is running in XAMPP and try again.';
    }
    return 'Database connection failed. Start Apache and MySQL in XAMPP, then refresh the page.';
}

function ensureSchema(PDO $pdo): void
{
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
