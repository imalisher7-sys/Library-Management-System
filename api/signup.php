<?php
session_start();
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$name = trim($data['full_name'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$phone = trim($data['phone'] ?? '');

if ($name === '' || $email === '' || $password === '') {
    jsonResponse(['success' => false, 'message' => 'Name, email, and password are required.'], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'Invalid email address.'], 400);
}

if (strlen($password) < 6) {
    jsonResponse(['success' => false, 'message' => 'Password must be at least 6 characters.'], 400);
}

try {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = getDB()->prepare(
        'INSERT INTO member (Name, Email, Phone_No, Password, Membership_Date, Membership_Status)
         VALUES (?, ?, ?, ?, CURDATE(), ?)'
    );
    $stmt->execute([$name, $email, $phone !== '' ? $phone : null, $hash, 'Active']);

    $_SESSION['user_id'] = (int) getDB()->lastInsertId();
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;

    jsonResponse(['success' => true, 'message' => 'Member account created successfully.']);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        jsonResponse(['success' => false, 'message' => 'Email already registered.'], 409);
    }
    jsonResponse(['success' => false, 'message' => 'Database error. Import blockshelf_db.sql first.'], 500);
}
