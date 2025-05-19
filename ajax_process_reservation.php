<?php
// Iniciar sesión
session_start();

// Habilitar visualización de errores para debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log de depuración
error_log("Solicitud recibida en ajax_process_reservation.php");
error_log("Datos POST: " . print_r($_POST, true));
error_log("Datos SESSION: " . print_r($_SESSION, true));

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
    error_log("Error de conexión a la BD: " . $conn->connect_error);
    $response = [
        'success' => false,
        'message' => 'Error de conexión a la base de datos: ' . $conn->connect_error
    ];
    echo json_encode($response);
    exit;
}

// Verificar que exista la tabla reservaciones
$check_table = $conn->query("SHOW TABLES LIKE 'reservaciones'");
if ($check_table->num_rows == 0) {
    // Crear la tabla si no existe
    $create_table = "CREATE TABLE IF NOT EXISTS reservaciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        fecha DATE NOT NULL,
        hora TIME NOT NULL,
        num_personas INT NOT NULL,
        telefono VARCHAR(20) NULL,
        comentarios TEXT,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    )";
    
    if (!$conn->query($create_table)) {
        error_log("Error al crear tabla: " . $conn->error);
        $response = [
            'success' => false,
            'message' => 'Error al crear la tabla reservaciones: ' . $conn->error
        ];
        echo json_encode($response);
        exit;
    } else {
        error_log("Tabla reservaciones creada con éxito");
    }
}

// Obtener y validar datos del formulario
$usuario_id = $_SESSION['user_id'];
$fecha = isset($_POST['fecha']) ? $conn->real_escape_string($_POST['fecha']) : '';
$hora = isset($_POST['hora']) ? $conn->real_escape_string($_POST['hora']) : '';
$personas = isset($_POST['personas']) ? intval($_POST['personas']) : 0;
$telefono = isset($_POST['telefono']) ? $conn->real_escape_string($_POST['telefono']) : '';
$comentarios = isset($_POST['comentarios']) ? $conn->real_escape_string($_POST['comentarios']) : '';

// Validar datos
if (empty($fecha) || empty($hora) || $personas <= 0) {
    error_log("Datos de formulario incompletos/inválidos");
    $response = [
        'success' => false,
        'message' => 'Por favor complete todos los campos requeridos'
    ];
    echo json_encode($response);
    exit();
}

// Insertar la reservación con valores explícitos para cada campo
$sql = "INSERT INTO reservaciones (usuario_id, fecha, hora, num_personas, telefono, comentarios, fecha_creacion) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())";

try {
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta: " . $conn->error);
    }
    
    $stmt->bind_param("issis", $usuario_id, $fecha, $hora, $personas, $telefono, $comentarios);
    
    if ($stmt->execute()) {
        $reserva_id = $conn->insert_id;
        
        // Guardar los datos en sesión para mostrar en el resumen
        $_SESSION['reserva'] = [
            'id' => $reserva_id,
            'fecha' => $fecha,
            'hora' => $hora,
            'personas' => $personas,
            'telefono' => $telefono,
            'comentarios' => $comentarios
        ];
        
        error_log("Reservación guardada con éxito, ID: " . $reserva_id);
        
        $response = [
            'success' => true,
            'message' => 'Reservación confirmada exitosamente',
            'redirect' => 'resumen_reserva.php',
            'reserva' => $_SESSION['reserva']
        ];
    } else {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }
    
    $stmt->close();
} catch (Exception $e) {
    error_log("Error al procesar reservación: " . $e->getMessage());
    
    // Intentar hacer una inserción más simple sin prepared statement como alternativa
    $simple_sql = "INSERT INTO reservaciones (usuario_id, fecha, hora, num_personas, telefono, comentarios) 
            VALUES ($usuario_id, '$fecha', '$hora', $personas, '$telefono', '$comentarios')";
    
    if ($conn->query($simple_sql) === TRUE) {
        $reserva_id = $conn->insert_id;
        
        $_SESSION['reserva'] = [
            'id' => $reserva_id,
            'fecha' => $fecha,
            'hora' => $hora,
            'personas' => $personas,
            'telefono' => $telefono,
            'comentarios' => $comentarios
        ];
        
        error_log("Reservación guardada con método alternativo, ID: " . $reserva_id);
        
        $response = [
            'success' => true,
            'message' => 'Reservación confirmada exitosamente',
            'redirect' => 'resumen_reserva.php',
            'reserva' => $_SESSION['reserva']
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'Error al procesar la reservación: ' . $conn->error,
            'sql_error' => $conn->error,
            'query' => $simple_sql
        ];
    }
}

$conn->close();

// Devolver respuesta como JSON
echo json_encode($response);
?>