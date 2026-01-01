<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$kegiatan_id = $_GET["kegiatan_id"] ?? null;
if (!$kegiatan_id) {
    die("Kegiatan tidak valid.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Import Tahapan • PlanKita</title>
    <link rel="stylesheet" href="../assets/dashboard.css">
    <style>
        body { background: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .container { max-width: 700px; margin: 40px auto; padding: 0 20px; }
        .card { background: white; border-radius: 14px; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 28px; color: #1e293b; }
        .btn { display: inline-block; padding: 10px 16px; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 14px; transition: background 0.2s; }
        .btn-secondary { background: #e2e8f0; color: #1e293b; }
        .btn-secondary:hover { background: #cbd5e1; }
        .btn-primary { background: #2563eb; color: white; border: none; cursor: pointer; }
        .btn-primary:hover { background: #1d4ed8; }
        input[type="file"], button { font-size: 15px; }
        .info { background: #dbeafe; padding: 16px; border-radius: 10px; margin-top: 20px; font-size: 14px; color: #1e40af; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>📥 Import Tahapan (CSV)</h1>
        <a href="index.php?kegiatan_id=<?= (int)$kegiatan_id ?>" class="btn btn-secondary">
            ← Kembali
        </a>
    </div>

    <div class="card">
        <form method="POST" action="proses_import.php" enctype="multipart/form-data">
            <input type="hidden" name="kegiatan_id" value="<?= (int)$kegiatan_id ?>">

            <label for="csv_file" style="display:block; margin-bottom:8px; font-weight:600;">
                Pilih File CSV
            </label>
            <input type="file" name="csv_file" id="csv_file" accept=".csv" required 
                   style="width:100%; padding:12px; border:1px solid #cbd5e1; border-radius:8px;">

            <button type="submit" class="btn btn-primary" style="margin-top:20px; width:100%; padding:14px;">
                🚀 Import Sekarang
            </button>
        </form>

        <div class="info">
            <strong>Format CSV yang wajib:</strong><br>
            Baris pertama (header) diabaikan. Kolom harus dalam urutan:<br><br>
            <code>urutan,nama_tahapan,penanggung_jawab,jenis_proses</code><br><br>
            Contoh:<br>
            <code>1,Persiapan Proposal,Budi,manual</code><br>
            <code>2,Pengumpulan Data,Ani,digital</code>
        </div>
    </div>
</div>

</body>
</html>