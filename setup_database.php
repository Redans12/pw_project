<?php
// Habilitar la visualización de errores para debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Configuración de la base de datos para Cuncunul</h1>";

// Conectar a MySQL sin seleccionar una base de datos
$conn = new mysqli("localhost", "root", "");

// Verificar la conexión
if ($conn->connect_error) {
    die("<p style='color:red'>Error de conexión: " . $conn->connect_error . "</p>");
}
echo "<p style='color:green'>Conexión a MySQL exitosa.</p>";

// Crear la base de datos si no existe
$sql = "CREATE DATABASE IF NOT EXISTS cuncunul";
if ($conn->query($sql) === TRUE) {
    echo "<p style='color:green'>Base de datos 'cuncunul' creada o ya existente.</p>";
} else {
    echo "<p style='color:red'>Error al crear la base de datos: " . $conn->error . "</p>";
    $conn->close();
    exit;
}

// Seleccionar la base de datos
$conn->select_db("cuncunul");

// Crear la tabla de usuarios si no existe
$sql = "CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultima_sesion DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "<p style='color:green'>Tabla 'usuarios' creada o ya existente.</p>";
} else {
    echo "<p style='color:red'>Error al crear la tabla 'usuarios': " . $conn->error . "</p>";
}

// Crear la tabla de reservaciones si no existe
$sql = "CREATE TABLE IF NOT EXISTS reservaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    num_personas INT NOT NULL,
    comentarios TEXT,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "<p style='color:green'>Tabla 'reservaciones' creada o ya existente.</p>";
} else {
    echo "<p style='color:red'>Error al crear la tabla 'reservaciones': " . $conn->error . "</p>";
}

// Mostrar las tablas existentes
$result = $conn->query("SHOW TABLES");
echo "<h2>Tablas en la base de datos:</h2>";
echo "<ul>";
while ($row = $result->fetch_row()) {
    echo "<li>" . $row[0] . "</li>";
}
echo "</ul>";

// Verificar estructura de la tabla usuarios
$result = $conn->query("DESCRIBE usuarios");
echo "<h2>Estructura de la tabla 'usuarios':</h2>";
echo "<table border='1'><tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por defecto</th><th>Extra</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row["Field"] . "</td>";
    echo "<td>" . $row["Type"] . "</td>";
    echo "<td>" . $row["Null"] . "</td>";
    echo "<td>" . $row["Key"] . "</td>";
    echo "<td>" . $row["Default"] . "</td>";
    echo "<td>" . $row["Extra"] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Verificar estructura de la tabla reservaciones
$result = $conn->query("DESCRIBE reservaciones");
echo "<h2>Estructura de la tabla 'reservaciones':</h2>";
echo "<table border='1'><tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por defecto</th><th>Extra</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row["Field"] . "</td>";
    echo "<td>" . $row["Type"] . "</td>";
    echo "<td>" . $row["Null"] . "</td>";
    echo "<td>" . $row["Key"] . "</td>";
    echo "<td>" . $row["Default"] . "</td>";
    echo "<td>" . $row["Extra"] . "</td>";
    echo "</tr>";
}
echo "</table>";

$conn->close();
echo "<p>Configuración completada.</p>";
?>