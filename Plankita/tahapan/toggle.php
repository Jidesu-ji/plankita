<?php
/**
 * Mengubah status tahapan (belum ↔ selesai)
 */

session_start();
require_once "../config/database.php";

$id = $_POST['tahapan_id'];

$stmt = $pdo->prepare(
    "UPDATE tahapan
     SET status = IF(status='belum','selesai','belum')
     WHERE id = ?"
);
$stmt->execute([$id]);

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
