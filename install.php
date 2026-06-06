<?php
/**
 * One-time setup: open http://localhost/lirary/install.php in your browser
 * after starting Apache + MySQL in XAMPP.
 */
header('Content-Type: text/html; charset=utf-8');

$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'blockshelf_db';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
    ]);

    $sql = file_get_contents(__DIR__ . '/blockshelf_db.sql');
    $pdo->exec($sql);

    echo '<h1>BlockShelf Library — Setup complete</h1>';
    echo '<p>Database <strong>' . htmlspecialchars($dbName) . '</strong> is ready.</p>';
    echo '<p><a href="index.html">Go to website</a></p>';
    echo '<p style="color:#666;">You can delete install.php after setup.</p>';
} catch (PDOException $e) {
    http_response_code(500);
    echo '<h1>Setup failed</h1>';
    echo '<p>Make sure Apache and MySQL are running in XAMPP.</p>';
    echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
}
