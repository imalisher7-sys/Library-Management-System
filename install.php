<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/api/db.php';

echo '<h1>BlockShelf Library — Ready</h1>';
echo '<p>Database <strong>blockshelf_db</strong> is connected.</p>';
echo '<p><a href="index.html">Go to website</a> | <a href="api/login.php">Login</a></p>';
