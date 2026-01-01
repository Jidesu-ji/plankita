<?php
require_once "../tahapan/analisis.php";

/**
 * Fungsi ini dipakai di dua tempat:
 * 1. Dashboard summary → hanya punya array status string
 * 2. Halaman tahapan/detail → punya data tahapan lengkap
 */
function statusKegiatan(array $tahapanStatuses): array
{
    // Kasus: hanya array string status (dari dashboard summary)
    if (!empty($tahapanStatuses) && is_string($tahapanStatuses[0] ?? null)) {
        // Hanya cek apakah semua selesai atau tidak
        $semuaSelesai = true;
        foreach ($tahapanStatuses as $status) {
            if ($status !== 'selesai') {
                $semuaSelesai = false;
                break;
            }
        }

        if ($semuaSelesai && count($tahapanStatuses) > 0) {
            return [
                'label' => 'Aman',
                'warna' => '#22c55e',
                'ikon'  => '🟢'
            ];
        }

        return [
            'label' => 'Perlu Perhatian',
            'warna' => '#f59e0b',
            'ikon'  => '🟡'
        ];
    }

    // Kasus: data tahapan lengkap (dari halaman tahapan/index)
    if (empty($tahapanStatuses)) {
        return [
            'label' => 'Belum Ada Tahapan',
            'warna' => '#6b7280',
            'ikon'  => '⚪'
        ];
    }

    $hasil = analisisTahapan($tahapanStatuses);

    if (count($hasil) === 1 && $hasil[0]['level'] === 'rendah') {
        return [
            'label' => 'Aman',
            'warna' => '#22c55e',
            'ikon'  => '🟢'
        ];
    }

    if (count($hasil) <= 3) {  // sedikit temuan
        return [
            'label' => 'Perlu Perhatian',
            'warna' => '#f59e0b',
            'ikon'  => '🟡'
        ];
    }

    return [
        'label' => 'Berisiko',
        'warna' => '#ef4444',
        'ikon'  => '🔴'
    ];
}