<?php
session_start();
require_once "../config/database.php";
require_once "status.php";
require_once "progress.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// ambil semua kegiatan user
$stmt = $pdo->prepare("SELECT * FROM kegiatan WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$kegiatan = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kegiatan • PlanKita</title>
<link rel="stylesheet" href="../assets/dashboard.css">
<style>
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 24px;
}
.kartu {
    background: #fff;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}
.kartu:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.12);
}
.kartu h3 {
    margin: 0 0 8px;
    font-size: 20px;
    color: #1e293b;
}
.meta {
    font-size: 14px;
    color: #64748b;
    margin-bottom: 12px;
}
.badge {
    display: inline-block;
    padding: 8px 14px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 12px;
}
.progress {
    background: #e2e8f0;
    height: 10px;
    border-radius: 999px;
    overflow: hidden;
    margin: 12px 0;
}
.progress div {
    height: 100%;
    background: #2563eb;
    border-radius: 999px;
    transition: width 0.6s ease;
}
.progress-text {
    font-size: 15px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 8px;
}
.status-count {
    font-size: 14px;
    color: #64748b;
    margin-top: 12px;
}
.actions {
    display: flex;
    gap: 12px;
    margin-top: 20px;
    flex-wrap: wrap;
}
.actions a {
    flex: 1;
    min-width: 120px;
    padding: 12px 16px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    text-align: center;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.primary {
    background: #2563eb;
    color: #fff;
}
.primary:hover {
    background: #1d4ed8;
    transform: translateY(-2px);
}
.secondary {
    background: #e2e8f0;
    color: #1e293b;
}
.secondary:hover {
    background: #cbd5e1;
    transform: translateY(-2px);
}
.danger {
    background: #fee2e2;
    color: #dc2626;
}
.danger:hover {
    background: #fecaca;
}
</style>
</head>

<body>
<div class="wrapper">

<!-- SIDEBAR -->
<aside class="sidebar">
    <h2>📌 PlanKita</h2>
    <a href="../dashboard/index.php">🏠 Dashboard</a>
    <a href="index.php" class="active">📋 Kegiatan</a>
    <a href="../arsip/index.php">📁 Dokumen & Arsip</a>
    <a href="../anggota/index.php">👥 Anggota</a>
    <a href="../keuangan/index.php">💰 Keuangan</a>
    <a href="../auth/logout.php" class="logout">🚪 Logout</a>
</aside>

<!-- MAIN -->
<main class="main">
<div class="header">
    <h1>📋 Kegiatan</h1>
    <a href="tambah.php" class="btn-primary">➕ Kegiatan Baru</a>
</div>

<section class="section">
<?php if (empty($kegiatan)): ?>
    <p style="text-align:center; color:#64748b; font-style:italic; padding:60px 0;">
        Belum ada kegiatan. Mulai buat yang pertama!
    </p>
<?php else: ?>
<div class="grid">

<?php foreach ($kegiatan as $k): ?>
<?php
    $t = $pdo->prepare("SELECT * FROM tahapan WHERE kegiatan_id = ? ORDER BY urutan ASC");
    $t->execute([$k['id']]);
    $tahapan = $t->fetchAll();

    $progress = hitungProgress($tahapan);
    $statusData = statusKegiatan($tahapan);
    $label = $statusData['label'];
    $warna = $statusData['warna'];
    $ikon  = $statusData['ikon'];

    $selesai = $jalan = $belum = 0;
    foreach ($tahapan as $th) {
        if ($th['status'] === 'selesai') $selesai++;
        elseif ($th['status'] === 'berjalan') $jalan++;
        else $belum++;
    }
?>
<div class="kartu">
    <h3><?= htmlspecialchars($k['nama_kegiatan']) ?></h3>
    <div class="meta"><?= htmlspecialchars($k['jenis_kegiatan']) ?></div>

    <span class="badge" style="background:<?= $warna ?>22;color:<?= $warna ?>">
        <?= $ikon ?> <?= $label ?>
    </span>

    <div class="progress-text"><?= $progress ?>% Selesai</div>
    <div class="progress">
        <div style="width:<?= $progress ?>%"></div>
    </div>

    <div class="status-count">
        ✔ <?= $selesai ?> selesai • ⏳ <?= $jalan ?> berjalan • ⏸ <?= $belum ?> belum
    </div>

    <div class="actions">
        <a href="../tahapan/index.php?kegiatan_id=<?= $k['id'] ?>" class="primary">
            🧩 Lihat Tahapan
        </a>
        <a href="hapus.php?id=<?= $k['id'] ?>" class="danger"
           onclick="return confirm('Hapus kegiatan ini beserta semua tahapannya?')">
            🗑 Hapus
        </a>
    </div>
</div>
<?php endforeach; ?>

</div>
<?php endif; ?>
</section>

</main>
</div>
</body>
</html>