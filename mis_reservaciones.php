<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Obtener información del usuario
$user_id = $_SESSION['user_id'];
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

// Obtener las reservaciones del usuario
$sql = "SELECT * FROM reservaciones WHERE usuario_id = $user_id ORDER BY fecha DESC, hora ASC";
$result = $conn->query($sql);

// Preparar array para almacenar las reservaciones
$reservaciones = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $reservaciones[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <title>Cuncunul - Mis Reservaciones</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="resources/cero.ico" type="image/x-icon" />

    <!-- W3.CSS -->
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Kurale&family=Quattrocento+Sans&family=Raleway:wght@400&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="restaurantStyle.css">
    <link rel="stylesheet" href="notification.css">
    <link rel="stylesheet" href="chat.css">
    <style>
        .mis-reservaciones-container {
            width: 90%;
            max-width: 1000px;
            margin: 120px auto 60px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .mis-reservaciones-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .mis-reservaciones-header h2 {
            font-size: 2.2rem;
            font-family: 'Kurale', serif;
            color: white;
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }

        .mis-reservaciones-header h2:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: #e3ba7e;
        }

        .welcome-message {
            text-align: center;
            margin-bottom: 30px;
            font-family: 'Raleway', sans-serif;
            color: white;
        }

        .reservacion-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            border-left: 4px solid #e3ba7e;
        }

        .reservacion-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
        }

        .reservacion-fecha {
            font-family: 'Kurale', serif;
            font-size: 1.4rem;
            color: #e3ba7e;
            margin-bottom: 10px;
        }

        .reservacion-detalles {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .reservacion-dato {
            flex: 1;
            min-width: 200px;
        }

        .reservacion-dato strong {
            display: block;
            margin-bottom: 5px;
            color: white;
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        .reservacion-dato span {
            font-size: 1.1rem;
            color: white;
        }

        .reservacion-acciones {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }

        .btn-accion {
            padding: 8px 15px;
            border: 1px solid white;
            background: transparent;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Raleway', sans-serif;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-editar {
            border-color: #e3ba7e;
            color: #e3ba7e;
        }

        .btn-cancelar {
            border-color: #ff6b6b;
            color: #ff6b6b;
        }

        .btn-accion:hover {
            background: white;
            color: #7e2108;
        }

        .btn-editar:hover {
            background: #e3ba7e;
            color: white;
        }

        .btn-cancelar:hover {
            background: #ff6b6b;
            color: white;
        }

        .no-reservaciones {
            text-align: center;
            padding: 30px;
            font-family: 'Raleway', sans-serif;
            color: white;
            font-size: 1.2rem;
        }

        .btn-nueva-reservacion {
            display: block;
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
            padding: 12px;
            background: transparent;
            color: white;
            border: 2px solid white;
            border-radius: 5px;
            text-align: center;
            font-family: 'Raleway', sans-serif;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-nueva-reservacion:hover {
            background: white;
            color: #7e2108;
        }

        @media screen and (max-width: 768px) {
            .reservacion-detalles {
                flex-direction: column;
                gap: 10px;
            }

            .reservacion-dato {
                min-width: 100%;
            }
        }
    </style>

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

    <!-- Contenedor de Mis Reservaciones -->
    <div class="mis-reservaciones-container">
        <div class="mis-reservaciones-header">
            <h2>Mis Reservaciones</h2>
        </div>

        <div class="welcome-message">
            <p>Bienvenido, <?php echo htmlspecialchars($user_name); ?>. Aquí puedes ver y administrar tus reservaciones.</p>
        </div>

        <?php if (count($reservaciones) > 0): ?>
            <?php foreach ($reservaciones as $reserva): ?>
                <div class="reservacion-card" data-id="<?php echo $reserva['id']; ?>">
                    <div class="reservacion-fecha">
                        <?php
                        $fecha = new DateTime($reserva['fecha']);
                        echo $fecha->format('d/m/Y');
                        ?>
                    </div>
                    <div class="reservacion-detalles">
                        <div class="reservacion-dato">
                            <strong>Hora</strong>
                            <span><?php echo htmlspecialchars($reserva['hora']); ?></span>
                        </div>
                        <div class="reservacion-dato">
                            <strong>Personas</strong>
                            <span><?php echo htmlspecialchars($reserva['num_personas']); ?></span>
                        </div>
                        <?php if (!empty($reserva['comentarios'])): ?>
                            <div class="reservacion-dato">
                                <strong>Comentarios</strong>
                                <span><?php echo htmlspecialchars($reserva['comentarios']); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="reservacion-dato">
                            <strong>Fecha de Reservación</strong>
                            <span>
                                <?php
                                $fecha_creacion = new DateTime($reserva['fecha_creacion']);
                                echo $fecha_creacion->format('d/m/Y H:i');
                                ?>
                            </span>
                        </div>
                    </div>
                    <div class="reservacion-acciones">
                        <button class="btn-accion btn-editar" onclick="editarReservacion(<?php echo $reserva['id']; ?>)">Editar</button>
                        <button class="btn-accion btn-cancelar" onclick="cancelarReservacion(<?php echo $reserva['id']; ?>)">Cancelar</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-reservaciones">
                <p>No tienes reservaciones activas.</p>
            </div>
        <?php endif; ?>

        <a href="reservacion.php" class="btn-nueva-reservacion">Nueva Reservación</a>
    </div>

    <!-- Footer -->
    <footer>
        <img src="resources/cero.ico" alt="Logo Cuncunul" class="header-logo">
    </footer>

    <script>
        // Función para editar una reservación
        function editarReservacion(reservacionId) {
            window.location.href = `editar_reservacion.php?id=${reservacionId}`;
        }

        // Función para cancelar una reservación
        function cancelarReservacion(reservacionId) {
            if (confirm('¿Estás seguro de que deseas cancelar esta reservación?')) {
                // Realizar petición AJAX para cancelar
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'ajax_cancelar_reservacion.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            const response = JSON.parse(xhr.responseText);

                            if (response.success) {
                                showNotification(response.message, 'success');
                                // Eliminar la tarjeta de reservación del DOM
                                const reservacionCard = document.querySelector(`.reservacion-card[data-id="${reservacionId}"]`);
                                if (reservacionCard) {
                                    reservacionCard.remove();
                                }

                                // Si no quedan reservaciones, mostrar mensaje
                                const tarjetas = document.querySelectorAll('.reservacion-card');
                                if (tarjetas.length === 0) {
                                    const contenedor = document.querySelector('.mis-reservaciones-container');
                                    const noReservaciones = document.createElement('div');
                                    noReservaciones.className = 'no-reservaciones';
                                    noReservaciones.innerHTML = '<p>No tienes reservaciones activas.</p>';

                                    // Insertar antes del botón de nueva reservación
                                    const btnNueva = document.querySelector('.btn-nueva-reservacion');
                                    contenedor.insertBefore(noReservaciones, btnNueva);
                                }
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
                    showNotification('Error de conexión', 'error');
                };

                xhr.send(`id=${reservacionId}`);
            }
        }
    </script>
    <script src="chat.js"></script>
</body>

</html>