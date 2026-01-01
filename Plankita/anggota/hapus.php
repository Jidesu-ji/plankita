<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) die("ID tidak valid");

$user_id = $_SESSION["user_id"];

/**
 * Hapus node + seluruh turunannya
 */
function hapusNode($pdo, $id) {
    // cari anak
    $stmt = $pdo->prepare("SELECT id FROM anggota WHERE parent_id = ?");
    $stmt->execute([$id]);
    $anak = $stmt->fetchAll();

    foreach ($anak as $a) {
        hapusNode($pdo, $a['id']);
    }

    // hapus node ini
    $del = $pdo->prepare("DELETE FROM anggota WHERE id = ?");
    $del->execute([$id]);
}

// validasi kepemilikan
$cek = $pdo->prepare("SELECT id FROM anggota WHERE id = ? AND user_id = ?");
$cek->execute([$id, $user_id]);

if (!$cek->fetch()) {
    die("Akses ditolak");
}

hapusNode($pdo, $id);

header("Location: index.php");
exit;