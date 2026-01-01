<?php
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

// Ambil data + validasi
$stmt = $pdo->prepare("
    SELECT t.*, k.user_id, k.id AS kegiatan_id 
    FROM tahapan t
    JOIN kegiatan k ON t.kegiatan_id = k.id
    WHERE t.id = ?
");
$stmt->execute([$tahapan_id]);
$tahapan = $stmt->fetch();

if (!$tahapan || $tahapan['user_id'] != $_SESSION['user_id']) {
    die("Akses ditolak.");
}

// Proses update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $pdo->prepare("
        UPDATE tahapan 
        SET nama_tahapan = ?, penanggung_jawab = ?, jenis_proses = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $_POST["nama_tahapan"],
        $_POST["penanggung_jawab"],
        $_POST["jenis_proses"],
        $tahapan_id
    ]);

    header("Location: index.php?kegiatan_id=" . $tahapan['kegiatan_id']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Tahapan • PlanKita</title>
    <link rel="stylesheet" href="../assets/dashboard.css">
    <style>
        body { background: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .container { max-width: 700px; margin: 40px auto; padding: 0 20px; }
        .card { background: white; border-radius: 14px; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 28px; color: #1e293b; }
        .btn { display: inline-block; padding: 10px 16px; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 14px; }
        .btn-secondary { background: #e2e8f0; color: #1e293b; }
        .btn-secondary:hover { background: #cbd5e1; }
        .btn-primary { background: #2563eb; color: white; border: none; cursor: pointer; padding: 12px 20px; }
        .btn-primary:hover { background: #1d4ed8; }
        label { display: block; margin: 16px 0 6px; font-weight: 600; color: #374151; }
        input, select { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>✏️ Edit Tahapan</h1>
        <a href="index.php?kegiatan_id=<?= (int)$tahapan['kegiatan_id'] ?>" class="btn btn-secondary">
            ← Kembali
        </a>
    </div>

    <div class="card">
        <form method="POST">
            <label>Nama Tahapan</label>
            <input type="text" name="nama_tahapan" 
                   value="<?= htmlspecialchars($tahapan['nama_tahapan']) ?>" required>

            <label>Penanggung Jawab</label>
            <input type="text" name="penanggung_jawab" 
                   value="<?= htmlspecialchars($tahapan['penanggung_jawab']) ?>">

            <label>Jenis Proses</label>
            <select name="jenis_proses" required>
                <option value="manual" <?= $tahapan['jenis_proses'] == 'manual' ? 'selected' : '' ?>>Manual</option>
                <option value="digital" <?= $tahapan['jenis_proses'] == 'digital' ? 'selected' : '' ?>>Digital</option>
            </select>

            <button type="submit" class="btn btn-primary" style="width:100%; margin-top:24px;">
                💾 Simpan Perubahan
            </button>
        </form>
    </div>
</div>

</body>
</html>