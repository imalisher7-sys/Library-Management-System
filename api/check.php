<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

try {
    $pdo = getDB();
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    jsonResponse([
        'success' => true,
        'message' => 'PHP and database are connected.',
        'database' => DB_NAME,
        'tables' => $tables,
    ]);
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
