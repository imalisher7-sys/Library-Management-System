<?php
session_start();
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['user_id'])) {
    jsonResponse(['logged_in' => false]);
}

jsonResponse([
    'logged_in' => true,
    'user' => [
        'name' => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
    ],
]);
