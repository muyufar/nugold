<?php

/**
 * File Test Scraper
 * Untuk test apakah pattern regex berhasil mengambil data dari website
 * HAPUS file ini setelah testing selesai!
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controllers/ScraperController.php';

echo "<h2>Test Scraper Harga Emas Pegadaian</h2>";

$scraper = new ScraperController();

// Test scraping
echo "<h3>1. Test Scraping Harga Emas</h3>";
$result = $scraper->scrapeHargaEmas();

if ($result) {
    echo "<p style='color: green;'>✓ Scraping berhasil!</p>";

    // Ambil data dari database
    require_once __DIR__ . '/models/HargaEmasModel.php';
    $hargaModel = new HargaEmasModel();
    $hargaHariIni = $hargaModel->getHargaHariIni();

    if ($hargaHariIni) {
        echo "<h4>Data yang tersimpan di database:</h4>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Tanggal</th><th>Harga 24K</th><th>Harga 22K</th><th>Harga 18K</th></tr>";
        echo "<tr>";
        echo "<td>" . $hargaHariIni['tanggal'] . "</td>";
        echo "<td>Rp " . number_format($hargaHariIni['harga_24k'], 0, ',', '.') . "</td>";
        echo "<td>Rp " . number_format($hargaHariIni['harga_22k'], 0, ',', '.') . "</td>";
        echo "<td>Rp " . number_format($hargaHariIni['harga_18k'], 0, ',', '.') . "</td>";
        echo "</tr>";
        echo "</table>";

        // Cek apakah harga sesuai (sekitar 2.422.000)
        if ($hargaHariIni['harga_24k'] >= 2400000 && $hargaHariIni['harga_24k'] <= 2500000) {
            echo "<p style='color: green;'>✓ Harga sesuai dengan yang diharapkan (2.422.000)</p>";
        } else {
            echo "<p style='color: orange;'>⚠ Harga tidak sesuai. Diharapkan: 2.422.000, Ditemukan: " . number_format($hargaHariIni['harga_24k'], 0, ',', '.') . "</p>";
        }
    }
} else {
    echo "<p style='color: red;'>✗ Scraping gagal!</p>";
}

echo "<hr>";
echo "<h3>2. Test Raw HTML (untuk debugging)</h3>";
echo "<p>Mengambil HTML dari website...</p>";

$url = 'https://harga-emas.org/history-harga';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$html = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && !empty($html)) {
    echo "<p style='color: green;'>✓ HTML berhasil diambil</p>";

    // Cari pattern untuk baris dengan "1"
    echo "<h4>Mencari pattern untuk baris dengan satuan '1':</h4>";

    // Pattern 1: Cari <td>1</td> diikuti 2 kolom
    $pattern1 = '/<td[^>]*>\s*1\s*<\/td>.*?<td[^>]*>([\d.,]+)<\/td>.*?<td[^>]*>([\d.,]+)<\/td>/is';
    if (preg_match_all($pattern1, $html, $matches, PREG_SET_ORDER)) {
        echo "<p style='color: green;'>Pattern 1 ditemukan " . count($matches) . " match:</p>";
        foreach ($matches as $i => $match) {
            echo "<p>Match " . ($i + 1) . ": Antam = " . $match[1] . ", Pegadaian = " . $match[2] . "</p>";
        }
    } else {
        echo "<p style='color: red;'>Pattern 1 tidak ditemukan</p>";
    }

    // Pattern 2: Tanpa whitespace
    $pattern2 = '/<td[^>]*>1<\/td>.*?<td[^>]*>([\d.,]+)<\/td>.*?<td[^>]*>([\d.,]+)<\/td>/is';
    if (preg_match_all($pattern2, $html, $matches, PREG_SET_ORDER)) {
        echo "<p style='color: green;'>Pattern 2 ditemukan " . count($matches) . " match:</p>";
        foreach ($matches as $i => $match) {
            echo "<p>Match " . ($i + 1) . ": Antam = " . $match[1] . ", Pegadaian = " . $match[2] . "</p>";
        }
    } else {
        echo "<p style='color: red;'>Pattern 2 tidak ditemukan</p>";
    }

    // Pattern 3: Cari dalam konteks <tr>
    $pattern3 = '/<tr[^>]*>.*?<td[^>]*>\s*1\s*<\/td>.*?<td[^>]*>([\d.,]+)<\/td>.*?<td[^>]*>([\d.,]+)<\/td>.*?<\/tr>/is';
    if (preg_match_all($pattern3, $html, $matches, PREG_SET_ORDER)) {
        echo "<p style='color: green;'>Pattern 3 ditemukan " . count($matches) . " match:</p>";
        foreach ($matches as $i => $match) {
            echo "<p>Match " . ($i + 1) . ": Antam = " . $match[1] . ", Pegadaian = " . $match[2] . "</p>";
        }
    } else {
        echo "<p style='color: red;'>Pattern 3 tidak ditemukan</p>";
    }

    // Tampilkan HTML sekitar angka 2.422.000 untuk melihat strukturnya
    echo "<h4>HTML sekitar angka 2.422.000:</h4>";
    if (preg_match('/2\.422\.000/', $html, $match, PREG_OFFSET_CAPTURE)) {
        $pos = $match[0][1];
        $context = substr($html, max(0, $pos - 1000), 2000);
        echo "<pre style='background: #f5f5f5; padding: 10px; overflow-x: auto; font-size: 12px;'>";
        echo htmlspecialchars($context);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>Tidak dapat menemukan angka 2.422.000</p>";
    }

    // Cari semua baris yang berisi "1" dan angka 2.4xx.xxx
    echo "<h4>Mencari semua angka yang mirip 2.422.000:</h4>";
    $pattern3 = '/2\.4\d{2}\.\d{3}/';
    if (preg_match_all($pattern3, $html, $matches)) {
        echo "<p>Ditemukan " . count($matches[0]) . " angka yang mirip 2.4xx.xxx:</p>";
        $unique = array_unique($matches[0]);
        foreach ($unique as $num) {
            echo "<p>- " . $num . "</p>";
        }
    }

    // Cari semua kemunculan angka "1" dalam tag <td> untuk melihat formatnya
    echo "<h4>Mencari semua tag &lt;td&gt; yang berisi angka '1':</h4>";
    if (preg_match_all('/<td[^>]*>.*?1.*?<\/td>/is', $html, $matches)) {
        echo "<p>Ditemukan " . count($matches[0]) . " tag &lt;td&gt; yang berisi '1':</p>";
        $count = 0;
        foreach ($matches[0] as $td) {
            if ($count < 5) { // Tampilkan 5 pertama saja
                echo "<pre style='background: #f0f0f0; padding: 5px; margin: 5px 0; font-size: 11px;'>";
                echo htmlspecialchars($td);
                echo "</pre>";
                $count++;
            }
        }
    } else {
        echo "<p style='color: red;'>Tidak ditemukan tag &lt;td&gt; yang berisi '1'</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Gagal mengambil HTML</p>";
}

echo "<hr>";
echo "<p><strong>PENTING:</strong> Hapus file ini setelah testing selesai!</p>";
echo "<p><a href='index.php'>Kembali ke Aplikasi</a></p>";
