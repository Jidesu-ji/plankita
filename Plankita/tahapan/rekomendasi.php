<?php
/**
 * Sistem rekomendasi berbasis aturan
 * Memberikan saran perbaikan proses kegiatan
 */

function rekomendasiTahapan(array $tahapan): array
{
    $saran = [];

    foreach ($tahapan as $t) {
        if (empty($t['penanggung_jawab'])) {
            $saran[] = "Tambahkan penanggung jawab pada tahapan '{$t['nama_tahapan']}'.";
        }

        if (
            (stripos($t['nama_tahapan'], 'verifikasi') !== false ||
             stripos($t['nama_tahapan'], 'validasi') !== false)
            && !$t['perlu_bukti']
        ) {
            $saran[] = "Pertimbangkan menambahkan bukti pada tahapan '{$t['nama_tahapan']}'.";
        }
    }

    $manualCount = 0;
    foreach ($tahapan as $t) {
        if ($t['jenis_proses'] === 'manual') {
            $manualCount++;
            if ($manualCount > 2) {
                $saran[] = "Kurangi tahapan manual berturut-turut untuk efisiensi proses.";
                break;
            }
        } else {
            $manualCount = 0;
        }
    }

    if (empty($saran)) {
        $saran[] = "Struktur tahapan sudah optimal.";
    }

    return $saran;
}
