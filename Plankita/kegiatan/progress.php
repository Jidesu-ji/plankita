<?php
/**
 * Menghitung progres kegiatan berdasarkan status tahapan
 */

function hitungProgress(array $tahapan): int
{
    if (count($tahapan) === 0) return 0;

    $selesai = 0;
    foreach ($tahapan as $t) {
        if ($t['status'] === 'selesai') {
            $selesai++;
        }
    }

    return round(($selesai / count($tahapan)) * 100);
}
