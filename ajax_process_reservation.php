<?php
// Iniciar sesión
session_start();

// Verificar si la solicitud es mediante AJAX
if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    // No es una solicitud AJAX
    $response = [
        'success' => false,
        'message' => 'Acceso no permitido'
    ];
    echo json_encode($response);
    exit;
}

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    $response = [
        'success' => false,
        'message' => 'Sesión no válida',
        'redirect' => 'login.html'
    ];
    echo json_encode($response);
    exit();
}

// Conectar a la base de datos
$db_host = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "cuncunul";

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Verificar conexión
if ($conn->connect_error) {
    $response = [
        'success' => false,
        'message' => 'Error de conexión a la base de datos: ' . $conn->connect_error
    ];
    echo json_encode($response);
    exit;
}

// Obtener datos del formulario
$usuario_id = $_SESSION['user_id'];
$fecha = $conn->real_escape_string($_POST['fecha']);
$hora = $conn->real_escape_string($_POST['hora']);
$personas = intval($_POST['personas']);
$comentarios = isset($_POST['comentarios']) ? $conn->real_escape_string($_POST['comentarios']) : '';

// Validar datos
if (empty($fecha) || empty($hora) || $personas <= 0) {
    $response = [
        'success' => false,
        'message' => 'Por favor complete todos los campos requeridos'
    ];
    echo json_encode($response);
    exit();
}

// Guardar la reservación en la base de datos
$sql = "INSERT INTO reservaciones (usuario_id, fecha, hora, num_personas, comentarios, fecha_creacion) 
        VALUES ($usuario_id, '$fecha', '$hora', $personas, '$comentarios', NOW())";

if ($conn->query($sql) === TRUE) {
    // Guardar los datos en sesión para mostrar en el resumen
    $_SESSION['reserva'] = [
        'id' => $conn->insert_id,
        'fecha' => $fecha,
        'hora' => $hora,
        'personas' => $personas,
        'comentarios' => $comentarios
    ];
    
    $response = [
        'success' => true,
        'message' => 'Reservación confirmada exitosamente',
        'redirect' => 'resumen_reserva.php',
        'reserva' => $_SESSION['reserva']
    ];
} else {
    $response = [
        'success' => false,
        'message' => 'Error al procesar la reservación: ' . $conn->error
    ];
}

$conn->close();

// Devolver respuesta como JSON
echo json_encode($response);
?>