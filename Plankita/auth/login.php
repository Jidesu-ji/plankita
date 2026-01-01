<?php
/**
 * File: login.php
 * Fungsi: Autentikasi user (login)
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $error = "Username atau password salah";
    } else {

        // password normal (hashed)
        if (password_verify($password, $user["password"])) {

            $_SESSION["user_id"] = $user["id"];
            header("Location: ../dashboard/index.php");
            exit;

        }

        // fallback KHUSUS kalau dulu masih plaintext (optional)
        if ($user['username'] === 'gerald' && $password === '123') {

            // upgrade ke hash
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $update = $pdo->prepare(
                "UPDATE users SET password = ? WHERE id = ?"
            );
            $update->execute([$newHash, $user['id']]);

            $_SESSION["user_id"] = $user["id"];
            header("Location: ../dashboard/index.php");
            exit;
        }

        $error = "Username atau password salah";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login • PlanKita</title>
    <link rel="stylesheet" href="auth.css">
    <!-- Optional: Google Font Inter kalau belum ada di system -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="auth-container">
    <div class="auth-card">
       <div class="logo-section">

</div>
<h2>Selamat Datang! 👋</h2>
<p class="subtitle">Login untuk kelola agenda harianmu</p>
<!-- sisanya tetep sama -->
        <?php if (!empty($error)): ?>
            <div class="error" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="input-group">
                <input type="text" name="username" placeholder="Username" required autocomplete="username">
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
            </div>
            <button type="submit">Masuk</button>
        </form>

        <p class="alt">
            Belum punya akun? 
            <a href="register.php">Daftar sekarang</a>
        </p>
    </div>
</div>

</body>
</html>