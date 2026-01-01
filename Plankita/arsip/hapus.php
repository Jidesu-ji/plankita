<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit;
}

/**
 * Ambil data arsip → pastikan milik user
 */
$stmt = $pdo->prepare(
    "SELECT * FROM arsip WHERE id = ? AND user_id = ?"
);
$stmt->execute([$id, $user_id]);
$arsip = $stmt->fetch();

if (!$arsip) {
    die("Akses ditolak.");
}

/**
 * Hapus file fisik
 */
if (file_exists($arsip['file_path'])) {
    unlink($arsip['file_path']);
}

/**
 * Hapus data database
 */
$delete = $pdo->prepare("DELETE FROM arsip WHERE id = ?");
$delete->execute([$id]);

header("Location: index.php");
exit;
