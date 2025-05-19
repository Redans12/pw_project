<?php
// Habilitar CORS para JavaScript
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Verificar si es una petición OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Habilitar visualización de errores para debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log para debugging
error_log("Solicitud de chat recibida");

// Verificar si es una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Obtener el contenido raw del cuerpo de la petición
$input_data = file_get_contents('php://input');
error_log("Datos recibidos: " . $input_data);

// Intentar decodificar JSON
$input = json_decode($input_data, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("Error al decodificar JSON: " . json_last_error_msg());
    echo json_encode(['success' => false, 'error' => 'Error al procesar los datos: ' . json_last_error_msg()]);
    exit;
}

// Cargar la API key desde el archivo .env
function loadEnv($file) {
    if (!file_exists($file)) {
        return [];
    }
    
    $env = [];
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && $line[0] !== '#') {
            list($key, $value) = explode('=', $line, 2);
            $env[trim($key)] = trim($value);
        }
    }
    
    return $env;
}

try {
    // Obtener el mensaje del usuario
    $userMessage = isset($input['message']) ? trim($input['message']) : '';
    
    if (empty($userMessage)) {
        throw new Exception('Mensaje vacío');
    }
    
    // Usar una API key hardcodeada para desarrollo local 
    // (no recomendado para producción, pero útil para pruebas)
    $apiKey = 'AIzaSyDgE6V0wUOH3EvIbSpK2NAGkKX5SAc9QXQ';
    
    // También intentar cargar desde .env si existe
    $envFilePaths = [
        __DIR__ . '/.env',
        __DIR__ . '/../.env',
        dirname(__DIR__) . '/.env'
    ];
    
    foreach ($envFilePaths as $envFile) {
        if (file_exists($envFile)) {
            $env = loadEnv($envFile);
            if (isset($env['GEMINI_API_KEY']) && !empty($env['GEMINI_API_KEY'])) {
                $apiKey = $env['GEMINI_API_KEY'];
                break;
            }
        }
    }
    
    // También buscar en variables de entorno del sistema
    if (getenv('GEMINI_API_KEY')) {
        $apiKey = getenv('GEMINI_API_KEY');
    }
    
    // Configurar el contexto del restaurante
    $systemPrompt = "Eres un asistente virtual del restaurante Cuncunul, especializado en comida yucateca y maya. Tu trabajo es:
1. Responder preguntas sobre el menú, ingredientes y precios
2. Ayudar con información sobre reservaciones (horarios: Lunes a Domingo 13:00-23:00)
3. Dar información sobre el restaurante y su filosofía
4. Sugerir platillos según preferencias
5. Explicar la cultura culinaria yucateca

Mantén un tono amable, profesional y conocedor. Si no sabes algo específico, dilo honestamente y ofrece contactar al restaurante.

Mensaje del usuario: " . $userMessage;
    
    // Preparar la petición a Gemini
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;
    
    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $systemPrompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'topK' => 40,
            'topP' => 0.95,
            'maxOutputTokens' => 1024
        ]
    ];
    
    $jsonData = json_encode($data);
    error_log("Enviando a Gemini: " . $jsonData);
    
    // Realizar la petición cURL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ],
        CURLOPT_TIMEOUT => 30,
        // Deshabilitar verificación SSL para entornos de desarrollo
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Log de la respuesta
    error_log("Respuesta de Gemini (HTTP $httpCode): " . $response);
    
    if (curl_errno($ch)) {
        throw new Exception('Error cURL: ' . curl_error($ch));
    }
    
    curl_close($ch);
    
    // Si hay error HTTP
    if ($httpCode !== 200) {
        throw new Exception('Error HTTP al contactar Gemini API: ' . $httpCode . ' - ' . $response);
    }
    
    $responseData = json_decode($response, true);
    
    // Verificar si la estructura es la esperada
    if (!$responseData || !isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        throw new Exception('Respuesta inválida o inesperada de la API de Gemini');
    }
    
    $aiResponse = $responseData['candidates'][0]['content']['parts'][0]['text'];
    
    // Devolver la respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => $aiResponse
    ]);
    
} catch (Exception $e) {
    error_log("Error en el chat API: " . $e->getMessage());
    
    // Devolver error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => [
            'apiKey_exists' => !empty($apiKey),
            'apiKey_length' => !empty($apiKey) ? strlen($apiKey) : 0,
            'php_version' => phpversion(),
            'curl_enabled' => function_exists('curl_init'),
        ]
    ]);
}