<?php
/**
 * Optional manual setup — database also creates automatically on first visit.
 */
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/config.php';

try {
    getDB();
    echo '<h1>BlockShelf Library — Ready</h1>';
    echo '<p>Database <strong>' . htmlspecialchars(DB_NAME) . '</strong> is connected.</p>';
    echo '<p><a href="index.html">Go to website</a></p>';
} catch (PDOException $e) {
    http_response_code(500);
    echo '<h1>Setup failed</h1>';
    echo '<p>Start Apache and MySQL in XAMPP, then refresh this page.</p>';
    echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
}
