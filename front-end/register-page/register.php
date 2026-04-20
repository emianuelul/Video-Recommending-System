<?php
session_start();
require '../../db/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        die('Username and password are required.');
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $stmt->execute([
                ':username' => $username,
                ':password' => $hashedPassword
        ]);

        echo "User registered successfully.";
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            echo "Username already exists.";
        } else {
            echo "Error: " . $e->getMessage();
        }
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
</head>

<body>
<form class="loginContainer" method="post">
    <input type="text" id="username" name="username" placeholder="Username" />
    <input type="text" id="password" name="password" placeholder="Password" />
    <button type="submit" id="register-submit">Register</button>
</form>
</body>
</html>
