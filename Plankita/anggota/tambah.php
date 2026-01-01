<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$atasan = $pdo->prepare(
    "SELECT id, nama, level FROM anggota WHERE user_id = ?"
);
$atasan->execute([$user_id]);
$atasan = $atasan->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $bukti = null;
    if (!empty($_FILES['bukti']['name'])) {
        $bukti = "../uploads/" . time() . "_" . $_FILES['bukti']['name'];
        move_uploaded_file($_FILES['bukti']['tmp_name'], $bukti);
    }

    $stmt = $pdo->prepare("
        INSERT INTO anggota
        (user_id, nama, level, parent_id, deskripsi_tugas, bukti_file)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $user_id,
        $_POST['nama'],
        $_POST['level'],
        $_POST['parent_id'] ?: null,
        $_POST['tugas'],
        $bukti
    ]);

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah Anggota • PlanKita</title>
<link rel="stylesheet" href="../assets/dashboard.css">
<style>
    .container {
    max-width: 600px;
    margin: 40px auto;
    padding: 0 20px;
}
.form-card {
    background: white;
    padding: 32px;
    border-radius: 16px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
}
label {
    display: block;
    margin: 20px 0 8px;
    font-weight: 600;
    color: #374151;
}
input, select, textarea {
    width: 100%;
    padding: 14px;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    font-size: 15px;
}
button {
    width: 100%;
    padding: 16px;
    background: #2563eb;
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    margin-top: 20px;
    cursor: pointer;
}
button:hover { background: #1d4ed8; }
.back-link {
    display: block;
    text-align: center;
    margin-top: 20px;
    color: #6366f1;
    font-weight: 500;
}
.form-card {
    max-width:480px;
    background:#fff;
    padding:28px;
    border-radius:16px;
    box-shadow:0 10px 30px rgba(0,0,0,.08);
}
.form-card input,
.form-card select,
.form-card textarea {
    width:100%;
    padding:12px;
    border-radius:10px;
    border:1px solid #ddd;
    margin-bottom:14px;
}
.form-card button {
    width:100%;
    padding:14px;
    border:none;
    border-radius:12px;
    background:#6366f1;
    color:#fff;
    font-weight:600;
}
</style>
</head>
<body>

<div class="wrapper">
<aside class="sidebar">
    <h2>📌 PlanKita</h2>
    <a href="../dashboard/index.php">🏠 Dashboard</a>
    <a href="../kegiatan/index.php">📋 Kegiatan</a>
    <a href="../agenda/index.php">🗓 Agenda</a>
    <a href="../arsip/index.php">📁 Dokumen & Arsip</a>
    <a href="index.php" class="active">👥 Anggota</a>
    <a href="../keuangan/index.php">💰 Keuangan</a>
    <a href="../auth/logout.php" class="logout">🚪 Logout</a>
</aside>

<main class="main">
<div class="header">
    <h1>➕ Tambah Anggota</h1>
    <a href="index.php" class="btn-secondary">← Kembali</a>
</div>

<div class="form-card">
<form method="POST" enctype="multipart/form-data">

    <input name="nama" placeholder="Nama anggota" required>

    <select name="level" required>
        <option value="">Pilih Level</option>
        <option value="ketua">Ketua</option>
        <option value="wakil">Wakil</option>
        <option value="kepala">Kepala</option>
        <option value="anggota">Anggota</option>
    </select>

    <select name="parent_id">
        <option value="">Tanpa Atasan</option>
        <?php foreach ($atasan as $a): ?>
            <option value="<?= $a['id'] ?>">
                <?= strtoupper($a['level']) ?> — <?= htmlspecialchars($a['nama']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <textarea name="tugas" placeholder="Deskripsi tugas"></textarea>

    <label>📎 Upload Bukti Tugas</label>
    <input type="file" name="bukti">

    <button>Simpan Anggota</button>
</form>
</div>

</main>
</div>
</body>
</html>