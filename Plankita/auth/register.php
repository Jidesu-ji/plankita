<?php
/**
 * File: register.php
 */

session_start();
require_once "../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username === "" || $password === "") {
        $error = "Username dan password wajib diisi.";
    } else {

        // cek username sudah ada atau belum
        $cek = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $cek->execute([$username]);

        if ($cek->fetch()) {
            $error = "Username sudah digunakan.";
        } else {

            // hash password
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // simpan user
            $stmt = $pdo->prepare(
                "INSERT INTO users (username, password) VALUES (?, ?)"
            );
            $stmt->execute([$username, $hash]);

            // auto login setelah register
            $_SESSION["user_id"] = $pdo->lastInsertId();

            header("Location: ../dashboard/index.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register • PlanKita</title>
    <link rel="stylesheet" href="auth.css">
</head>
<body>

<div class="auth-card">
    <h2>Register Akun 🚀</h2>
    <p class="subtitle">untuk memulai segala agenda</p>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Daftar</button>
        </form>

    <div class="alt">
        Sudah punya akun?
        <a href="login.php">Login</a>
    </div>
</div>

</body>
</html>