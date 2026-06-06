<?php
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'Valid email is required.'], 400);
}

try {
    $stmt = getDB()->prepare('SELECT Member_ID FROM member WHERE Email = ?');
    $stmt->execute([$email]);

    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        jsonResponse(['success' => false, 'message' => 'No member found with that email.'], 404);
    }

    jsonResponse(['success' => true, 'message' => 'Reset link sent to your email (demo mode).']);
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Database error. Import blockshelf_db.sql first.'], 500);
}
