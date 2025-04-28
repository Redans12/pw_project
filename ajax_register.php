<?php
// Habilitar la visualización de errores para debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión
session_start();

// Log para debugging
error_log("Solicitud recibida en ajax_register.php");

// Verificar si la solicitud es mediante AJAX
// Omitir temporalmente esta verificación para pruebas
/*
if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    // No es una solicitud AJAX
    $response = [
        'success' => false,
        'message' => 'Acceso no permitido'
    ];
    echo json_encode($response);
    exit;
}
*/

// Log para debugging
error_log("Datos POST recibidos: " . print_r($_POST, true));

// Conectar a la base de datos
$db_host = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "cuncunul";

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Verificar conexión
if ($conn->connect_error) {
    error_log("Error de conexión: " . $conn->connect_error);
    $response = [
        'success' => false,
        'message' => 'Error de conexión a la base de datos: ' . $conn->connect_error
    ];
    echo json_encode($response);
    exit;
}

// Verificar que exista la tabla usuarios
$check_table = $conn->query("SHOW TABLES LIKE 'usuarios'");
if ($check_table->num_rows == 0) {
    // Crear la tabla si no existe
    $create_table = "CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
        ultima_sesion DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($create_table)) {
        error_log("Error al crear tabla: " . $conn->error);
        $response = [
            'success' => false,
            'message' => 'Error al crear la tabla usuarios: ' . $conn->error
        ];
        echo json_encode($response);
        exit;
    }
}

// Obtener datos del formulario
$nombre = isset($_POST['name']) ? $conn->real_escape_string($_POST['name']) : '';
$email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

// Validación de datos
if (empty($nombre) || empty($email) || empty($password) || empty($confirm_password)) {
    $response = [
        'success' => false,
        'message' => 'Todos los campos son obligatorios'
    ];
    echo json_encode($response);
    exit();
}

// Validar contraseñas
if ($password !== $confirm_password) {
    $response = [
        'success' => false,
        'message' => 'Las contraseñas no coinciden'
    ];
    echo json_encode($response);
    exit();
}

// Verificar si el correo ya está registrado
$check_email = "SELECT id FROM usuarios WHERE email = '$email'";
$result = $conn->query($check_email);

if ($result->num_rows > 0) {
    $response = [
        'success' => false,
        'message' => 'El correo electrónico ya está registrado'
    ];
    echo json_encode($response);
    exit();
}

// Encriptar contraseña
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insertar nuevo usuario
$sql = "INSERT INTO usuarios (nombre, email, password, fecha_registro, ultima_sesion) 
        VALUES ('$nombre', '$email', '$hashed_password', NOW(), NOW())";

if ($conn->query($sql) === TRUE) {
    // Iniciar sesión automáticamente
    $_SESSION['user_id'] = $conn->insert_id;
    $_SESSION['user_name'] = $nombre;
    $_SESSION['user_email'] = $email;
    
    error_log("Usuario registrado con éxito: " . $nombre);
    
    $response = [
        'success' => true,
        'message' => 'Registro exitoso',
        'redirect' => 'reservacion.php'
    ];
} else {
    error_log("Error al registrar: " . $conn->error);
    $response = [
        'success' => false,
        'message' => 'Error al registrar: ' . $conn->error
    ];
}

$conn->close();

// Devolver respuesta como JSON
echo json_encode($response);
?>