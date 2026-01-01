<?php
/**
 * File: keuangan/tambah.php
 * Modul: Tambah Transaksi Keuangan
 */

session_start();
require_once "../config/database.php";

/* ================= PROTEKSI LOGIN ================= */
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

/* ================= PROSES SUBMIT ================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $tanggal     = $_POST["tanggal"] ?? null;
    $jenis       = $_POST["jenis"] ?? null;
    $jumlah      = (int)($_POST["jumlah"] ?? 0);
    $sumber      = trim($_POST["sumber_tujuan"] ?? "");
    $keterangan  = trim($_POST["keterangan"] ?? "");

    if (
        !$tanggal ||
        !in_array($jenis, ['masuk','keluar']) ||
        $jumlah <= 0 ||
        !$sumber
    ) {
        die("Data transaksi tidak lengkap atau tidak valid.");
    }

    $stmt = $pdo->prepare("
        INSERT INTO keuangan
        (user_id, tanggal, jenis, jumlah, sumber_tujuan, keterangan)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $user_id,
        $tanggal,
        $jenis,
        $jumlah,
        $sumber,
        $keterangan
    ]);

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Transaksi • PlanKita</title>
    <link rel="stylesheet" href="../assets/dashboard.css">
    <style>
        body { background: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .container { max-width: 600px; margin: 40px auto; padding: 0 20px; }
        .card { background: white; padding: 32px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .card h2 { margin-top: 0; color: #1e293b; }
        label { display: block; margin: 20px 0 8px; font-weight: 600; color: #374151; }
        input, select, textarea {
            width: 100%; padding: 14px; border: 1px solid #cbd5e1; border-radius: 12px; font-size: 15px; box-sizing: border-box;
        }
        textarea { resize: vertical; }
        button {
            width: 100%; padding: 16px; background: #2563eb; color: white; border: none; border-radius: 12px;
            font-size: 16px; font-weight: 600; margin-top: 24px; cursor: pointer; transition: background 0.2s;
        }
        button:hover { background: #1d4ed8; }
        .back { display: block; margin-top: 20px; text-align: center; color: #6366f1; font-weight: 500; }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <h2>➕ Tambah Transaksi Baru</h2>

        <form method="POST">
            <label>Tanggal Transaksi</label>
            <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>

            <label>Jenis Transaksi</label>
            <select name="jenis" required>
                <option value="">-- Pilih Jenis --</option>
                <option value="masuk">Pemasukan</option>
                <option value="keluar">Pengeluaran</option>
            </select>

            <label>Jumlah (Rp)</label>
            <input type="number" name="jumlah" min="1" placeholder="500000" required>

            <label>Sumber / Tujuan</label>
            <input type="text" name="sumber_tujuan" placeholder="Contoh: Sponsor, Bayar Vendor, dll" required>

            <label>Keterangan (opsional)</label>
            <textarea name="keterangan" rows="4" placeholder="Detail transaksi jika diperlukan..."></textarea>

            <button type="submit">💾 Simpan Transaksi</button>
        </form>

        <a href="index.php" class="back">← Kembali ke Keuangan</a>
    </div>
</div>

</body>
</html>