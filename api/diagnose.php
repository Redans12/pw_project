<?php
// diagnose.php - Coloca este archivo en la carpeta /api/ de tu proyecto

// Configurar cabeceras para permitir acceso y evitar cachés
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Recopilar información del sistema
$info = [
    'success' => true,
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido',
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'path' => $_SERVER['REQUEST_URI'],
    'client_ip' => $_SERVER['REMOTE_ADDR'],
    'environment' => [
        'gemini_key_exists' => !empty(getenv('GEMINI_API_KEY')),
        'gemini_key_length' => strlen(getenv('GEMINI_API_KEY')),
        'env_vars' => array_keys($_ENV),
        'server_vars' => array_keys($_SERVER),
    ],
    'extensions' => [
        'curl' => extension_loaded('curl'),
        'json' => extension_loaded('json'),
        'openssl' => extension_loaded('openssl'),
    ],
    'input' => file_get_contents('php://input')
];

// Probar la conexión a la API de Gemini (simplificada)
$key = getenv('GEMINI_API_KEY') ?: 'AIzaSyDgE6V0wUOH3EvIbSpK2NAGkKX5SAc9QXQ';
$testUrl = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $key;

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $testUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0,
    CURLOPT_TIMEOUT => 10
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$errorMsg = curl_error($ch);
curl_close($ch);

$info['gemini_test'] = [
    'http_code' => $httpCode,
    'success' => $httpCode >= 200 && $httpCode < 300,
    'error' => $errorMsg,
    'response_sample' => substr($response, 0, 200) . (strlen($response) > 200 ? '...' : '')
];

// Devolver toda la información recopilada como JSON
echo json_encode($info, JSON_PRETTY_PRINT);