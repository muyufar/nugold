<?php

/**
 * Konfigurasi Google OAuth 2.0
 * 
 * Cara mendapatkan Client ID dan Client Secret:
 * 1. Buka https://console.cloud.google.com/
 * 2. Buat project baru atau pilih project yang sudah ada
 * 3. Enable Google+ API
 * 4. Buka Credentials > Create Credentials > OAuth 2.0 Client ID
 * 5. Set Authorized redirect URIs: http://localhost/nugold/index.php?action=google_callback
 * 6. Copy Client ID dan Client Secret ke bawah ini
 */

define('GOOGLE_CLIENT_ID', '202618587485-hl29tpdglpfg79msp1hjq1dro201assr.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', BASE_URL . 'index.php?action=google_callback');

// Google OAuth endpoints
define('GOOGLE_AUTH_URL', 'https://accounts.google.com/o/oauth2/v2/auth');
define('GOOGLE_TOKEN_URL', 'https://oauth2.googleapis.com/token');
define('GOOGLE_USERINFO_URL', 'https://www.googleapis.com/oauth2/v2/userinfo');

/**
 * Fungsi untuk mendapatkan URL autentikasi Google
 */
function getGoogleAuthUrl()
{
    $params = array(
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'access_type' => 'online',
        'prompt' => 'consent'
    );

    return GOOGLE_AUTH_URL . '?' . http_build_query($params);
}

/**
 * Fungsi untuk mendapatkan access token dari Google
 */
function getGoogleAccessToken($code)
{
    $data = array(
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, GOOGLE_TOKEN_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $tokenData = json_decode($response, true);
        return $tokenData['access_token'] ?? null;
    }

    return null;
}

/**
 * Fungsi untuk mendapatkan data user dari Google
 */
function getGoogleUserInfo($accessToken)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, GOOGLE_USERINFO_URL . '?access_token=' . $accessToken);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        return json_decode($response, true);
    }

    return null;
}
