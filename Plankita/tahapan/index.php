<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$kegiatan_id = $_GET["kegiatan_id"] ?? null;

if (!$kegiatan_id) {
    die("Kegiatan tidak ditemukan.");
}

// Validasi kepemilikan
$stmt = $pdo->prepare("SELECT * FROM kegiatan WHERE id = ? AND user_id = ?");
$stmt->execute([$kegiatan_id, $user_id]);
$kegiatan = $stmt->fetch();

if (!$kegiatan) {
    die("Akses ditolak.");
}

// Ambil tahapan
$stmt = $pdo->prepare("SELECT * FROM tahapan WHERE kegiatan_id = ? ORDER BY urutan ASC");
$stmt->execute([$kegiatan_id]);
$tahapan = $stmt->fetchAll();

// Hitung progress
$totalTahapan = count($tahapan);
$selesai = 0;
foreach ($tahapan as $t) {
    if ($t['status'] === 'selesai') $selesai++;
}
$progress = $totalTahapan > 0 ? round(($selesai / $totalTahapan) * 100) : 0;

// Analisis
$hasilAnalisis = [];
if (!empty($tahapan)) {
    require_once "analisis.php";
    $hasilAnalisis = analisisTahapan($tahapan);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tahapan • <?= htmlspecialchars($kegiatan['nama_kegiatan']) ?> • PlanKita</title>
    <link rel="stylesheet" href="../assets/dashboard.css">
    <style>
        body {
            background: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            color: #1e293b;
        }
        .btn {
            display: inline-block;
            padding: 10px 16px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.2s;
        }
        .btn-primary {
            background: #2563eb;
            color: white;
        }
        .btn-primary:hover {
            background: #1d4ed8;
        }
        .btn-secondary {
            background: #e2e8f0;
            color: #1e293b;
        }
        .btn-secondary:hover {
            background: #cbd5e1;
        }
        .btn-danger {
            background: #dc2626;
            color: white;
        }
        .btn-danger:hover {
            background: #b91c1c;
        }
        .progress-container {
            background: #e2e8f0;
            height: 12px;
            border-radius: 999px;
            overflow: hidden;
            margin: 20px 0;
        }
        .progress-bar {
            height: 100%;
            background: <?= $progress == 100 ? '#16a34a' : '#2563eb' ?>;
            width: <?= $progress ?>%;
            transition: width 0.4s ease;
        }
        .card {
            background: white;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .card h3 {
            margin: 0 0 8px;
            font-size: 18px;
            color: #1e293b;
        }
        .meta {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 12px;
        }
        .status-selesai {
            color: #16a34a;
            font-weight: 600;
        }
        .status-belum {
            color: #f59e0b;
            font-weight: 600;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 16px;
        }
        .actions form {
            display: inline;
        }
        .actions button {
            padding: 8px 14px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }
        .toggle-btn {
            background: #f1f5f9;
            color: #1e293b;
        }
        .toggle-btn:hover {
            background: #e2e8f0;
        }
        .analisis {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 16px;
            border-radius: 8px;
            margin-top: 30px;
        }
        .analisis h3 {
            margin: 0 0 12px;
            color: #92400e;
        }
        .analisis ul {
            margin: 0;
            padding-left: 20px;
        }
        .analisis li {
            margin-bottom: 8px;
            color: #78350f;
        }
        .empty {
            text-align: center;
            color: #64748b;
            font-style: italic;
            padding: 40px 0;
        }
    </style>
</head>
<body>

<div class="container">

    <div class="header">
        <h1>📋 Tahapan: <?= htmlspecialchars($kegiatan['nama_kegiatan']) ?></h1>
        <a href="../kegiatan/index.php" class="btn btn-secondary">← Kembali ke Kegiatan</a>
    </div>

    <!-- Progress -->
    <div class="card">
        <strong>Progres Kegiatan</strong>
        <div class="progress-container">
            <div class="progress-bar"></div>
        </div>
        <p style="margin:8px 0 0"><strong><?= $progress ?>%</strong> selesai 
            (<?= $selesai ?> dari <?= $totalTahapan ?> tahapan)</p>
    </div>

    <!-- Aksi Utama -->
    <div style="margin-bottom: 24px; display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="import.php?kegiatan_id=<?= $kegiatan_id ?>" class="btn btn-secondary">📥 Import CSV</a>
        <a href="tambah.php?kegiatan_id=<?= $kegiatan_id ?>" class="btn btn-primary">+ Tambah Tahapan</a>
    </div>

    <!-- Daftar Tahapan -->
    <?php if (empty($tahapan)): ?>
        <div class="empty">Belum ada tahapan.</div>
    <?php else: ?>
        <?php foreach ($tahapan as $t): ?>
            <div class="card">
                <h3><?= (int)$t['urutan'] ?>. <?= htmlspecialchars($t['nama_tahapan']) ?></h3>
                <div class="meta">
                    PJ: <?= htmlspecialchars($t['penanggung_jawab']) ?> • 
                    Proses: <?= htmlspecialchars($t['jenis_proses']) ?> • 
                    Status: <span class="<?= $t['status'] === 'selesai' ? 'status-selesai' : 'status-belum' ?>">
                        <?= $t['status'] === 'selesai' ? '✔ Selesai' : '⏳ Belum Selesai' ?>
                    </span>
                </div>

                <div class="actions">
                    <a href="edit.php?id=<?= (int)$t['id'] ?>" class="btn btn-secondary">✏️ Edit</a>
                    <a href="hapus.php?id=<?= (int)$t['id'] ?>" 
                       onclick="return confirm('Hapus tahapan ini?')" 
                       class="btn btn-danger">🗑 Hapus</a>

                    <form method="POST" action="toggle.php">
                        <input type="hidden" name="tahapan_id" value="<?= (int)$t['id'] ?>">
                        <button type="submit" class="toggle-btn">
                            <?= $t['status'] === 'selesai' ? '✔ Batalkan Selesai' : '✓ Tandai Selesai' ?>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Analisis -->
    <?php if (!empty($hasilAnalisis)): ?>
        <div class="analisis">
            <h3>⚠️ Hasil Analisis Sistem</h3>
            <ul>
                <?php foreach ($hasilAnalisis as $a): ?>
                    <li><strong>[<?= htmlspecialchars($a['level']) ?>]</strong> <?= htmlspecialchars($a['pesan']) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

</div>

</body>
</html>