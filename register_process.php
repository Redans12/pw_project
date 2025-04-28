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
$nombre = $conn->real_escape_string($_POST['name']);
$email = $conn->real_escape_string($_POST['email']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Validar contraseñas
if ($password !== $confirm_password) {
    $_SESSION['error'] = "Las contraseñas no coinciden";
    header("Location: login.html?error=password_match");
    exit();
}

// Verificar si el correo ya está registrado
$check_email = "SELECT id FROM usuarios WHERE email = '$email'";
$result = $conn->query($check_email);

if ($result->num_rows > 0) {
    $_SESSION['error'] = "El correo electrónico ya está registrado";
    header("Location: login.html?error=email_exists");
    exit();
}

// Encriptar contraseña
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insertar nuevo usuario
$sql = "INSERT INTO usuarios (nombre, email, password) 
        VALUES ('$nombre', '$email', '$hashed_password')";

if ($conn->query($sql) === TRUE) {
    // Iniciar sesión automáticamente
    $_SESSION['user_id'] = $conn->insert_id;
    $_SESSION['user_name'] = $nombre;
    $_SESSION['user_email'] = $email;
    
   // Redirigir al procesamiento de reservación (versión corregida)
   header("Location: reservacion.php");
   exit();
} else {
   $_SESSION['error'] = "Error al registrar: " . $conn->error;
   header("Location: login.html?error=database");
   exit();
}

$conn->close();