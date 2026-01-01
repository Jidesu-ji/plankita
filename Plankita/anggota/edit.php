<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$id = $_GET['id'] ?? null;
if (!$id) die("ID tidak valid");

$stmt = $pdo->prepare("SELECT * FROM anggota WHERE id=? AND user_id=?");
$stmt->execute([$id,$user_id]);
$a = $stmt->fetch();
if (!$a) die("Data tidak ditemukan");

$atasan = $pdo->prepare(
    "SELECT id,nama,level FROM anggota WHERE user_id=? AND id!=?"
);
$atasan->execute([$user_id,$id]);
$atasan = $atasan->fetchAll();

if ($_SERVER["REQUEST_METHOD"]==="POST") {
    $bukti = $a['bukti_file'];

    if (!empty($_FILES['bukti']['name'])) {
        $bukti = "../uploads/".time()."_".$_FILES['bukti']['name'];
        move_uploaded_file($_FILES['bukti']['tmp_name'],$bukti);
    }

    $stmt = $pdo->prepare(
        "UPDATE anggota SET
        nama=?, level=?, parent_id=?, deskripsi_tugas=?, bukti_file=?
        WHERE id=? AND user_id=?"
    );
    $stmt->execute([
        $_POST['nama'],
        $_POST['level'],
        $_POST['parent_id'] ?: null,
        $_POST['tugas'],
        $bukti,
        $id,
        $user_id
    ]);

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Anggota • PlanKita</title>
<link rel="stylesheet" href="../assets/dashboard.css">

<style>
.edit-card{
    max-width:520px;
    background:#fff;
    margin:auto;
    padding:28px;
    border-radius:18px;
    box-shadow:0 15px 40px rgba(0,0,0,.08);
}
.edit-card h2{
    margin-bottom:6px;
}
.edit-card p{
    color:#666;
    margin-bottom:22px;
}
.edit-card input,
.edit-card select,
.edit-card textarea{
    width:100%;
    padding:12px;
    border-radius:10px;
    border:1px solid #ddd;
    margin-bottom:14px;
}
.edit-card textarea{
    min-height:90px;
}
.form-actions{
    display:flex;
    gap:12px;
    margin-top:10px;
}
.btn-primary{
    flex:1;
    background:#6366f1;
    color:#fff;
    padding:14px;
    border-radius:12px;
    border:none;
    font-weight:600;
}
.btn-secondary{
    flex:1;
    background:#f3f4f6;
    color:#111;
    padding:14px;
    border-radius:12px;
    text-align:center;
}
.bukti-preview{
    background:#f9fafb;
    padding:12px;
    border-radius:10px;
    font-size:14px;
    margin-bottom:12px;
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
    <h1>✏️ Edit Anggota</h1>
</div>

<div class="edit-card">
    <h2><?= htmlspecialchars($a['nama']) ?></h2>
    <p>Perbarui data, struktur, atau bukti tugas anggota.</p>

<form method="POST" enctype="multipart/form-data">

    <input name="nama" value="<?= htmlspecialchars($a['nama']) ?>" required>

    <select name="level" required>
        <?php foreach (['ketua','wakil','kepala','anggota'] as $lvl): ?>
        <option value="<?= $lvl ?>" <?= $a['level']===$lvl?'selected':'' ?>>
            <?= ucfirst($lvl) ?>
        </option>
        <?php endforeach; ?>
    </select>

    <select name="parent_id">
        <option value="">Tanpa Atasan</option>
        <?php foreach ($atasan as $x): ?>
        <option value="<?= $x['id'] ?>"
            <?= $a['parent_id']==$x['id']?'selected':'' ?>>
            <?= strtoupper($x['level']) ?> — <?= htmlspecialchars($x['nama']) ?>
        </option>
        <?php endforeach; ?>
    </select>

    <textarea name="tugas"><?= htmlspecialchars($a['deskripsi_tugas']) ?></textarea>

    <?php if ($a['bukti_file']): ?>
    <div class="bukti-preview">
        📎 Bukti saat ini:
        <a href="<?= $a['bukti_file'] ?>" target="_blank">Lihat File</a>
    </div>
    <?php endif; ?>

    <label>Upload bukti baru (opsional)</label>
    <input type="file" name="bukti">

    <div class="form-actions">
        <a href="index.php" class="btn-secondary">← Batal</a>
        <button class="btn-primary">💾 Simpan</button>
    </div>

</form>
</div>

</main>
</div>
</body>
</html>