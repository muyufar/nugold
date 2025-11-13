<?php
/**
 * Script Debug untuk Test Scraper
 * Jalankan: php test_scraper_debug.php
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controllers/ScraperController.php';

echo "=== TEST SCRAPER DEBUG ===\n\n";

$scraper = new ScraperController();

// Test 1: Cek koneksi database
echo "1. Testing database connection...\n";
try {
    $conn = getDBConnection();
    if ($conn) {
        echo "   ✓ Database connected\n";
    } else {
        echo "   ✗ Database connection failed\n";
    }
} catch (Exception $e) {
    echo "   ✗ Database error: " . $e->getMessage() . "\n";
}

// Test 2: Test CURL
echo "\n2. Testing CURL...\n";
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
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo "   ✗ CURL Error: $curlError\n";
} else {
    echo "   ✓ CURL OK\n";
}

echo "   HTTP Code: $httpCode\n";
echo "   HTML Length: " . strlen($html) . " bytes\n";

if ($httpCode !== 200) {
    echo "   ✗ HTTP Code is not 200\n";
    exit;
}

if (empty($html)) {
    echo "   ✗ HTML is empty\n";
    exit;
}

// Test 3: Test extract harga
echo "\n3. Testing extract harga...\n";

// Simpan HTML untuk debugging
file_put_contents(__DIR__ . '/logs/debug_html.html', $html);
echo "   HTML saved to logs/debug_html.html\n";

// Test pattern extraction
$patterns = [
    'HistoryAntamTable' => '/HistoryAntamTable_table__O0Tvl/i',
    'Tabel tbody' => '/<tbody[^>]*>/i',
    'Harga IDR/gr' => '/Rp\s*([\d.,]+)\s*\/?\s*gr/i',
    'Waktu pattern' => '/<td[^>]*>(\d{2}:\d{2})<\/td>/i',
];

foreach ($patterns as $name => $pattern) {
    if (preg_match($pattern, $html)) {
        echo "   ✓ Found: $name\n";
    } else {
        echo "   ✗ Not found: $name\n";
    }
}

// Test extract riwayat harga
echo "\n4. Testing extractRiwayatHarga...\n";
$reflection = new ReflectionClass($scraper);
$method = $reflection->getMethod('extractRiwayatHarga');
$method->setAccessible(true);
$dataHistory = $method->invoke($scraper, $html);

if (!empty($dataHistory)) {
    echo "   ✓ Found " . count($dataHistory) . " records\n";
    echo "   First record: " . print_r($dataHistory[0], true) . "\n";
} else {
    echo "   ✗ No data found\n";
}

// Test extract harga spot
echo "\n5. Testing extractHargaSpot...\n";
$method = $reflection->getMethod('extractHargaSpot');
$method->setAccessible(true);
$harga24k = $method->invoke($scraper, $html);

if ($harga24k > 0) {
    echo "   ✓ Harga 24K found: " . number_format($harga24k) . "\n";
} else {
    echo "   ✗ Harga 24K not found (value: $harga24k)\n";
}

// Test 6: Full scrape test
echo "\n6. Testing full scrape...\n";
$result = $scraper->scrapeHargaEmas();
if ($result) {
    echo "   ✓ Scrape SUCCESS\n";
} else {
    echo "   ✗ Scrape FAILED\n";
}

// Test 7: Test update harga
echo "\n7. Testing updateHargaEmas...\n";
$result = $scraper->updateHargaEmas();
if ($result) {
    echo "   ✓ Update SUCCESS\n";
} else {
    echo "   ✗ Update FAILED\n";
}

echo "\n=== END TEST ===\n";
echo "Check logs/scraper.log for detailed errors\n";

