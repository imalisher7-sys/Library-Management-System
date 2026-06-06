<?php
session_start();
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
}

$data = readJsonBody();
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if ($email === '' || $password === '') {
    jsonResponse(['success' => false, 'message' => 'Email and password are required.'], 400);
}

try {
    $stmt = getDB()->prepare(
        'SELECT Member_ID, Name, Email, Password, Membership_Status
         FROM member WHERE Email = ?'
    );
    $stmt->execute([$email]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member || !$member['Password'] || !password_verify($password, $member['Password'])) {
        jsonResponse(['success' => false, 'message' => 'Invalid email or password.'], 401);
    }

    if ($member['Membership_Status'] !== 'Active') {
        jsonResponse(['success' => false, 'message' => 'Your membership is inactive.'], 403);
    }

    $_SESSION['user_id'] = (int) $member['Member_ID'];
    $_SESSION['user_name'] = $member['Name'];
    $_SESSION['user_email'] = $member['Email'];

    jsonResponse([
        'success' => true,
        'message' => 'Login successful.',
        'user' => ['name' => $member['Name'], 'email' => $member['Email']],
    ]);
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
