<?php
// Iniciar sesión
session_start();

// Habilitar logs para depuración
error_log("Procesando reservación - Inicio del script");
error_log("Datos recibidos en ajax_process_reservation.php: " . print_r($_POST, true));
error_log("Cabeceras recibidas: " . print_r(apache_request_headers(), true));

// Comentar temporalmente esta verificación
/*
if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    // No es una solicitud AJAX
    error_log("Acceso no permitido - No es una solicitud AJAX");
    $response = [
        'success' => false,
        'message' => 'Acceso no permitido'
    ];
    echo json_encode($response);
    exit;
}
*/

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    error_log("Sesión no válida - Usuario no logueado");
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
    error_log("Error de conexión a la base de datos: " . $conn->connect_error);
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
$telefono = isset($_POST['telefono']) ? $conn->real_escape_string($_POST['telefono']) : '';
$comentarios = isset($_POST['comentarios']) ? $conn->real_escape_string($_POST['comentarios']) : '';

// Validar datos
if (empty($fecha) || empty($hora) || $personas <= 0) {
    error_log("Validación fallida - Campos requeridos faltantes");
    $response = [
        'success' => false,
        'message' => 'Por favor complete todos los campos requeridos'
    ];
    echo json_encode($response);
    exit();
}

// Preparar consulta SQL
$sql = "INSERT INTO reservaciones (usuario_id, fecha, hora, num_personas, telefono, comentarios, fecha_creacion) 
        VALUES ($usuario_id, '$fecha', '$hora', $personas, '$telefono', '$comentarios', NOW())";

error_log("SQL a ejecutar: " . $sql);

// Guardar la reservación en la base de datos
if ($conn->query($sql) === TRUE) {
    // Guardar los datos en sesión para mostrar en el resumen
    $_SESSION['reserva'] = [
        'id' => $conn->insert_id,
        'fecha' => $fecha,
        'hora' => $hora,
        'personas' => $personas,
        'telefono' => $telefono,
        'comentarios' => $comentarios
    ];
    
    error_log("Reservación guardada con éxito. ID: " . $conn->insert_id);
    
    $response = [
        'success' => true,
        'message' => 'Reservación confirmada exitosamente',
        'redirect' => 'resumen_reserva.php',
        'reserva' => $_SESSION['reserva']
    ];
} else {
    error_log("Error al guardar reservación: " . $conn->error);
    $response = [
        'success' => false,
        'message' => 'Error al procesar la reservación: ' . $conn->error
    ];
}

$conn->close();

// Devolver respuesta como JSON
echo json_encode($response);
?>