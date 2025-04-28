<?php
// Iniciar sesión
session_start();

// Habilitar visualización de errores para debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log para debugging
error_log("Solicitud recibida en ajax_login.php");

// Comentamos temporalmente la verificación de AJAX para resolver el problema
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

// Obtener datos del formulario
$email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validar que se recibieron los datos necesarios
if (empty($email) || empty($password)) {
    $response = [
        'success' => false,
        'message' => 'Por favor, ingrese correo y contraseña'
    ];
    echo json_encode($response);
    exit;
}

// Buscar usuario en la base de datos
$sql = "SELECT id, nombre, email, password FROM usuarios WHERE email = '$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // Verificar contraseña
    if (password_verify($password, $row['password'])) {
        // Iniciar sesión
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_name'] = $row['nombre'];
        $_SESSION['user_email'] = $row['email'];
        
        // Actualizar última sesión
        $update_sql = "UPDATE usuarios SET ultima_sesion = NOW() WHERE id = " . $row['id'];
        $conn->query($update_sql);
        
        error_log("Login exitoso para usuario: " . $row['nombre']);
        
        $response = [
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'redirect' => 'reservacion.php'
        ];
    } else {
        error_log("Contraseña incorrecta para: " . $email);
        $response = [
            'success' => false,
            'message' => 'Contraseña incorrecta'
        ];
    }
} else {
    error_log("Usuario no encontrado: " . $email);
    $response = [
        'success' => false,
        'message' => 'Usuario no encontrado'
    ];
}

$conn->close();

// Devolver respuesta como JSON
echo json_encode($response);
?>