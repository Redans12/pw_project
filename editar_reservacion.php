<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Verificar si se proporcionó el ID de reservación
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: mis_reservaciones.php");
    exit();
}

// Obtener el ID de reservación y del usuario
$reservacion_id = intval($_GET['id']);
$usuario_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Conectar a la base de datos
$db_host = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "cuncunul";

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Verificar que la reservación pertenezca al usuario actual
$sql = "SELECT * FROM reservaciones WHERE id = $reservacion_id AND usuario_id = $usuario_id";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    // La reservación no existe o no pertenece al usuario
    $conn->close();
    header("Location: mis_reservaciones.php");
    exit();
}

// Obtener datos de la reservación
$reserva = $result->fetch_assoc();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <title>Cuncunul - Editar Reservación</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="resources/cero.ico" type="image/x-icon" />

    <!-- W3.CSS -->
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Kurale&family=Quattrocento+Sans&family=Raleway:wght@400&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="restaurantStyle.css">
    <link rel="stylesheet" href="reservacionStyle.css">
    <link rel="stylesheet" href="notification.css">
    <link rel="stylesheet" href="chat.css">
    <!-- Script de Ajax -->
    <script src="ajax_handler.js"></script>
</head>

<body class="background">
    <!-- Header -->
    <div class="w3-top" id="navbar">
        <div class="w3-bar w3-padding w3-card">
            <a href="index.html" class="w3-bar-item w3-button logo-container">
                <img src="resources/cero.ico" alt="Logo Cuncunul" class="header-logo">
                <span style='font-family: kurale, serif; font-size: larger;'>Cuncunul</span>
            </a>
            <div class="w3-right w3-hide-small">
                <a href="index.html#about" class="w3-bar-item w3-button">Sobre nosotros</a>
                <a href="menu.html" class="w3-bar-item w3-button">Menú</a>
                <a href="index.html#contact" class="w3-bar-item w3-button">Contacto</a>
                <a href="logout.php" class="w3-bar-item w3-button">Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <!-- Contenedor de Edición de Reservación -->
    <div class="reservation-container">
        <div class="reservation-header">
            <h2>Editar Reservación</h2>
        </div>

        <div class="welcome-message">
            <p>Modifica los detalles de tu reservación, <?php echo htmlspecialchars($user_name); ?>.</p>
        </div>

        <form id="edit-reservation-form">
            <input type="hidden" id="reservacion-id" value="<?php echo $reservacion_id; ?>">

            <div class="form-row">
                <div class="form-column">
                    <div class="form-group">
                        <label for="fecha">Fecha</label>
                        <input type="date" id="fecha" name="fecha" class="form-control" required value="<?php echo htmlspecialchars($reserva['fecha']); ?>">
                    </div>
                </div>
                <div class="form-column">
                    <div class="form-group">
                        <label for="hora">Hora</label>
                        <input type="time" id="hora" name="hora" class="form-control" required value="<?php echo htmlspecialchars($reserva['hora']); ?>">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-column">
                    <div class="form-group">
                        <label for="personas">Número de personas</label>
                        <select id="personas" name="personas" class="form-control" required>
                            <?php for ($i = 1; $i <= 12; $i++) : ?>
                                <option value="<?php echo $i; ?>" <?php echo ($reserva['num_personas'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="form-column">
                    <div class="form-group">
                        <label for="telefono">Teléfono de contacto</label>
                        <input type="tel" id="telefono" name="telefono" class="form-control" required value="<?php echo isset($reserva['telefono']) ? htmlspecialchars($reserva['telefono']) : ''; ?>">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="comentarios">Comentarios o solicitudes especiales</label>
                <textarea id="comentarios" name="comentarios" class="form-control" rows="4"><?php echo htmlspecialchars($reserva['comentarios']); ?></textarea>
            </div>

            <div class="form-buttons">
                <button type="submit" class="btn">Guardar Cambios</button>
                <a href="mis_reservaciones.php" class="btn btn-cancelar">Cancelar</a>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <footer>
        <img src="resources/cero.ico" alt="Logo Cuncunul" class="header-logo">
    </footer>

    <script>
        // Configurar fecha mínima como hoy
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const yyyy = today.getFullYear();
            let mm = today.getMonth() + 1;
            let dd = today.getDate();

            if (dd < 10) dd = '0' + dd;
            if (mm < 10) mm = '0' + mm;

            const formattedToday = yyyy + '-' + mm + '-' + dd;
            document.getElementById('fecha').min = formattedToday;
        });

        // Manejar envío del formulario de edición con AJAX
        document.getElementById('edit-reservation-form').addEventListener('submit', function(e) {
            e.preventDefault();

            // Agregar clase de carga
            this.classList.add('form-loading');

            const reservacionId = document.getElementById('reservacion-id').value;
            const fecha = document.getElementById('fecha').value;
            const hora = document.getElementById('hora').value;
            const personas = document.getElementById('personas').value;
            const telefono = document.getElementById('telefono').value;
            const comentarios = document.getElementById('comentarios').value;

            // Crear objeto FormData para enviar
            const formData = new FormData();
            formData.append('id', reservacionId);
            formData.append('fecha', fecha);
            formData.append('hora', hora);
            formData.append('personas', personas);
            formData.append('telefono', telefono);
            formData.append('comentarios', comentarios);

            // Convertir a URL-encoded
            const data = Array.from(formData.entries())
                .map(pair => encodeURIComponent(pair[0]) + '=' + encodeURIComponent(pair[1]))
                .join('&');

            // Crear y configurar la solicitud XHR
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'ajax_editar_reservacion.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhr.onload = function() {
                // Quitar clase de carga
                document.getElementById('edit-reservation-form').classList.remove('form-loading');

                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const response = JSON.parse(xhr.responseText);

                        if (response.success) {
                            showNotification(response.message, 'success');
                            // Redireccionar después de un breve retraso
                            setTimeout(function() {
                                window.location.href = 'mis_reservaciones.php';
                            }, 1000);
                        } else {
                            showNotification(response.message, 'error');
                        }
                    } catch (e) {
                        showNotification('Error al procesar la respuesta', 'error');
                    }
                } else {
                    showNotification('Error de conexión', 'error');
                }
            };

            xhr.onerror = function() {
                // Quitar clase de carga
                document.getElementById('edit-reservation-form').classList.remove('form-loading');
                showNotification('Error de conexión', 'error');
            };

            xhr.send(data);
        });
    </script>
    <script src="chat.js"></script>
</body>

</html>