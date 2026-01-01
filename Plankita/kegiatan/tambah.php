<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = trim($_POST["nama"]);
    $jenis = trim($_POST["jenis"]);
    $desk  = trim($_POST["deskripsi"]);
    $user_id  = $_SESSION["user_id"];

    if ($nama === '') {
        die("Nama kegiatan wajib diisi.");
    }

    $stmt = $pdo->prepare(
        "INSERT INTO kegiatan (nama_kegiatan, jenis_kegiatan, deskripsi, user_id)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$nama, $jenis, $desk, $user_id]);

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Buat Kegiatan Baru • PlanKita</title>
<link rel="stylesheet" href="../assets/dashboard.css">
<style>
body{
    background:linear-gradient(135deg,#eef2ff,#f8fafc);
}
.center-wrap{
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
}
.form-card{
    width:100%;
    max-width:520px;
    background:#fff;
    padding:32px;
    border-radius:20px;
    box-shadow:0 20px 40px rgba(0,0,0,.08);
}
.form-card h1{
    margin:0 0 6px;
    font-size:26px;
}
.form-card p{
    margin:0 0 20px;
    color:#6b7280;
}
.form-card input,
.form-card textarea{
    width:100%;
    padding:14px;
    border-radius:12px;
    border:1px solid #e5e7eb;
    margin-bottom:14px;
    font-size:14px;
}
.form-card textarea{
    resize:vertical;
    min-height:100px;
}
.form-actions{
    display:flex;
    gap:12px;
    margin-top:10px;
}
.form-actions button{
    flex:1;
    padding:14px;
    border:none;
    border-radius:14px;
    font-weight:600;
    cursor:pointer;
}
.btn-primary{
    background:#6366f1;
    color:#fff;
}
.btn-secondary{
    background:#f3f4f6;
    color:#111;
    text-decoration:none;
    text-align:center;
    line-height:48px;
}
</style>
</head>

<body>

<div class="center-wrap">
    <div class="form-card">
        <h1>➕ Buat Kegiatan Baru</h1>
        <p>Definisikan kegiatan utama sebelum masuk ke tahap analisis.</p>

        <form method="POST">
            <input
                type="text"
                name="nama"
                placeholder="Nama kegiatan (contoh: Lomba Inovasi Digital)"
                required
            >

            <input
                type="text"
                name="jenis"
                placeholder="Jenis kegiatan (opsional)"
            >

            <textarea
                name="deskripsi"
                placeholder="Deskripsi singkat tujuan & ruang lingkup kegiatan"
            ></textarea>

            <div class="form-actions">
                <a href="index.php" class="btn btn-secondary">← Batal</a>
                <button type="submit" class="btn-primary">Simpan Kegiatan</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>