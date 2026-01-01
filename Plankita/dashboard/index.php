<?php
session_start();
require_once "../config/database.php";
require_once "summary.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$summary = dashboardSummary($pdo, $user_id);

// Ambil 5 kegiatan terbaru / yang butuh perhatian
$stmt = $pdo->prepare("
    SELECT 
        k.id, 
        k.nama_kegiatan,
        COUNT(t.id) AS total_tahapan,
        COUNT(CASE WHEN t.status = 'selesai' THEN 1 END) AS selesai
    FROM kegiatan k
    LEFT JOIN tahapan t ON t.kegiatan_id = k.id
    WHERE k.user_id = ?
    GROUP BY k.id
    ORDER BY (total_tahapan > selesai) DESC, k.created_at DESC
    LIMIT 5
");
// Ganti query ini di dashboard/index.php
$stmt = $pdo->prepare("
    SELECT 
        k.id, 
        k.nama_kegiatan,
        COUNT(t.id) AS total_tahapan,
        COUNT(CASE WHEN t.status = 'selesai' THEN 1 END) AS selesai
    FROM kegiatan k
    LEFT JOIN tahapan t ON t.kegiatan_id = k.id
    WHERE k.user_id = ?
    GROUP BY k.id
    ORDER BY 
        (COUNT(t.id) > COUNT(CASE WHEN t.status = 'selesai' THEN 1 END)) DESC,
        k.created_at DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$kegiatanPreview = $stmt->fetchAll();

// Tips motivasi
$tips = [
    "Transparansi adalah fondasi kepercayaan organisasi.",
    "Dokumentasi yang baik hari ini, memudahkan evaluasi besok.",
    "Setiap tahapan kecil yang selesai adalah kemenangan.",
    "PlanKita: karena kegiatan layak dikelola dengan rapi."
];
$tipHariIni = $tips[array_rand($tips)];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard • PlanKita</title>
    <link rel="stylesheet" href="../assets/dashboard.css">
    <style>
        .progress-overview {
            background: white;
            padding: 20px;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .progress-container {
            background: #e2e8f0;
            height: 16px;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 10px;
        }
        .progress-bar {
            height: 100%;
            background: #2563eb;
            width: <?= $summary['progress'] ?>%;
            transition: width 0.6s ease;
        }
        .preview-card {
            background: white;
            padding: 20px;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .preview-list {
            list-style: none;
            padding: 0;
            margin: 16px 0 0;
        }
        .preview-item {
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .preview-item:last-child {
            border-bottom: none;
        }
        .preview-item a {
            text-decoration: none;
            color: #2563eb;
            font-weight: 600;
            font-size: 15px;
        }
        .preview-item small {
            color: #64748b;
            margin-left: 8px;
        }
        .tip-box {
            background: #f0f9ff;
            padding: 16px;
            border-radius: 10px;
            font-style: italic;
            color: #0369a1;
            margin-top: 20px;
            border-left: 4px solid #0ea5e9;
        }
        .shortcuts {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin: 30px 0;
        }
        .btn-primary {
            padding: 12px 20px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .btn-primary:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>

<div class="wrapper">

    <!-- ===== Sidebar (tetap sama) ===== -->
    <div class="sidebar">
        <h2>📌 PlanKita</h2>
        <a href="../dashboard/index.php">🏠 Dashboard</a>
        <a href="../kegiatan/index.php">📋 Kegiatan</a>
        <a href="../arsip/index.php">📁 Dokumen & Arsip</a>
        <a href="../anggota/index.php">👥 Anggota</a>
        <a href="../keuangan/index.php">💰 Keuangan</a>
        <a href="../auth/logout.php" class="logout">🚪 Logout</a>
    </div>

    <!-- ===== Main ===== -->
    <div class="main">

        <div class="header">
            <h1>Dashboard</h1>
            <div class="user">Login ID: <?= (int)$user_id ?></div>
        </div>

        <!-- ===== Statistik Utama ===== -->
        <div class="cards">
            <div class="card">
                <h3>Total Kegiatan</h3>
                <p><?= $summary['total_kegiatan'] ?></p>
            </div>
            <div class="card">
                <h3>Total Tahapan</h3>
                <p><?= $summary['total_tahapan'] ?></p>
            </div>
            <div class="card">
                <h3>Progress Keseluruhan</h3>
                <p><?= $summary['progress'] ?>%</p>
            </div>
            <div class="card">
                <h3>Kegiatan Berisiko</h3>
                <p><?= $summary['berisiko'] ?></p>
            </div>
        </div>

        <!-- ===== Progress Bar Besar ===== -->
        <div class="progress-overview">
            <strong>Progres Seluruh Organisasi</strong>
            <div class="progress-container">
                <div class="progress-bar"></div>
            </div>
            <p style="margin: 10px 0 0; color:#64748b;">
                <?= (int)($summary['total_tahapan'] * $summary['progress'] / 100) ?> dari <?= $summary['total_tahapan'] ?> tahapan telah selesai
            </p>
        </div>

        <!-- ===== Preview Kegiatan ===== -->
        <div class="preview-card">
            <h3>🔥 Kegiatan Terbaru / Butuh Perhatian</h3>
            <?php if (empty($kegiatanPreview)): ?>
                <p style="color:#64748b; font-style:italic;">Belum ada kegiatan. Yuk mulai satu!</p>
            <?php else: ?>
                <ul class="preview-list">
                    <?php foreach ($kegiatanPreview as $k): ?>
                        <?php $prog = $k['total_tahapan'] > 0 ? round(($k['selesai'] / $k['total_tahapan']) * 100) : 0; ?>
                        <li class="preview-item">
                            <a href="../tahapan/index.php?kegiatan_id=<?= (int)$k['id'] ?>">
                                <?= htmlspecialchars($k['nama_kegiatan']) ?>
                            </a>
                            <small>
                                — <?= $prog ?>% selesai
                                <?php if ($k['total_tahapan'] > $k['selesai']): ?>
                                    <span style="color:#dc2626;">(<?= $k['total_tahapan'] - $k['selesai'] ?> tahapan tertunda)</span>
                                <?php else: ?>
                                    <span style="color:#16a34a;">(Semua selesai!)</span>
                                <?php endif; ?>
                            </small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- ===== Shortcut Cepat ===== -->
        <div class="shortcuts">
            <a href="../kegiatan/tambah.php" class="btn-primary">+ Kegiatan Baru</a>
            <a href="../arsip/upload.php" class="btn-primary">Upload Dokumen</a>
        </div>

        <!-- ===== Selamat Datang + Tip ===== -->
        <div class="section">
            <h3>👋 Selamat Datang Kembali</h3>
            <p>
                PlanKita membantu Anda mengelola kegiatan dengan transparan, terencana, 
                dan terdokumentasi dengan baik.
            </p>
            <div class="tip-box">
                💡 Tip hari ini: <?= $tipHariIni ?>
            </div>
        </div>

    </div>
</div>

</body>
</html>