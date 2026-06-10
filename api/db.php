<?php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "blockshelf_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
session_start();

ensureMemberRoleColumn($conn);

function ensureMemberRoleColumn(mysqli $conn): void
{
    $result = mysqli_query($conn, "SHOW COLUMNS FROM member LIKE 'Role'");
    if ($result && mysqli_num_rows($result) === 0) {
        mysqli_query($conn, "ALTER TABLE member ADD COLUMN Role enum('Admin','Member') DEFAULT 'Member' AFTER Membership_Status");
    }
}

function esc(mysqli $conn, string $value): string
{
    return mysqli_real_escape_string($conn, $value);
}
