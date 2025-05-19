<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Iniciar sesión
session_start();

// Verificar si el usuario está logueado pero no verificado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Si ya está verificado, redirigir a la página de reservación
if (isset($_SESSION['verificado']) && $_SESSION['verificado'] == 1) {
    header("Location: reservacion.php");
    exit();
}

$mensaje = '';
$tipo_mensaje = '';

// Procesar solicitud de reenvío de código
if (isset($_GET['reenviar']) && $_GET['reenviar'] == 'true') {
    $user_id = $_SESSION['user_id'];
    $user_email = $_SESSION['user_email'];
    $user_name = $_SESSION['user_name'];

    // Conectar a la base de datos
    $db_host = "localhost";
    $db_user = "root";
    $db_password = "";
    $db_name = "cuncunul";

    $conn = new mysqli($db_host, $db_user, $db_password, $db_name);

    if ($conn->connect_error) {
        $mensaje = "Error de conexión: " . $conn->connect_error;
        $tipo_mensaje = "error";
    } else {
        // Generar nuevo código
        $nuevo_codigo = mt_rand(100000, 999999);
        $nueva_expiracion = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $update = "UPDATE usuarios SET codigo_verificacion = '$nuevo_codigo', expiracion_codigo = '$nueva_expiracion' WHERE id = $user_id";
        if ($conn->query($update) === TRUE) {
            // Preparar contenido del correo en formato HTML
            $mensaje_email = "
            <html>
            <head>
                <title>Nuevo código de verificación - Cuncunul</title>
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
                        <h1>Restaurante Cuncunul</h1>
                    </div>
                    <div class='content'>
                        <p>Hola $user_name,</p>
                        <p>Has solicitado un nuevo código de verificación. Por favor utiliza el siguiente código:</p>
                        
                        <div class='code'>$nuevo_codigo</div>
                        
                        <p>Este código expirará en 24 horas.</p>
                    </div>
                    <div class='footer'>
                        <p>Este es un correo automático, por favor no responder.</p>
                        <p>&copy; " . date('Y') . " Restaurante Cuncunul. Todos los derechos reservados.</p>
                    </div>
                </div>
            </body>
            </html>
            ";

            // Cargar PHPMailer            
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
                $mail->addAddress($user_email, $user_name);

                // Contenido
                $mail->isHTML(true);
                $mail->Subject = 'Nuevo código de verificación - Restaurante Cuncunul';
                $mail->Body    = $mensaje_email;

                $mail->send();
                $mail_enviado = true;
            } catch (Exception $e) {
                error_log("Error al enviar correo: " . $mail->ErrorInfo);
                $mail_enviado = false;
            }

            if ($mail_enviado) {
                $mensaje = "Se ha enviado un nuevo código de verificación a tu correo electrónico.";
                $tipo_mensaje = "info";
            } else {
                // Fallback si no se puede enviar el correo
                $mensaje = "No se pudo enviar el correo. Tu nuevo código de verificación es: " . $nuevo_codigo;
                $tipo_mensaje = "info";
            }
        } else {
            $mensaje = "Error al generar nuevo código: " . $conn->error;
            $tipo_mensaje = "error";
        }

        $conn->close();
    }
}

