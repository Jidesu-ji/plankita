<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

/* ================= DATA KEUANGAN ================= */
$stmt = $pdo->prepare("
    SELECT *
    FROM keuangan
    WHERE user_id = ?
    ORDER BY tanggal DESC, id DESC
");
$stmt->execute([$user_id]);
$transaksi = $stmt->fetchAll();

/* ================= HITUNG SALDO ================= */
$totalMasuk = $totalKeluar = 0;
foreach ($transaksi as $t) {
    if ($t['is_void']) continue;
    if ($t['jenis'] === 'masuk') $totalMasuk += $t['jumlah'];
    else $totalKeluar += $t['jumlah'];
}
$saldo = $totalMasuk - $totalKeluar;

/* ================= PERINGATAN ================= */
$peringatan = [];
if ($saldo < 0) $peringatan[] = "Saldo negatif. Perlu verifikasi.";
foreach ($transaksi as $t) {
    if (!$t['is_void'] && $t['jumlah'] > 1000000 && empty($t['keterangan'])) {
        $peringatan[] = "Transaksi besar tanpa keterangan (Rp " . number_format($t['jumlah'],0,',','.') . ")";
    }
}

/* ================= GRAFIK BULANAN ================= */
$stmt = $pdo->prepare("
    SELECT 
        DATE_FORMAT(tanggal,'%Y-%m') AS bulan,
        SUM(CASE WHEN jenis='masuk' AND is_void=0 THEN jumlah ELSE 0 END) AS masuk,
        SUM(CASE WHEN jenis='keluar' AND is_void=0 THEN jumlah ELSE 0 END) AS keluar
    FROM keuangan
    WHERE user_id = ?
    GROUP BY bulan
    ORDER BY bulan ASC
");
$stmt->execute([$user_id]);
$grafik = $stmt->fetchAll();

$labelBulan = [];
$dataMasuk = [];
$dataKeluar = [];
foreach ($grafik as $g) {
    $labelBulan[] = date('M Y', strtotime($g['bulan'].'-01'));
    $dataMasuk[] = (int)$g['masuk'];
    $dataKeluar[] = (int)$g['keluar'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keuangan • PlanKita</title>
    <link rel="stylesheet" href="../assets/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 24px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); text-align: center; }
        .stat-card h3 { margin: 0; font-size: 14px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-card p { margin: 12px 0 0; font-size: 28px; font-weight: 700; }
        .positive { color: #16a34a; }
        .negative { color: #dc2626; }
        .chart-card { background: white; padding: 24px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .table-card { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; padding: 16px; text-align: left; font-weight: 600; color: #374151; }
        td { padding: 16px; border-top: 1px solid #f1f5f9; }
        .badge { padding: 6px 12px; border-radius: 999px; font-size: 13px; font-weight: 600; }
        .in { background: #dcfce7; color: #166534; }
        .out { background: #fee2e2; color: #991b1b; }
        .void { background: #e5e7eb; color: #374151; opacity: 0.7; }
        .warning-box { background: #fff7ed; border-left: 5px solid #f97316; padding: 16px; border-radius: 12px; margin-bottom: 30px; }
        .actions { display: flex; gap: 12px; margin: 30px 0; flex-wrap: wrap; }
        .btn-primary, .btn-secondary {
            padding: 12px 20px; border-radius: 12px; text-decoration: none; font-weight: 600; transition: 0.2s;
        }
        .btn-primary { background: #2563eb; color: white; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-secondary { background: #e2e8f0; color: #1e293b; }
        .btn-secondary:hover { background: #cbd5e1; }
        .void-form { display: flex; gap: 8px; align-items: center; }
        .void-form input { padding: 8px; border: 1px solid #cbd5e1; border-radius: 8px; width: 140px; }
        .btn-danger { background: #dc2626; color: white; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; }
        .btn-danger:hover { background: #b91c1c; }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- SIDEBAR (tetap sama) -->
    <aside class="sidebar">
        <h2>📌 PlanKita</h2>
        <a href="../dashboard/index.php">🏠 Dashboard</a>
        <a href="../kegiatan/index.php">📋 Kegiatan</a>
        <a href="../arsip/index.php">📁 Dokumen & Arsip</a>
        <a href="../anggota/index.php">👥 Anggota</a>
        <a href="index.php" class="active">💰 Keuangan</a>
        <a href="../auth/logout.php" class="logout">🚪 Logout</a>
    </aside>

    <!-- MAIN -->
    <main class="main">
        <div class="header">
            <h1>💰 Keuangan Organisasi</h1>
            <div class="actions">
                <a href="tambah.php" class="btn-primary">➕ Tambah Transaksi</a>
                <a href="import.php" class="btn-secondary">📥 Import CSV</a>
            </div>
        </div>

        <!-- Statistik -->
        <div class="stats">
            <div class="stat-card">
                <h3>Saldo Saat Ini</h3>
                <p class="<?= $saldo >= 0 ? 'positive' : 'negative' ?>">
                    Rp <?= number_format(abs($saldo),0,',','.') ?>
                </p>
            </div>
            <div class="stat-card">
                <h3>Total Pemasukan</h3>
                <p class="positive">Rp <?= number_format($totalMasuk,0,',','.') ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Pengeluaran</h3>
                <p class="negative">Rp <?= number_format($totalKeluar,0,',','.') ?></p>
            </div>
        </div>

        <!-- Grafik -->
        <div class="chart-card">
            <h3 style="margin-top:0;">📊 Tren Keuangan Bulanan</h3>
            <canvas id="grafikKeuangan" height="100"></canvas>
        </div>

        <!-- Peringatan -->
        <?php if ($peringatan): ?>
            <div class="warning-box">
                <h4 style="margin:0 0 12px;">⚠️ Peringatan Sistem</h4>
                <ul style="margin:0;">
                    <?php foreach ($peringatan as $p): ?>
                        <li><?= htmlspecialchars($p) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Tabel Transaksi -->
        <div class="table-card">
            <h3 style="margin:0 0 20px; padding:20px 24px 0;">📄 Riwayat Transaksi</h3>
            <?php if (empty($transaksi)): ?>
                <p style="padding:24px; text-align:center; color:#64748b;">Belum ada transaksi.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Jumlah</th>
                            <th>Sumber/Tujuan</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transaksi as $t): ?>
                            <tr style="<?= $t['is_void'] ? 'opacity:0.6' : '' ?>">
                                <td><?= date('d/m/Y', strtotime($t['tanggal'])) ?></td>
                                <td><span class="badge <?= $t['jenis']=='masuk'?'in':($t['is_void']?'void':'out') ?>">
                                    <?= $t['is_void'] ? 'VOID' : strtoupper($t['jenis']) ?>
                                </span></td>
                                <td>Rp <?= number_format($t['jumlah'],0,',','.') ?></td>
                                <td><?= htmlspecialchars($t['sumber_tujuan'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($t['keterangan'] ?? '-') ?></td>
                                <td>
                                    <?php if (!$t['is_void']): ?>
                                        <form class="void-form" method="POST" action="void.php">
                                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                            <input type="text" name="alasan" placeholder="Alasan VOID" required>
                                            <button type="submit" class="btn-danger" onclick="return confirm('Yakin VOID transaksi ini?')">
                                                VOID
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <em>Telah VOID</em>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
new Chart(document.getElementById('grafikKeuangan'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($labelBulan) ?>,
        datasets: [
            { label: 'Pemasukan', data: <?= json_encode($dataMasuk) ?>, backgroundColor: '#22c55e' },
            { label: 'Pengeluaran', data: <?= json_encode($dataKeluar) ?>, backgroundColor: '#ef4444' }
        ]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});
</script>

</body>
</html>