<?php
function dashboardSummary($pdo, $user_id) {
    $summary = [
        'total_kegiatan' => 0,
        'total_tahapan' => 0,
        'progress' => 0,
        'berisiko' => 0
    ];

    // Total Kegiatan
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM kegiatan WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $summary['total_kegiatan'] = (int)$stmt->fetchColumn();

    // Total Tahapan
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tahapan t JOIN kegiatan k ON t.kegiatan_id = k.id WHERE k.user_id = ?");
    $stmt->execute([$user_id]);
    $summary['total_tahapan'] = (int)$stmt->fetchColumn();

    // Progress Rata-rata
    $stmt = $pdo->prepare("
        SELECT AVG(
            (SELECT COUNT(*) FROM tahapan WHERE kegiatan_id = k.id AND status = 'selesai') / 
            NULLIF((SELECT COUNT(*) FROM tahapan WHERE kegiatan_id = k.id), 0) * 100
        ) AS avg_progress
        FROM kegiatan k WHERE k.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();
    $summary['progress'] = $row && $row['avg_progress'] !== null ? round($row['avg_progress']) : 0;

    // Kegiatan Berisiko (contoh: yang progress < 50% atau analisis bilang risiko)
    // Sesuaikan dengan logika analisis-mu kalau ada
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM kegiatan WHERE user_id = ?"); // placeholder, ganti dengan logika risiko-mu
    $stmt->execute([$user_id]);
    $summary['berisiko'] = (int)$stmt->fetchColumn(); // atau logika lain

    return $summary;
}
?>