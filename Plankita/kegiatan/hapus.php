<?php
/**
 * File: hapus.php
 * Fungsi: Menghapus kegiatan beserta tahapan yang terkait
 * Menjaga integritas relasi database
 */

session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$kegiatan_id = $_GET["id"];
$user_id = $_SESSION["user_id"];

/**
 * Hapus tahapan terlebih dahulu
 * karena tahapan bergantung pada kegiatan
 */
$stmt = $pdo->prepare("DELETE FROM tahapan WHERE kegiatan_id = ?");
$stmt->execute([$kegiatan_id]);

/**
 * Setelah tahapan terhapus,
 * baru kegiatan bisa dihapus
 */
$stmt = $pdo->prepare(
    "DELETE FROM kegiatan WHERE id = ? AND user_id = ?"
);
$stmt->execute([$kegiatan_id, $user_id]);

header("Location: index.php");
exit;
