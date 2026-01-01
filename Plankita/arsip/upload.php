<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$kegiatan = $pdo->query(
    "SELECT id, nama_kegiatan FROM kegiatan ORDER BY nama_kegiatan ASC"
)->fetchAll();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== 0) {
        $error = "File gagal diupload.";
    } else {

        // ================================
        // VALIDASI FILE (AMAN & MASUK AKAL)
        // ================================
        $allowed = ['pdf','doc','docx','xls','xlsx','ppt','pptx','jpg','png'];
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error = "Jenis file tidak didukung.";
        } else {

            // Pastikan folder upload ada
            if (!is_dir("../uploads")) {
                mkdir("../uploads", 0777, true);
            }

            $namaFile = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['file']['name']);
            $path = "../uploads/" . $namaFile;

            move_uploaded_file($_FILES['file']['tmp_name'], $path);

            // ================================
            // SIMPAN KE DATABASE
            // ================================
            $stmt = $pdo->prepare(
                "INSERT INTO arsip
                 (user_id, kegiatan_id, judul, jenis, file_path)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $user_id,
                $_POST['kegiatan_id'] ?: null,
                $_POST['judul'],
                $_POST['jenis'],
                $path
            ]);

            header("Location: index.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Upload Dokumen • PlanKita</title>
    <style>
        body {
            font-family: system-ui, sans-serif;
            background: #f1f5f9;
            padding: 40px;
        }

        .card {
            max-width: 520px;
            margin: auto;
            background: white;
            padding: 26px;
            border-radius: 18px;
            box-shadow: 0 10px 25px rgba(0,0,0,.08);
        }

        h2 {
            margin-bottom: 20px;
        }

        input, select, button {
            width: 100%;
            padding: 12px;
            margin-bottom: 14px;
            border-radius: 10px;
            border: 1px solid #cbd5f5;
            font-size: 14px;
        }

        button {
            background: #2563eb;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 14px;
        }

        a {
            display: inline-block;
            margin-top: 14px;
            text-decoration: none;
            color: #2563eb;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>📤 Upload Dokumen</h2>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input
            type="text"
            name="judul"
            placeholder="Judul dokumen"
            required
        >

        <input
            type="text"
            name="jenis"
            placeholder="Jenis (LPJ, Proposal, Surat, dll)"
        >

        <select name="kegiatan_id">
            <option value="">-- Dokumen Umum --</option>
            <?php foreach ($kegiatan as $k): ?>
                <option value="<?= $k['id'] ?>">
                    <?= htmlspecialchars($k['nama_kegiatan']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="file" name="file" required>

        <button type="submit">⬆ Upload Dokumen</button>
    </form>

    <a href="index.php">← Kembali ke Arsip</a>
</div>

</body>
</html>