// Procesar el formulario de verificación
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['codigo'])) {
    $codigo = $_POST['codigo'];
    $user_id = $_SESSION['user_id'];

    // Conectar a la base de datos
    $db_host = "localhost";
    $db_user = "root";
    $db_password = "";
    $db_name = "cuncunul";

    $conn = new mysqli($db_host, $db_user, $db_password, $db_name);

    if ($conn->connect_error) {
        $mensaje = "Error de conexión: " . $conn->connect_error;
        $tipo_mensaje = "error";
    } else {
        // Verificar el código
        $sql = "SELECT codigo_verificacion, expiracion_codigo FROM usuarios WHERE id = $user_id";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $codigo_db = $row['codigo_verificacion'];
            $expiracion = $row['expiracion_codigo'];

            // Verificar si el código es correcto y no ha expirado
            if ($codigo == $codigo_db && strtotime($expiracion) > time()) {
                // Actualizar el estado de verificación
                $update = "UPDATE usuarios SET verificado = 1 WHERE id = $user_id";
                if ($conn->query($update) === TRUE) {
                    $_SESSION['verificado'] = 1;
                    $mensaje = "Cuenta verificada con éxito";
                    $tipo_mensaje = "success";

                    // Redireccionar después de un breve retraso
                    header("Refresh: 2; URL=reservacion.php");
                } else {
                    $mensaje = "Error al verificar la cuenta: " . $conn->error;
                    $tipo_mensaje = "error";
                }
            } else if (strtotime($expiracion) <= time()) {
                $mensaje = "El código ha expirado. Por favor, solicita un nuevo código.";
                $tipo_mensaje = "error";
            } else {
                $mensaje = "Código de verificación incorrecto";
                $tipo_mensaje = "error";
            }
        } else {
            $mensaje = "Usuario no encontrado";
            $tipo_mensaje = "error";
        }

        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <title>Cuncunul - Verificación de Cuenta</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="resources/cero.ico" type="image/x-icon" />

    <!-- W3.CSS -->
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Kurale&family=Quattrocento+Sans&family=Raleway:wght@400&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="restaurantStyle.css">
    <link rel="stylesheet" href="loginStyle.css">
    <link rel="stylesheet" href="notification.css">
    <link rel="stylesheet" href="chat.css">
    <style>
        .verification-container {
            width: 90%;
            max-width: 450px;
            margin: 120px auto 60px;
            padding: 40px;
            background: rgba(126, 33, 8, 0.8);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(227, 186, 126, 0.3);
        }

        .mensaje-verificacion {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
            font-family: 'Raleway', sans-serif;
        }

        .mensaje-success {
            background-color: rgba(76, 175, 80, 0.2);
            border: 1px solid rgba(76, 175, 80, 0.5);
            color: #b9f6ca;
        }

        .mensaje-error {
            background-color: rgba(244, 67, 54, 0.2);
            border: 1px solid rgba(244, 67, 54, 0.5);
            color: #ffcdd2;
        }

        .mensaje-info {
            background-color: rgba(33, 150, 243, 0.2);
            border: 1px solid rgba(33, 150, 243, 0.5);
            color: #bbdefb;
        }

        .codigo-destacado {
            font-size: 1.3rem;
            color: #e3ba7e;
            font-weight: bold;
            background: rgba(255, 255, 255, 0.1);
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 5px;
        }
    </style>
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
            </div>
        </div>
    </div>

    <!-- Contenedor de Verificación -->
    <div class="verification-container">
        <div class="login-header">
            <img src="resources/cero.ico" alt="Logo" class="login-icon">
            <h2>Verificar Cuenta</h2>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="mensaje-verificacion mensaje-<?php echo $tipo_mensaje; ?>">
                <?php
                echo $mensaje;
                // Si es un mensaje que contiene código, destacarlo
                if (strpos($mensaje, 'código:') !== false || strpos($mensaje, 'código es:') !== false) {
                    $partes = strpos($mensaje, 'código:') !== false ?
                        explode('código:', $mensaje) :
                        explode('código es:', $mensaje);
                    echo '<div class="codigo-destacado">' . trim($partes[1]) . '</div>';
                }
                ?>
            </div>
        <?php else: ?>
            <div class="mensaje-verificacion mensaje-info">
                Para continuar con tu reservación, por favor ingresa el código de verificación que se ha enviado a tu correo electrónico.
            </div>
        <?php endif; ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="codigo">Código de Verificación</label>
                <input type="text" id="codigo" name="codigo" class="form-control" required placeholder="Ingresa el código de 6 dígitos" pattern="[0-9]{6}" maxlength="6">
                <p style="color: #e3ba7e; margin-top: 10px; font-size: 0.9rem;">
                    Ingresa el código de 6 dígitos que se envió a tu correo electrónico.
                </p>
            </div>

            <button type="submit" class="btn">Verificar</button>

            <div style="margin-top: 15px; text-align: center;">
                <a href="verificar.php?reenviar=true" style="color: #e3ba7e; text-decoration: none; font-size: 0.9rem;">
                    ¿No recibiste el código? Solicitar uno nuevo
                </a>
            </div>

            <div style="margin-top: 20px; text-align: center;">
                <a href="logout.php" style="color: white; text-decoration: none; opacity: 0.7;">
                    Cancelar y volver al inicio
                </a>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <footer>
        <img src="resources/cero.ico" alt="Logo Cuncunul" class="header-logo">
    </footer>

    <script src="chat.js"></script>
</body>

</html>