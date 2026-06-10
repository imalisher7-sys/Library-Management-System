<?php

include 'db.php';

if(isset($_POST['login']))
{
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM Member
            WHERE Email='$email'
            AND Password='$password'";

    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) == 1)
    {
        $user = mysqli_fetch_assoc($result);

        $_SESSION['member_id'] = $user['Member_ID'];
        $_SESSION['name'] = $user['Name'];
        $_SESSION['role'] = $user['Role'];

        header("Location: books.php");
        exit();
    }
    else
    {
        $error = "Invalid Email or Password";
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

<?php
if(isset($error))
{
    echo "<p>$error</p>";
}
?>

<form method="POST">

<input type="email" name="email" placeholder="Email" required>

<input type="password" name="password" placeholder="Password" required>

<button type="submit" name="login">Login</button>

</form>

<a class="link" href="index.php">← Back Home</a>

</div>

</body>
</html>

