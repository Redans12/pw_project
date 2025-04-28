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

// Verificar si se proporcionó el ID de la reservación
if (!isset($_POST['id']) || empty($_POST['id'])) {
    $response = [
        'success' => false,
        'message' => 'ID de reservación no proporcionado'
    ];
    echo json_encode($response);
    exit();
}

// Obtener el ID de reservación y del usuario
$reservacion_id = intval($_POST['id']);
$usuario_id = $_SESSION['user_id'];

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

// Verificar que la reservación pertenezca al usuario actual
$check_sql = "SELECT id FROM reservaciones WHERE id = $reservacion_id AND usuario_id = $usuario_id";
$check_result = $conn->query($check_sql);

if ($check_result->num_rows === 0) {
    $response = [
        'success' => false,
        'message' => 'No tienes permiso para cancelar esta reservación'
    ];
    echo json_encode($response);
    $conn->close();
    exit();
}

// Eliminar la reservación
$delete_sql = "DELETE FROM reservaciones WHERE id = $reservacion_id AND usuario_id = $usuario_id";

if ($conn->query($delete_sql) === TRUE) {
    $response = [
        'success' => true,
        'message' => 'Reservación cancelada con éxito'
    ];
} else {
    $response = [
        'success' => false,
        'message' => 'Error al cancelar la reservación: ' . $conn->error
    ];
}

$conn->close();

// Devolver respuesta como JSON
echo json_encode($response);