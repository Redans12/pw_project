<?php
// Iniciar sesión
session_start();

// Conectar a la base de datos
$db_host = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "cuncunul";

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener datos del formulario
$email = $conn->real_escape_string($_POST['email']);
$password = $_POST['password'];

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
        
        // Redirigir a la página de reserva
        header("Location: reservacion.php");
        exit();
    } else {
        $_SESSION['error'] = "Contraseña incorrecta";
        header("Location: login.html?error=password");
        exit();
    }
} else {
    $_SESSION['error'] = "Usuario no encontrado";
    header("Location: login.html?error=user");
    exit();
}

$conn->close();

?>