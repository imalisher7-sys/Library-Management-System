<?php

include 'db.php';

$error = '';

if (isset($_POST['login'])) {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email and password are required.';
    } else {
        $stmt = mysqli_prepare(
            $conn,
            'SELECT Member_ID, Name, Email, Password, Role, Membership_Status
             FROM member WHERE Email = ? AND Password = ? LIMIT 1'
        );
        mysqli_stmt_bind_param($stmt, 'ss', $email, $password);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user   = mysqli_fetch_assoc($result);

        if ($user && $user['Membership_Status'] === 'Active') {
            $_SESSION['member_id'] = $user['Member_ID'];
            $_SESSION['name']      = $user['Name'];
            $_SESSION['role']      = $user['Role'] ?? 'Member';

            header('Location: books.php');
            exit();
        }

        $error = 'Invalid email or password.';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="center-wrapper">

<div class="card">
<h2>Login</h2>

<?php if ($error !== '') { ?>
<p style="color:#e74c3c;"><?php echo htmlspecialchars($error); ?></p>
<?php } ?>

<form method="POST">
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>
<button type="submit" name="login">Login</button>
</form>

<a class="link" href="../index.html">← Back Home</a>
</div>

</body>
</html>
