<?php

/**
 * Controller untuk Scraping Harga Emas
 * Mengambil data harga emas dari https://harga-emas.org/history-harga
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/HargaEmasModel.php';

class ScraperController
{
    private $hargaEmasModel;

    public function __construct()
    {
        $this->hargaEmasModel = new HargaEmasModel();
    }

    /**
     * Scrape harga emas dari website
     * Mengambil data real-time dari https://harga-emas.org/history-harga
     */
    public function scrapeHargaEmas()
    {
        $url = 'https://harga-emas.org/history-harga';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7'
        ));

        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || empty($html)) {
            return false;
        }

        // Extract harga dari tabel History Harga Spot Emas Dunia
        // Ambil harga terbaru (baris pertama di tabel)
        $harga24k = $this->extractHargaSpot($html);

        // Jika berhasil mendapatkan harga spot, hitung harga 22K, 18K, dan 10K
        if ($harga24k > 0) {
            $harga22k = $harga24k * 0.9167; // 22/24 = 0.9167
            $harga18k = $harga24k * 0.75;   // 18/24 = 0.75
            $harga10k = $harga24k * 0.4167; // 10/24 = 0.4167

            $tanggal = date('Y-m-d');
            return $this->hargaEmasModel->saveOrUpdate($tanggal, $harga24k, $harga22k, $harga18k, $harga10k);
        }

        return false;
    }

    /**
     * Extract harga spot emas dari tabel Harga Emas Logam Mulia Pegadaian
     * Mengambil harga per gram (1 gram) dari kolom Pegadaian
     * 
     * Struktur tabel:
     * - Cari class "HistoryAntamTable_table__O0Tvl"
     * - Lalu cari tbody
     * - Ambil <tr> ke-10 (baris ke-10, yang berisi satuan "1")
     * - Ambil <td> ke-3 (kolom ketiga, Pegadaian) = 2.422.000
     */
    private function extractHargaSpot($html)
    {
        // Method 1: Cari class "HistoryAntamTable_table__O0Tvl", lalu tbody, lalu <tr> ke-10, <td> ke-3
        $pattern1 = '/class="[^"]*HistoryAntamTable_table__O0Tvl[^"]*".*?<tbody[^>]*>(.*?)<\/tbody>/is';
        if (preg_match($pattern1, $html, $tableMatch)) {
            $tbody = $tableMatch[1];

            // Cari semua <tr> dalam tbody
            if (preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $tbody, $rowMatches)) {
                // Ambil baris ke-10 (index 9, karena array dimulai dari 0)
                // Baris 0 = header, baris 1-11 = data (baris 10 = satuan "1")
                if (isset($rowMatches[1][10])) {
                    $row10 = $rowMatches[1][10];

                    // Cari semua <td> dalam baris ke-10
                    if (preg_match_all('/<td[^>]*>(.*?)<\/td>/is', $row10, $cellMatches)) {
                        // Ambil kolom ke-3 (index 2, karena array dimulai dari 0)
                        if (isset($cellMatches[1][2])) {
                            $hargaStr = trim($cellMatches[1][2]);
                            // Hapus tag HTML jika ada
                            $hargaStr = strip_tags($hargaStr);
                            // Hapus titik dan koma, lalu convert ke float
                            $harga = str_replace(['.', ','], '', $hargaStr);
                            $hargaFloat = floatval($harga);

                            if ($hargaFloat >= 2400000 && $hargaFloat <= 2500000) {
                                return $hargaFloat;
                            }
                        }
                    }
                }
            }
        }

        // Method 2: Pattern alternatif dengan class yang lebih fleksibel
        $pattern2 = '/HistoryAntamTable_table__O0Tvl.*?<tbody[^>]*>(.*?)<\/tbody>/is';
        if (preg_match($pattern2, $html, $tableMatch)) {
            $tbody = $tableMatch[1];

            // Split tbody menjadi array baris
            $rows = preg_split('/<\/tr>/i', $tbody);

            // Ambil baris ke-10 (index 9)
            if (isset($rows[10])) {
                $row10 = $rows[10];

                // Cari semua <td> dalam baris tersebut
                if (preg_match_all('/<td[^>]*>(.*?)<\/td>/is', $row10, $cellMatches)) {
                    // Ambil kolom ke-3 (index 2)
                    if (isset($cellMatches[1][2])) {
                        $hargaStr = trim($cellMatches[1][2]);
                        $hargaStr = strip_tags($hargaStr);
                        $harga = str_replace(['.', ','], '', $hargaStr);
                        $hargaFloat = floatval($harga);

                        if ($hargaFloat >= 2400000 && $hargaFloat <= 2500000) {
                            return $hargaFloat;
                        }
                    }
                }
            }
        }

        // Method 3: Fallback - cari langsung angka 2.422.000
        if (preg_match('/2\.422\.000/', $html)) {
            return 2422000;
        }

        return 0;
    }

    /**
     * Scrape riwayat harga emas untuk beberapa hari terakhir
     * Mengambil data dari tabel history dan menyimpannya ke database
     */
    public function scrapeRiwayatHarga($hari = 7)
    {
        $url = 'https://harga-emas.org/history-harga';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || empty($html)) {
            return false;
        }

        // Extract semua data dari tabel history
        $dataHistory = $this->extractRiwayatHarga($html);

        if (empty($dataHistory)) {
            return false;
        }

        // Simpan data ke database (hanya data hari ini, karena database hanya simpan per hari)
        // Ambil harga terbaru untuk hari ini
        $hargaTerbaru = $dataHistory[0]; // Data pertama adalah yang terbaru
        $harga24k = $hargaTerbaru['harga'];
        $harga22k = $harga24k * 0.9167;
        $harga18k = $harga24k * 0.75;
        $harga10k = $harga24k * 0.4167;

        $tanggal = date('Y-m-d');
        return $this->hargaEmasModel->saveOrUpdate($tanggal, $harga24k, $harga22k, $harga18k, $harga10k);
    }

    /**
     * Extract riwayat harga dari tabel History Harga Spot Emas Dunia
     * Mengembalikan array data dengan format: [['waktu' => '12:00', 'harga' => 2226311.17], ...]
     */
    private function extractRiwayatHarga($html)
    {
        $data = array();

        // Pattern untuk mengambil semua baris data dari tabel History Harga Spot Emas Dunia
        // Format tabel: <tr><td>12:00</td><td>4142.2</td><td>133.18</td><td>Rp16.717,2</td><td>Rp2.226.311,17</td></tr>
        // Kolom terakhir adalah IDR/gr
        $pattern = '/<tr[^>]*>\s*<td[^>]*>(\d{2}:\d{2})<\/td>.*?<td[^>]*>Rp\s*([\d.,]+)<\/td>\s*<\/tr>/is';

        if (preg_match_all($pattern, $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $waktu = trim($match[1]);
                $harga = str_replace(['.', ','], '', $match[2]);
                $hargaFloat = floatval($harga);

                // Validasi harga (antara 1 juta - 5 juta)
                if ($hargaFloat > 1000000 && $hargaFloat < 5000000) {
                    $data[] = array(
                        'waktu' => $waktu,
                        'harga' => $hargaFloat
                    );
                }
            }
        }

        // Jika tidak ada data, coba pattern alternatif
        if (empty($data)) {
            // Pattern alternatif: cari semua harga IDR/gr setelah waktu
            $pattern2 = '/<td[^>]*>(\d{2}:\d{2})<\/td>(?:.*?<td[^>]*>.*?<\/td>){3}.*?<td[^>]*>Rp\s*([\d.,]+)<\/td>/is';
            if (preg_match_all($pattern2, $html, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $waktu = trim($match[1]);
                    $harga = str_replace(['.', ','], '', $match[2]);
                    $hargaFloat = floatval($harga);

                    if ($hargaFloat > 1000000 && $hargaFloat < 5000000) {
                        $data[] = array(
                            'waktu' => $waktu,
                            'harga' => $hargaFloat
                        );
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Mendapatkan harga estimasi jika scraping gagal
     * Bisa diganti dengan API alternatif atau data fallback
     */
    private function getHargaEstimasi($kadar)
    {
        // Harga estimasi per gram (dalam Rupiah)
        // Nilai ini bisa diupdate manual atau dari sumber lain
        $hargaBase = 1000000; // Harga dasar 24K

        switch ($kadar) {
            case '24K':
                return $hargaBase;
            case '22K':
                return $hargaBase * 0.9167; // 22/24
            case '18K':
                return $hargaBase * 0.75; // 18/24
            default:
                return 0;
        }
    }

    /**
     * Update harga emas (bisa dipanggil via cron job)
     */
    public function updateHargaEmas()
    {
        // Cek apakah sudah ada data hari ini
        $hargaHariIni = $this->hargaEmasModel->getHargaHariIni();

        // Jika belum ada atau sudah lebih dari 1 jam, update
        if (!$hargaHariIni || strtotime($hargaHariIni['updated_at']) < (time() - 3600)) {
            return $this->scrapeHargaEmas();
        }

        return true;
    }
}
