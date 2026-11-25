<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$projectDir = str_replace('\\', '/', __DIR__);
$documentRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));
$basePath = '/';
if ($documentRoot !== '' && stripos($projectDir, $documentRoot) === 0) {
    $basePath = substr($projectDir, strlen($documentRoot));
}
define('BASE_URL', rtrim($basePath === '' ? '/' : $basePath, '/') . '/');
define('SITE_NAME', 'PetAdopt India');

$apiBaseUrl = getenv('API_BASE_URL');
if ($apiBaseUrl === false || $apiBaseUrl === '') {
    $apiBaseUrl = 'http://127.0.0.1:3000/api/v1';
}
define('API_BASE_URL', rtrim($apiBaseUrl, '/'));

function app_url($path = '') {
    return BASE_URL . ltrim($path, '/');
}

function app_api_request($method, $path, $data = null, $upload = null) {
    $url = API_BASE_URL . '/' . ltrim($path, '/');
    $headers = ['Accept: application/json'];
    $content = '';
    $hasUpload = is_array($upload) && isset($upload['tmp_name']) && $upload['error'] === UPLOAD_ERR_OK;

    if (is_array($upload) && isset($upload['error']) && $upload['error'] !== UPLOAD_ERR_NO_FILE && $upload['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'status' => 422, 'error' => 'The uploaded file could not be read.'];
    }
    if (!empty($_SESSION['api_token'])) {
        $headers[] = 'Authorization: Bearer ' . $_SESSION['api_token'];
    }

    if ($hasUpload) {
        $boundary = '----PetAdopt' . bin2hex(random_bytes(12));
        foreach ((array)$data as $name => $value) {
            $content .= '--' . $boundary . "\r\n";
            $content .= 'Content-Disposition: form-data; name="' . addslashes($name) . '"' . "\r\n\r\n";
            $content .= (string)$value . "\r\n";
        }
        $filename = str_replace(["\r", "\n", '"'], '', basename($upload['name']));
        $mime = function_exists('mime_content_type') ? mime_content_type($upload['tmp_name']) : 'application/octet-stream';
        $fileContents = file_get_contents($upload['tmp_name']);
        if ($fileContents === false) {
            return ['ok' => false, 'status' => 422, 'error' => 'Could not read the uploaded image.'];
        }
        $content .= '--' . $boundary . "\r\n";
        $content .= 'Content-Disposition: form-data; name="image"; filename="' . $filename . '"' . "\r\n";
        $content .= 'Content-Type: ' . ($mime ?: 'application/octet-stream') . "\r\n\r\n";
        $content .= $fileContents . "\r\n";
        $content .= '--' . $boundary . "--\r\n";
        $headers[] = 'Content-Type: multipart/form-data; boundary=' . $boundary;
    } elseif ($data !== null) {
        $content = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($content === false) {
            return ['ok' => false, 'status' => 0, 'error' => 'Could not encode the API request.'];
        }
        $headers[] = 'Content-Type: application/json';
    }

    $context = stream_context_create([
        'http' => [
            'method' => strtoupper($method),
            'header' => implode("\r\n", $headers),
            'content' => $content,
            'timeout' => 5,
            'ignore_errors' => true,
            'follow_location' => 0
        ]
    ]);
    $responseBody = @file_get_contents($url, false, $context);
    if ($responseBody === false) {
        $lastError = error_get_last();
        error_log('Backend API request failed for ' . $path . ': ' . ($lastError['message'] ?? 'unknown transport error'));
        return ['ok' => false, 'status' => 0, 'error' => 'Backend server is unavailable. Please try again later.'];
    }

    $status = 0;
    foreach ($http_response_header ?? [] as $header) {
        if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $header, $matches)) $status = (int)$matches[1];
    }
    $response = json_decode($responseBody, true);
    if (!is_array($response)) {
        error_log('Backend API returned invalid JSON for ' . $path);
        return ['ok' => false, 'status' => $status, 'error' => 'Backend server returned an invalid response.'];
    }

    return [
        'ok' => $status >= 200 && $status < 300,
        'status' => $status,
        'data' => $response['data'] ?? null,
        'response' => $response,
        'error' => $response['error']['message'] ?? null
    ];
}

function app_api_error($result, $fallback = 'The request could not be completed.') {
    return $result['error'] ?? $fallback;
}

function upload_url($filename) {
    return app_url('uploads/' . rawurlencode(basename($filename)));
}
