<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Verificar si el usuario ha verificado su cuenta
if (!isset($_SESSION['verificado']) || $_SESSION['verificado'] != 1) {
    header("Location: verificar.php");
    exit();
}

// Obtener información del usuario
$user_name = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <title>Cuncunul - Reservación</title>
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

    <!-- Contenedor de Reservación -->
    <div class="reservation-container">
        <div class="reservation-header">
            <h2>Reservación</h2>
        </div>

        <div class="welcome-message">
            <p>Bienvenido, <?php echo htmlspecialchars($user_name); ?>. Por favor, complete los detalles de su reservación.</p>
        </div>

        <form id="reservation-form">
            <div class="form-row">
                <div class="form-column">
                    <div class="form-group">
                        <label for="fecha">Fecha</label>
                        <input type="date" id="fecha" name="fecha" class="form-control" required>
                    </div>
                </div>
                <div class="form-column">
                    <div class="form-group">
                        <label for="hora">Hora</label>
                        <input type="time" id="hora" name="hora" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-column">
                    <div class="form-group">
                        <label for="personas">Número de personas</label>
                        <select id="personas" name="personas" class="form-control" required>
                            <option value="">Seleccionar</option>
                            <?php for ($i = 1; $i <= 12; $i++) : ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="form-column">
                    <div class="form-group">
                        <label for="telefono">Teléfono de contacto</label>
                        <input type="tel" id="telefono" name="telefono" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="comentarios">Comentarios o solicitudes especiales</label>
                <textarea id="comentarios" name="comentarios" class="form-control" rows="4"></textarea>
            </div>

            <button type="submit" class="btn">Confirmar Reservación</button>
        </form>

        <div class="logout-link">
            <a href="logout.php">Cerrar Sesión</a>
        </div>
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

        // Manejar envío del formulario de reservación con AJAX
        document.getElementById('reservation-form').addEventListener('submit', function(e) {
            e.preventDefault();

            // Agregar clase de carga
            this.classList.add('form-loading');

            const fecha = document.getElementById('fecha').value;
            const hora = document.getElementById('hora').value;
            const personas = document.getElementById('personas').value;
            const telefono = document.getElementById('telefono').value;
            const comentarios = document.getElementById('comentarios').value;

            // Función para manejar la respuesta exitosa
            function reservationSuccess(response) {
                // Quitar clase de carga
                document.getElementById('reservation-form').classList.remove('form-loading');

                if (response.success) {
                    showNotification(response.message, 'success');
                    // Redireccionar después de un breve retraso
                    setTimeout(function() {
                        window.location.href = response.redirect;
                    }, 1000);
                } else {
                    showNotification(response.message, 'error');
                }
            }

            // Función para manejar errores
            function reservationError(errorMsg) {
                // Quitar clase de carga
                document.getElementById('reservation-form').classList.remove('form-loading');
                showNotification('Error: ' + errorMsg, 'error');
            }

            // Realizar petición AJAX
            processReservation(fecha, hora, personas, telefono, comentarios, reservationSuccess, reservationError);
        });

        // Agregar el header X-Requested-With a todas las solicitudes XHR para identificarlas como AJAX
        (function() {
            const oldXHROpen = XMLHttpRequest.prototype.open;
            XMLHttpRequest.prototype.open = function() {
                oldXHROpen.apply(this, arguments);
                this.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            };
        })();
    </script>
    <script src="chat.js"></script>
</body>

</html>