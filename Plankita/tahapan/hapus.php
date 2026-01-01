<?php
/**
 * File: tahapan/hapus.php
 *
 * Fungsi:
 * Menghapus satu tahapan kegiatan.
 * Sistem memastikan hanya pemilik kegiatan
 * yang dapat menghapus data.
 */

session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$tahapan_id = $_GET['id'] ?? null;
if (!$tahapan_id) {
    die("Tahapan tidak ditemukan.");
}

// Ambil tahapan + validasi kepemilikan kegiatan
$stmt = $pdo->prepare(
    "SELECT t.kegiatan_id, k.user_id
     FROM tahapan t
     JOIN kegiatan k ON t.kegiatan_id = k.id
     WHERE t.id = ?"
);
$stmt->execute([$tahapan_id]);
$data = $stmt->fetch();

if (!$data || $data['user_id'] != $_SESSION['user_id']) {
    die("Akses ditolak.");
}

// Hapus tahapan
$stmt = $pdo->prepare("DELETE FROM tahapan WHERE id = ?");
$stmt->execute([$tahapan_id]);

// Kembali ke daftar tahapan kegiatan
header("Location: index.php?kegiatan_id=" . $data['kegiatan_id']);
exit;
