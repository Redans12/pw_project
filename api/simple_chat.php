<?php
// simple_chat.php - Coloca este archivo en la carpeta /api/ de tu proyecto

// Configurar cabeceras 
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Si es una solicitud preflight, respondemos solo con los headers
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Para cualquier otra solicitud que no sea POST, devolver error
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido. Use POST.'
    ]);
    exit;
}

// API key hardcodeada (solo para pruebas)
$apiKey = 'AIzaSyDgE6V0wUOH3EvIbSpK2NAGkKX5SAc9QXQ';

// Intentar obtener el mensaje del usuario
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
$userMessage = $input['message'] ?? 'Hola, ¿cómo estás?';

// Crear una respuesta estática para pruebas (sin llamar a Gemini)
$response = [
    'success' => true,
    'message' => "Esto es una respuesta de prueba a: \"$userMessage\". El sistema está funcionando correctamente.",
    'timestamp' => date('Y-m-d H:i:s')
];

// Devolver la respuesta
echo json_encode($response);