<?php
// Habilitar la visualización de errores para debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión
session_start();

// Cargar PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Cargar autoload de Composer
require 'vendor/autoload.php';

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
        telefono VARCHAR(20) NULL,
        password VARCHAR(255) NOT NULL,
        verificado TINYINT(1) DEFAULT 0,
        codigo_verificacion VARCHAR(10) NULL,
        expiracion_codigo DATETIME NULL,
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
$telefono = isset($_POST['telefono']) ? $conn->real_escape_string($_POST['telefono']) : '';

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

// Generar código de verificación aleatorio
$codigo_verificacion = mt_rand(100000, 999999); // Código de 6 dígitos
$expiracion = date('Y-m-d H:i:s', strtotime('+24 hours')); // Expira en 24 horas

// Encriptar contraseña
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insertar nuevo usuario con código de verificación
$sql = "INSERT INTO usuarios (nombre, email, telefono, password, codigo_verificacion, expiracion_codigo, fecha_registro, ultima_sesion) 
        VALUES ('$nombre', '$email', '$telefono', '$hashed_password', '$codigo_verificacion', '$expiracion', NOW(), NOW())";

if ($conn->query($sql) === TRUE) {
    // Iniciar sesión automáticamente
    $_SESSION['user_id'] = $conn->insert_id;
    $_SESSION['user_name'] = $nombre;
    $_SESSION['user_email'] = $email;
    $_SESSION['verificado'] = 0; // Usuario no verificado
    
    error_log("Usuario registrado con éxito: " . $nombre);
    
    // Preparar contenido del correo en formato HTML
    $mensaje_email = "
    <html>
    <head>
        <title>Verificación de tu cuenta en Cuncunul</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #7e2108; color: white; padding: 10px; text-align: center; }
            .content { padding: 20px; border: 1px solid #ddd; }
            .code { font-size: 24px; font-weight: bold; color: #7e2108; text-align: center; 
                    padding: 10px; background-color: #f5f5f5; margin: 20px 0; }
            .footer { font-size: 12px; text-align: center; margin-top: 20px; color: #777; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Bienvenido a Cuncunul</h1>
            </div>
            <div class='content'>
                <p>Hola $nombre,</p>
                <p>Gracias por registrarte en Restaurante Cuncunul. Para verificar tu cuenta, por favor utiliza el siguiente código de verificación:</p>
                
                <div class='code'>$codigo_verificacion</div>
                
                <p>Este código expirará en 24 horas.</p>
                <p>Si no has solicitado este registro, por favor ignora este correo.</p>
            </div>
            <div class='footer'>
                <p>Este es un correo automático, por favor no responder.</p>
                <p>&copy; " . date('Y') . " Restaurante Cuncunul. Todos los derechos reservados.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Enviar correo con PHPMailer
    $mail = new PHPMailer(true);
    $mail_enviado = false;
    
    try {
        // Configuración del servidor
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Reemplaza con tu servidor SMTP
        $mail->SMTPAuth   = true;
        $mail->Username   = 'cuncunul.help@gmail.com'; // Reemplaza con tu correo
        $mail->Password   = 'cjcd uxfs txrf tgyd'; // Reemplaza con tu contraseña o token de app
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        
        // Destinatarios
        $mail->setFrom('noreply@cuncunul.com', 'Restaurante Cuncunul');
        $mail->addAddress($email, $nombre);
        
        // Contenido
        $mail->isHTML(true);
        $mail->Subject = 'Verificación de cuenta - Restaurante Cuncunul';
        $mail->Body    = $mensaje_email;
        
        $mail->send();
        $mail_enviado = true;
        error_log("Correo de verificación enviado a: " . $email);
    } catch (Exception $e) {
        error_log("Error al enviar correo: " . $mail->ErrorInfo);
        $mail_enviado = false;
    }
    
    if ($mail_enviado) {
        $response = [
            'success' => true,
            'message' => 'Registro exitoso. Por favor, verifica tu cuenta con el código enviado a tu correo electrónico.',
            'redirect' => 'verificar.php'
        ];
    } else {
        // Si no se pudo enviar el correo, mostrar el código en la respuesta como fallback
        $response = [
            'success' => true,
            'message' => 'Registro exitoso. No se pudo enviar el correo de verificación. Tu código es: ' . $codigo_verificacion,
            'redirect' => 'verificar.php',
            'codigo' => $codigo_verificacion
        ];
    }
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