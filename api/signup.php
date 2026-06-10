<?php

include 'db.php';

$error = '';

if (isset($_POST['signup'])) {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $error = 'All fields are required.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO member (Name, Email, Password, Membership_Date, Membership_Status, Role)
             VALUES (?, ?, ?, CURDATE(), 'Active', 'Member')"
        );
        mysqli_stmt_bind_param($stmt, 'sss', $name, $email, $password);

        if (mysqli_stmt_execute($stmt)) {
            header('Location: login.php');
            exit();
        }

        if (mysqli_errno($conn) === 1062) {
            $error = 'Email already registered.';
        } else {
            $error = 'Signup failed. Please try again.';
        }
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

<?php if ($error !== '') { ?>
<p style="color:#e74c3c;"><?php echo htmlspecialchars($error); ?></p>
<?php } ?>

<form method="POST">
<input type="text" name="name" placeholder="Full Name" required>
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required minlength="6">
<button type="submit" name="signup">Signup</button>
</form>

<a class="link" href="../index.html">← Back Home</a>
</div>

</body>
</html>
