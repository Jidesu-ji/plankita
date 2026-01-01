<?php
session_start();
require_once "../config/database.php";

$anggota_id = $_GET['anggota_id'] ?? null;
if (!$anggota_id) die("Anggota tidak valid");

$stmt = $pdo->prepare("SELECT * FROM anggota WHERE id=?");
$stmt->execute([$anggota_id]);
$anggota = $stmt->fetch();