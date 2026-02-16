<?php
session_start();

if (!isset($_SESSION['user']) || !isset($_SESSION['spreadsheet_id'])) {
    echo json_encode(['error' => 'Anda harus login terlebih dahulu!']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request.']);
    exit;
}

// === Fungsi untuk dapatkan access token dari Service Account ===
function get_google_access_token($service_account_file) {
    $jwt_header = ['alg' => 'RS256', 'typ' => 'JWT'];
    $now = time();
    $jwt_claim = [
        'iss' => '', // akan diisi dari file
        'scope' => 'https://www.googleapis.com/auth/spreadsheets',
        'aud' => 'https://oauth2.googleapis.com/token',
        'exp' => $now + 3600,
        'iat' => $now
    ];

    $json = json_decode(file_get_contents($service_account_file), true);
    $jwt_claim['iss'] = $json['client_email'];

    $header = rtrim(strtr(base64_encode(json_encode($jwt_header)), '+/', '-_'), '=');
    $claim = rtrim(strtr(base64_encode(json_encode($jwt_claim)), '+/', '-_'), '=');
    $signature_input = $header . '.' . $claim;

    // Sign JWT
    $private_key = openssl_pkey_get_private($json['private_key']);
    openssl_sign($signature_input, $signature, $private_key, 'sha256WithRSAEncryption');
    $signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

    $jwt = $signature_input . '.' . $signature;

    // Exchange JWT for access token
    $post_fields = [
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ];
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    $result = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($result, true);
    return $data['access_token'] ?? null;
}

// === Fungsi request ke Google Sheets API ===
function sheets_api_request($method, $url, $accessToken, $body = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    $result = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) return ['error' => $err];
    return json_decode($result, true);
}

// === Ambil access token dari service account ===
$service_account_file = __DIR__ . '/Login Required/credentials.json'; // Pastikan path benar
$accessToken = get_google_access_token($service_account_file);

if (!$accessToken) {
    echo json_encode(['error' => 'Gagal mendapatkan access token dari Service Account']);
    exit;
}

$spreadsheetId = $_SESSION['spreadsheet_id'];
$sheetName = isset($_SESSION['halaqoh']) ? $_SESSION['halaqoh'] : 'Sheet1';

$action = $_POST['action'] ?? '';

if ($action === 'update') {
    $cell = $_POST['cell'] ?? '';
    $value = $_POST['value'] ?? '';
    if (empty($cell) || $value === null) {
        echo json_encode(['error' => 'Cell atau nilai kosong.']);
        exit;
    }
    $range = $sheetName . '!' . $cell;
    $url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId/values/$range?valueInputOption=USER_ENTERED";
    $body = json_encode(['values' => [[$value]]]);
    $result = sheets_api_request('PUT', $url, $accessToken, $body);
    if (isset($result['error'])) {
        echo json_encode(['error' => 'Error: ' . ($result['error']['message'] ?? $result['error'])]);
    } else {
        echo json_encode(['message' => "Sukses! Cell $cell diupdate menjadi $value"]);
    }
} elseif ($action === 'read') {
    $range = $_POST['range'] ?? ($sheetName . '!B3:B17');
    $url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId/values/$range";
    $result = sheets_api_request('GET', $url, $accessToken);
    if (isset($result['error'])) {
        echo json_encode(['error' => 'Error: ' . ($result['error']['message'] ?? $result['error'])]);
    } else {
        echo json_encode(['values' => $result['values'] ?? []]);
    }
} else {
    echo json_encode(['error' => 'Aksi tidak valid.']);
}
?>