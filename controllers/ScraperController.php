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
     * Log error untuk debugging
     */
    private function logError($message)
    {
        $logFile = __DIR__ . '/../logs/scraper.log';
        $logDir = dirname($logFile);

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logMessage = date('Y-m-d H:i:s') . ' - ' . $message . "\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
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
        $curlError = curl_error($ch);
        curl_close($ch);

        // Log error jika ada
        if ($curlError) {
            $this->logError("CURL Error: " . $curlError);
        }

        if ($httpCode !== 200 || empty($html)) {
            $this->logError("HTTP Code: $httpCode, HTML empty: " . (empty($html) ? 'yes' : 'no'));
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
            $result = $this->hargaEmasModel->saveOrUpdate($tanggal, $harga24k, $harga22k, $harga18k, $harga10k);

            if (!$result) {
                $this->logError("Failed to save harga to database");
            }

            return $result;
        }

        $this->logError("Failed to extract harga from HTML (harga24k = $harga24k)");
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
        // Method 1: Cari baris dengan satuan "1" gram, lalu ambil harga dari kolom Pegadaian (kolom ke-3)
        // Pattern: cari <tr> yang berisi <p>1</p> di kolom pertama, lalu ambil harga dari kolom ketiga
        $pattern1 = '/<tr[^>]*>.*?<td[^>]*>.*?<p>1<\/p>.*?<\/td>.*?<td[^>]*>.*?<p>([\d.,]+)<\/p>.*?<\/td>.*?<td[^>]*>.*?<p>([\d.,]+)<\/p>.*?<\/td>.*?<\/tr>/is';
        if (preg_match($pattern1, $html, $matches)) {
            // $matches[1] = harga Antam, $matches[2] = harga Pegadaian
            // Ambil harga Pegadaian (kolom ke-3)
            if (isset($matches[2])) {
                $hargaStr = trim($matches[2]);
                $harga = str_replace(['.', ','], '', $hargaStr);
                $hargaFloat = floatval($harga);

                // Validasi harga 2.3-2.6 juta (harga 24K yang benar)
                if ($hargaFloat >= 2300000 && $hargaFloat <= 2900000) {
                    return $hargaFloat;
                }
            }
        }

        // Method 1b: Pattern alternatif dengan class HistoryAntamTable
        $pattern1b = '/class="[^"]*HistoryAntamTable_table__O0Tvl[^"]*".*?<tbody[^>]*>(.*?)<\/tbody>/is';
        if (preg_match($pattern1b, $html, $tableMatch)) {
            $tbody = $tableMatch[1];

            // Cari baris yang berisi satuan "1"
            if (preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $tbody, $rowMatches)) {
                foreach ($rowMatches[1] as $row) {
                    // Cek apakah baris ini berisi satuan "1"
                    if (preg_match('/<p>1<\/p>/i', $row)) {
                        // Ambil semua <td> dalam baris ini
                        if (preg_match_all('/<td[^>]*>(.*?)<\/td>/is', $row, $cellMatches)) {
                            // Kolom ke-3 (index 2) adalah harga Pegadaian
                            if (isset($cellMatches[1][2])) {
                                $hargaStr = trim($cellMatches[1][2]);
                                $hargaStr = strip_tags($hargaStr);
                                $harga = str_replace(['.', ','], '', $hargaStr);
                                $hargaFloat = floatval($harga);

                                // Validasi harga 2.3-2.6 juta
                                if ($hargaFloat >= 2300000 && $hargaFloat <= 2900000) {
                                    return $hargaFloat;
                                }
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

                        // Prioritas untuk harga 2.3-2.6 juta (harga 24K yang benar)
                        if ($hargaFloat >= 2300000 && $hargaFloat <= 2900000) {
                            return $hargaFloat;
                        }
                        // Fallback untuk range lebih luas
                        if ($hargaFloat >= 1000000 && $hargaFloat <= 5000000) {
                            // Simpan sebagai kandidat, tapi cari yang lebih tepat dulu
                            $this->logError("Found harga in wide range: $hargaFloat, but looking for 2.3-2.6M range");
                        }
                    }
                }
            }
        }

        // Method 3: Cari harga IDR/gr dari tabel riwayat (lebih reliable)
        $dataHistory = $this->extractRiwayatHarga($html);
        if (!empty($dataHistory) && isset($dataHistory[0]['harga'])) {
            $harga = $dataHistory[0]['harga'];
            // Prioritas untuk harga 2.3-2.6 juta
            if ($harga >= 2300000 && $harga <= 2900000) {
                return $harga;
            }
            // Fallback untuk range lebih luas
            if ($harga >= 1000000 && $harga <= 5000000) {
                return $harga;
            }
        }

        // Method 4: Cari harga yang lebih spesifik (sekitar 2.4 juta - harga 24K yang benar)
        // Prioritas untuk harga antara 2.3 juta - 2.6 juta (range harga 24K yang wajar)
        $pattern4 = '/Rp\s*([\d.,]+)/i';
        if (preg_match_all($pattern4, $html, $matches)) {
            $candidates = [];
            foreach ($matches[1] as $hargaStr) {
                $harga = str_replace(['.', ','], '', $hargaStr);
                $hargaFloat = floatval($harga);
                // Prioritas untuk harga 2.3-2.6 juta (harga 24K yang benar)
                if ($hargaFloat >= 2300000 && $hargaFloat <= 2900000) {
                    $candidates[] = $hargaFloat;
                }
            }
            // Jika ada kandidat di range 2.3-2.6 juta, ambil yang pertama
            if (!empty($candidates)) {
                return $candidates[0];
            }
            // Jika tidak ada, cari di range lebih luas (1-5 juta)
            foreach ($matches[1] as $hargaStr) {
                $harga = str_replace(['.', ','], '', $hargaStr);
                $hargaFloat = floatval($harga);
                if ($hargaFloat >= 1000000 && $hargaFloat <= 5000000) {
                    return $hargaFloat;
                }
            }
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
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7'
        ));

        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            $this->logError("scrapeRiwayatHarga - CURL Error: " . $curlError);
        }

        if ($httpCode !== 200 || empty($html)) {
            $this->logError("scrapeRiwayatHarga - HTTP Code: $httpCode, HTML empty: " . (empty($html) ? 'yes' : 'no'));
            return false;
        }

        // Extract semua data dari tabel history
        $dataHistory = $this->extractRiwayatHarga($html);

        if (empty($dataHistory)) {
            $this->logError("scrapeRiwayatHarga - No data extracted from HTML");
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
        $result = $this->hargaEmasModel->saveOrUpdate($tanggal, $harga24k, $harga22k, $harga18k, $harga10k);

        if (!$result) {
            $this->logError("scrapeRiwayatHarga - Failed to save to database");
        }

        return $result;
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
            // Pattern alternatif 1: cari semua harga IDR/gr setelah waktu
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

        // Pattern alternatif 2: Cari semua harga dengan format Rp X.XXX.XXX atau Rp X,XXX,XXX
        if (empty($data)) {
            $pattern3 = '/Rp\s*([\d.,]+)\s*\/?\s*gr/i';
            if (preg_match_all($pattern3, $html, $matches)) {
                foreach ($matches[1] as $hargaStr) {
                    $harga = str_replace(['.', ','], '', $hargaStr);
                    $hargaFloat = floatval($harga);
                    if ($hargaFloat > 1000000 && $hargaFloat < 5000000) {
                        // Ambil harga pertama yang valid
                        $data[] = array(
                            'waktu' => date('H:i'),
                            'harga' => $hargaFloat
                        );
                        break; // Ambil yang pertama saja
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

        // Tentukan apakah perlu update
        $needUpdate = false;

        if (!$hargaHariIni) {
            // Belum ada data hari ini, perlu update
            $needUpdate = true;
        } else {
            // Cek apakah sudah lebih dari 1 jam sejak update terakhir
            $lastUpdate = isset($hargaHariIni['updated_at']) ? strtotime($hargaHariIni['updated_at']) : strtotime($hargaHariIni['created_at']);
            if ($lastUpdate < (time() - 3600)) {
                $needUpdate = true;
            }
        }

        if ($needUpdate) {
            // Coba method 1: scrapeHargaEmas
            $result = $this->scrapeHargaEmas();

            // Jika gagal, coba method 2: scrapeRiwayatHarga
            if (!$result) {
                $this->logError("scrapeHargaEmas failed, trying scrapeRiwayatHarga");
                $result = $this->scrapeRiwayatHarga(1);
            }

            if (!$result) {
                $this->logError("All scraping methods failed");
            }

            return $result;
        }

        // Data sudah ada dan masih fresh (kurang dari 1 jam)
        return true;
    }
}
