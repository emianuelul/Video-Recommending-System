<?php
session_start();
require '../../db/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        die('Username and password are required.');
    }

    $stmt = $db->prepare("SELECT id, username, password FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
    } else {
        echo "Invalid username or password.";
    }
}
?>


<!DOCTYPE html>

<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Document</title>
        <link rel="stylesheet" href="../styles/cssReset.css">
        <script src="login-script.js" defer></script>
    </head>

    <body>
        <form class="loginContainer" method="post">
            <input type="text" id="username" name="username" placeholder="Username" />
            <input type="text" id="password" name="password" placeholder="Password" />
            <button type="submit" id="login">Login</button>
        </form>
    </body>
</html>
