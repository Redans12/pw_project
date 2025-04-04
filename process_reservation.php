<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Procesar los datos del formulario de reservación
$fecha = $_POST['fecha'];
$hora = $_POST['hora'];
$personas = $_POST['personas'];
$comentarios = isset($_POST['comentarios']) ? $_POST['comentarios'] : '';

// Guardar los datos en sesión para mostrar en el resumen
$_SESSION['reserva'] = [
    'fecha' => $fecha,
    'hora' => $hora,
    'personas' => $personas,
    'comentarios' => $comentarios
];

// Redirigir a la página de resumen
header("Location: resumen_reserva.php");
exit();
?>