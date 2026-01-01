<style>
    .container { max-width: 600px; margin: 40px auto; padding: 0 20px; }
    .card { background: white; padding: 32px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
    .card h2 { margin-top: 0; }
    .info { background: #dbeafe; padding: 16px; border-radius: 12px; margin: 20px 0; font-size: 14px; }
    input[type="file"] { width: 100%; padding: 14px; border: 1px solid #cbd5e1; border-radius: 12px; }
    button { width: 100%; padding: 16px; background: #2563eb; color: white; border: none; border-radius: 12px; font-size: 16px; font-weight: 600; margin-top: 20px; cursor: pointer; }
    button:hover { background: #1d4ed8; }
    .back { display: block; margin-top: 20px; color: #6366f1; }
    code { background: #f3f4f6; padding: 4px 8px; border-radius: 6px; font-family: monospace; }
</style>

<div class="container">
    <div class="card">
        <h2>📥 Import Transaksi dari CSV</h2>

        <div class="info">
            <strong>Format CSV:</strong><br>
            <br>
            <code>tanggal,jenis,jumlah,sumber_tujuan,keterangan</code><br><br>
            Contoh baris:<br>
            <br>
            <code>2026-01-01,masuk,5000000,Sponsor Utama,Donasi acara tahunan</code>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="csv" accept=".csv" required>
            <button type="submit">🚀 Import Transaksi</button>
        </form>

        <a href="index.php" class="back">← Kembali ke Keuangan</a>
    </div>
</div>