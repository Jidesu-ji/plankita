<?php
/**
 * Fungsi analisis tahapan kegiatan
 * Sistem rule-based (tanpa AI)
 *
 * Tujuan:
 * - Mengidentifikasi potensi risiko proses
 * - Memberikan peringatan berbasis aturan sederhana
 * - Mendukung pengambilan keputusan pengurus
 */

function analisisTahapan(array $tahapan): array
{
    $hasil = [];

    // RULE 1: Tahapan tanpa penanggung jawab
    foreach ($tahapan as $t) {
        if (empty($t["penanggung_jawab"])) {
            $hasil[] = [
                "level" => "tinggi",
                "pesan" =>
                    "Tahapan '{$t['nama_tahapan']}' tidak memiliki penanggung jawab, berisiko tidak terlaksana."
            ];
        }
    }

    // RULE 2: Tahapan krusial tanpa bukti
    foreach ($tahapan as $t) {
        if (
            (stripos($t["nama_tahapan"], "verifikasi") !== false ||
             stripos($t["nama_tahapan"], "validasi") !== false)
            && !$t["perlu_bukti"]
        ) {
            $hasil[] = [
                "level" => "sedang",
                "pesan" =>
                    "Tahapan '{$t['nama_tahapan']}' bersifat krusial namun tidak memiliki bukti pendukung."
            ];
        }
    }

    // RULE 3: Terlalu banyak proses manual berurutan
    $manualCount = 0;
    foreach ($tahapan as $t) {
        if ($t["jenis_proses"] === "manual") {
            $manualCount++;
            if ($manualCount > 2) {
                $hasil[] = [
                    "level" => "sedang",
                    "pesan" =>
                        "Terdapat lebih dari dua tahapan manual berturut-turut yang dapat menurunkan efisiensi."
                ];
                break;
            }
        } else {
            $manualCount = 0;
        }
    }

    // RULE 4: Tidak ada tahapan sama sekali
    if (count($tahapan) === 0) {
        $hasil[] = [
            "level" => "tinggi",
            "pesan" =>
                "Kegiatan belum memiliki tahapan, sehingga tidak dapat dipantau progresnya."
        ];
    }

    // Jika tidak ada risiko
    if (empty($hasil)) {
        $hasil[] = [
            "level" => "rendah",
            "pesan" =>
                "Tidak ditemukan risiko signifikan pada alur kegiatan."
        ];
    }

    return $hasil;
}
