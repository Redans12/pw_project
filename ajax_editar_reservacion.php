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

// Verificar si se proporcionaron todos los datos necesarios
if (!isset($_POST['id']) || !isset($_POST['fecha']) || !isset($_POST['hora']) || !isset($_POST['personas'])) {
    $response = [
        'success' => false,
        'message' => 'Faltan datos requeridos'
    ];
    echo json_encode($response);
    exit();
}

// Obtener y validar datos del formulario
$reservacion_id = intval($_POST['id']);
$usuario_id = $_SESSION['user_id'];
$fecha = $_POST['fecha'];
$hora = $_POST['hora'];
$personas = intval($_POST['personas']);
$comentarios = isset($_POST['comentarios']) ? $_POST['comentarios'] : '';
$telefono = isset($_POST['telefono']) ? $_POST['telefono'] : '';

// Validar datos básicos
if (empty($fecha) || empty($hora) || $personas <= 0) {
    $response = [
        'success' => false,
        'message' => 'Por favor complete todos los campos requeridos correctamente'
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

// Escapar datos para evitar inyección SQL
$fecha = $conn->real_escape_string($fecha);
$hora = $conn->real_escape_string($hora);
$comentarios = $conn->real_escape_string($comentarios);
$telefono = $conn->real_escape_string($telefono);

// Verificar que la reservación pertenezca al usuario actual
$check_sql = "SELECT id FROM reservaciones WHERE id = $reservacion_id AND usuario_id = $usuario_id";
$check_result = $conn->query($check_sql);

if ($check_result->num_rows === 0) {
    $response = [
        'success' => false,
        'message' => 'No tienes permiso para editar esta reservación'
    ];
    echo json_encode($response);
    $conn->close();
    exit();
}

// Actualizar la reservación
$update_sql = "UPDATE reservaciones SET 
               fecha = '$fecha',
               hora = '$hora',
               num_personas = $personas,
               comentarios = '$comentarios'
               WHERE id = $reservacion_id AND usuario_id = $usuario_id";

if ($conn->query($update_sql) === TRUE) {
    $response = [
        'success' => true,
        'message' => 'Reservación actualizada con éxito'
    ];
} else {
    $response = [
        'success' => false,
        'message' => 'Error al actualizar la reservación: ' . $conn->error
    ];
}

$conn->close();

// Devolver respuesta como JSON
echo json_encode($response);
?>