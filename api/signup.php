<?php

include 'db.php';

if(isset($_POST['signup']))
{
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "INSERT INTO Member
    (Name, Email, Membership_Date, Membership_Status, Password, Role)
    VALUES
    (
        '$name',
        '$email',
        CURDATE(),
        'Active',
        '$password',
        'Member'
    )";

    if(mysqli_query($conn, $sql))
    {
        header("Location: login.php");
        exit();
    }
    else
    {
        echo "Signup Failed";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Signup</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="center-wrapper">

<div class="card">

<h2>Create Account</h2>

<form method="POST">

<input type="text" name="name" placeholder="Full Name" required>

<input type="email" name="email" placeholder="Email" required>

<input type="password" name="password" placeholder="Password" required>

<button type="submit" name="signup">Signup</button>

</form>

<a class="link" href="index.php">← Back Home</a>

</div>

</body>
</html>
