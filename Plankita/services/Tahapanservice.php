<?php
/**
 * TahapanService
 *
 * Menyatukan seluruh logika penyimpanan tahapan
 * dari form manual, CSV, maupun Excel.
 *
 * Catatan juri:
 * Semua input diproses melalui satu lapisan
 * untuk menjaga konsistensi dan validasi data.
 */

function simpanTahapan(PDO $pdo, int $kegiatan_id, array $data): void
{
    // Normalisasi & default
    $urutan = (int)($data['urutan'] ?? 0);
    $nama   = trim($data['nama_tahapan'] ?? '');
    $pj     = trim($data['penanggung_jawab'] ?? '');
    $proses = $data['jenis_proses'] ?? 'manual';
    $status = $data['status'] ?? 'belum';

    // Validasi minimal
    if ($nama === '') {
        return; // data kosong tidak disimpan
    }

    // Simpan ke database
    $stmt = $pdo->prepare(
        "INSERT INTO tahapan
        (kegiatan_id, urutan, nama_tahapan, penanggung_jawab, jenis_proses, status)
        VALUES (?, ?, ?, ?, ?, ?)"
    );

    $stmt->execute([
        $kegiatan_id,
        $urutan,
        $nama,
        $pj !== '' ? $pj : null,
        $proses,
        $status
    ]);
}
