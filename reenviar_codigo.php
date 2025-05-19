<?php
// Iniciar sesión
session_start();

// Verificar si hay datos de pre-registro
if (!isset($_SESSION['temp_user_id']) || !isset($_SESSION['temp_user_email'])) {
    header("Location: login.html");
    exit();
}

// Obtener datos temporales del usuario
$user_id = $_SESSION['temp_user_id'];
$user_email = $_SESSION['temp_user_email'];
$user_phone = $_SESSION['temp_user_phone'];

// Conectar a la base de datos
$db_host = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "cuncunul";

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Verificar conexión
if ($conn->connect_error) {
    $mensaje = "Error de conexión a la base de datos.";
    $tipo = "error";
} else {
    // Generar nuevo código de verificación
    $nuevo_codigo = rand(100000, 999999);
    $nueva_expiracion = date('Y-m-d H:i:s', strtotime('+30 minutes'));
    
    // Actualizar código en la base de datos
    $sql = "UPDATE usuarios SET codigo_verificacion = '$nuevo_codigo', expiracion_codigo = '$nueva_expiracion' WHERE id = $user_id";
    
    if ($conn->query($sql) === TRUE) {
        // Simular envío de código
        error_log("Código reenviado para usuario ID $user_id: $nuevo_codigo");
        
        $mensaje = "Código de verificación reenviado correctamente.";
        $tipo = "success";
    } else {
        $mensaje = "Error al reenviar el código: " . $conn->error;
        $tipo = "error";
    }
    
    $conn->close();
}

// Redirigir a la página de verificación con un mensaje
$_SESSION['verification_message'] = $mensaje;
$_SESSION['verification_type'] = $tipo;
header("Location: verificar.php");
exit();
?>