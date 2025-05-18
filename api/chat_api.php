<?php
// Habilitar CORS para JavaScript
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar si es una petición OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Verificar si es una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Cargar la API key desde el archivo .env
function loadEnv($file) {
    if (!file_exists($file)) {
        throw new Exception('Archivo .env no encontrado');
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
    // Cargar variables de entorno
    // Primero intentar desde archivo .env (desarrollo local)
    $apiKey = null;
    
    if (file_exists(__DIR__ . '/.env')) {
        $env = loadEnv(__DIR__ . '/.env');
        $apiKey = $env['GEMINI_API_KEY'] ?? null;
    }
    
    // Si no encontramos la clave en .env, buscar en variables de entorno del sistema
    // Esto funciona para Vercel, Heroku, etc.
    if (!$apiKey) {
        $apiKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY');
    }
    
    if (!$apiKey) {
        throw new Exception('API key no encontrada. Configura GEMINI_API_KEY en las variables de entorno.');
    }
    
    // Obtener el mensaje del usuario
    $input = json_decode(file_get_contents('php://input'), true);
    $userMessage = $input['message'] ?? '';
    
    if (empty($userMessage)) {
        throw new Exception('Mensaje vacío');
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
    
    // Realizar la petición cURL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        throw new Exception('Error cURL: ' . curl_error($ch));
    }
    
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception('Error HTTP: ' . $httpCode . ' - ' . $response);
    }
    
    $responseData = json_decode($response, true);
    
    if (!$responseData || !isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        throw new Exception('Respuesta inválida de la API');
    }
    
    $aiResponse = $responseData['candidates'][0]['content']['parts'][0]['text'];
    
    // Devolver la respuesta
    echo json_encode([
        'success' => true,
        'message' => $aiResponse
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}