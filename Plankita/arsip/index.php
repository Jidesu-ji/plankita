<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$stmt = $pdo->prepare(
    "SELECT a.*, k.nama_kegiatan
     FROM arsip a
     LEFT JOIN kegiatan k ON a.kegiatan_id = k.id
     WHERE a.user_id = ?
     ORDER BY uploaded_at DESC"
);
$stmt->execute([$user_id]);
$arsip = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Arsip & Dokumen • PlanKita</title>
    <link rel="stylesheet" href="../assets/dashboard.css">
    <style>
        .card {
            background: white;
            padding: 18px 20px;
            border-radius: 14px;
            margin-bottom: 14px;
            box-shadow: 0 6px 16px rgba(0,0,0,.06);
        }

        .card small {
            color: #6b7280;
        }

        .btn {
            display: inline-block;
            padding: 10px 14px;
            background: #25d7ebff;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-size: 14px;
        }

        .btn:hover {
            background: #1d4ed8;
        }

        .btn-danger {
            background: #dc2626;
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .actions {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>

<div class="wrapper">

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>📌 PlanKita</h2>
        <a href="../dashboard/index.php">🏠 Dashboard</a>
        <a href="../kegiatan/index.php">📋 Kegiatan</a>
        <a href="../arsip/index.php" class="active">📁 Dokumen & Arsip</a>
        <a href="../anggota/index.php">👥 Anggota</a>
        <a href="../keuangan/index.php">💰 Keuangan</a>

        <a href="../auth/logout.php" class="logout">🚪 Logout</a>
    </div>

    <!-- Main -->
    <div class="main">

        <h2>📂 Arsip & Dokumen</h2>

        <a href="upload.php" class="btn">
            ➕ Upload Dokumen
        </a>

        <hr>

        <?php if (empty($arsip)): ?>
            <p><em>Belum ada dokumen.</em></p>
        <?php else: ?>

            <?php foreach ($arsip as $a): ?>
                <div class="card">
                    <strong><?= htmlspecialchars($a['judul']) ?></strong><br>

                    <small>
                        Jenis: <?= htmlspecialchars($a['jenis']) ?>
                        <?= $a['nama_kegiatan']
                            ? " • Kegiatan: ".htmlspecialchars($a['nama_kegiatan'])
                            : "" ?>
                    </small>

                    <div class="actions">
                        <a
                            href="<?= htmlspecialchars($a['file_path']) ?>"
                            target="_blank"
                            class="btn"
                        >
                            📄 Lihat
                        </a>

                        <a
                            href="hapus.php?id=<?= (int)$a['id'] ?>"
                            onclick="return confirm('Hapus dokumen ini?')"
                            class="btn btn-danger"
                        >
                            🗑 Hapus
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>

        <br>

    </div>
</div>

</body>
</html>
