<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

function getChildren($pdo, $user_id, $parent_id = null) {
    $sql = $parent_id === null 
        ? "SELECT * FROM anggota WHERE user_id = ? AND parent_id IS NULL"
        : "SELECT * FROM anggota WHERE user_id = ? AND parent_id = ?";
    $sql .= " ORDER BY FIELD(level,'ketua','wakil','kepala','anggota'), created_at ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parent_id === null ? [$user_id] : [$user_id, $parent_id]);
    return $stmt->fetchAll();
}

function renderTree($pdo, $user_id, $parent_id = null) {
    $children = getChildren($pdo, $user_id, $parent_id);
    if (empty($children)) return;

    echo '<ul class="org-tree">';
    foreach ($children as $a) {
        $levelColor = match($a['level']) {
            'ketua' => '#dc2626',
            'wakil' => '#7c2d12',
            'kepala' => '#2563eb',
            'anggota' => '#16a34a',
            default => '#6b7280'
        };

        $levelLabel = match($a['level']) {
            'ketua' => 'Ketua',
            'wakil' => 'Wakil Ketua',
            'kepala' => 'Kepala Divisi',
            'anggota' => 'Anggota',
            default => ucfirst($a['level'])
        };

        echo '<li>';
        echo '<div class="member-card" style="border-top: 5px solid ' . $levelColor . ';">';
        echo '<h3>' . htmlspecialchars($a['nama']) . '</h3>';
        echo '<span class="level-badge" style="background:' . $levelColor . ';">' . $levelLabel . '</span>';

        if (!empty($a['deskripsi_tugas'])) {
            echo '<p class="tugas">' . nl2br(htmlspecialchars($a['deskripsi_tugas'])) . '</p>';
        }

        echo '<div class="member-actions">';
        if (!empty($a['bukti_file'])) {
            echo '<a href="' . htmlspecialchars($a['bukti_file']) . '" target="_blank">📎 Bukti</a>';
        }
        echo '<a href="edit.php?id=' . $a['id'] . '">✏️ Edit</a>';
        echo '<a href="hapus.php?id=' . $a['id'] . '" 
              onclick="return confirm(\'Hapus ' . htmlspecialchars($a['nama']) . ' dan semua bawahan?\')" 
              class="danger">🗑 Hapus</a>';
        echo '</div>';
        echo '</div>';

        renderTree($pdo, $user_id, $a['id']);

        echo '</li>';
    }
    echo '</ul>';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struktur Anggota • PlanKita</title>
    <link rel="stylesheet" href="../assets/dashboard.css">
    <style>
        .org-container { padding: 20px; overflow-x: auto; }
        .org-tree { 
            list-style: none; 
            padding: 0; 
            margin: 0; 
            position: relative; 
        }
        .org-tree ul {
            padding-left: 50px;
            position: relative;
            margin: 20px 0;
        }
        .org-tree ul::before {
            content: '';
            position: absolute;
            top: -20px;
            left: 20px;
            bottom: 0;
            width: 2px;
            background: #cbd5e1;
        }
        .org-tree li {
            position: relative;
            padding-left: 50px;
            margin-bottom: 30px;
        }
        .org-tree li::before {
            content: '';
            position: absolute;
            top: 40px;
            left: 20px;
            width: 30px;
            height: 2px;
            background: #cbd5e1;
        }
        .member-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            min-width: 280px;
            position: relative;
        }
        .member-card h3 { margin: 0 0 8px; font-size: 20px; color: #1e293b; }
        .level-badge { 
            display: inline-block;
            padding: 6px 14px;
            border-radius: 999px;
            color: white;
            font-size: 13px;
            font-weight: 600;
        }
        .tugas { 
            font-size: 14px; 
            color: #475569; 
            margin: 12px 0; 
            line-height: 1.5; 
        }
        .member-actions { 
            display: flex; 
            gap: 12px; 
            flex-wrap: wrap; 
            margin-top: 12px; 
            font-size: 13px; 
        }
        .member-actions a { color: #2563eb; text-decoration: none; }
        .member-actions .danger { color: #dc2626; }
        .member-actions a:hover { text-decoration: underline; }
        .empty-state { text-align: center; padding: 80px 20px; color: #64748b; }
        .btn-primary {
            display: inline-block;
            padding: 14px 28px;
            background: #2563eb;
            color: white;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(37,99,235,0.3);
            transition: all 0.2s;
        }
        .btn-primary:hover { background: #1d4ed8; transform: translateY(-2px); }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- SIDEBAR tetap sama -->
    <aside class="sidebar">
        <h2>📌 PlanKita</h2>
        <a href="../dashboard/index.php">🏠 Dashboard</a>
        <a href="../kegiatan/index.php">📋 Kegiatan</a>
        <a href="../arsip/index.php">📁 Dokumen & Arsip</a>
        <a href="index.php" class="active">👥 Anggota</a>
        <a href="../keuangan/index.php">💰 Keuangan</a>
        <a href="../auth/logout.php" class="logout">🚪 Logout</a>
    </aside>

    <main class="main">
        <div class="header">
            <h1>🌿 Struktur Organisasi</h1>
            <a href="tambah.php" class="btn-primary">➕ Tambah Anggota</a>
        </div>

        <div class="org-container">
            <?php if (empty(getChildren($pdo, $user_id, null))): ?>
                <div class="empty-state">
                    <p>Belum ada anggota terdaftar.</p>
                    <a href="tambah.php" class="btn-primary">Mulai Tambah Ketua Organisasi</a>
                </div>
            <?php else: ?>
                <?php renderTree($pdo, $user_id); ?>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>