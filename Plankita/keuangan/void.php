<?php
/**
 * File: keuangan/void.php
 * Fungsi: VOID transaksi keuangan (audit-safe)
 */

session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    die("Akses ditolak.");
}

$user_id = $_SESSION["user_id"];
$id      = $_POST['id'] ?? null;
$alasan  = trim($_POST['alasan'] ?? '');

if (!$id || $alasan === '') {
    die("Data tidak lengkap.");
}

/**
 * ================================
 * VALIDASI KEPEMILIKAN TRANSAKSI
 * ================================
 */
$stmt = $pdo->prepare(
    "SELECT id FROM keuangan
     WHERE id = ? AND user_id = ? AND is_void = 0"
);
$stmt->execute([$id, $user_id]);
$cek = $stmt->fetch();

if (!$cek) {
    die("Transaksi tidak ditemukan atau sudah di-VOID.");
}

/**
 * ================================
 * PROSES VOID
 * ================================
 */
$stmt = $pdo->prepare(
    "UPDATE keuangan
     SET is_void = 1,
         alasan_void = ?
     WHERE id = ? AND user_id = ?"
);

$stmt->execute([
    $alasan,
    $id,
    $user_id
]);

header("Location: index.php");
exit;