<?php
session_start();
require_once "../config/database.php";
require_once "../services/TahapanService.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$kegiatan_id = $_POST["kegiatan_id"] ?? null;
if (!$kegiatan_id || !isset($_FILES["csv_file"])) {
    die("Data tidak lengkap.");
}

$file = $_FILES["csv_file"]["tmp_name"];

if (($handle = fopen($file, "r")) === false) {
    die("Gagal membaca file.");
}

// Ambil header
$header = fgetcsv($handle);
$expected = ['urutan','nama_tahapan','penanggung_jawab','jenis_proses'];

if ($header !== $expected) {
    die("Format CSV tidak sesuai.");
}

// Loop data
while (($row = fgetcsv($handle)) !== false) {
    $data = [
        "urutan" => $row[0] ?? null,
        "nama_tahapan" => $row[1] ?? null,
        "penanggung_jawab" => $row[2] ?? null,
        "jenis_proses" => $row[3] ?? null,
    ];

    // Satu pintu: SERVICE
    simpanTahapan($pdo, $kegiatan_id, $data);
}

fclose($handle);

// Balik ke index
header("Location: index.php?kegiatan_id=" . $kegiatan_id);
exit;
