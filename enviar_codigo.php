<?php
// Habilitar visualización de errores para debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cargar PHPMailer
if (file_exists('vendor/autoload.php')) {
    // Si usas Composer
    require 'vendor/autoload.php';
} else {
    // Si descargaste PHPMailer manualmente
    require 'phpmailer/src/Exception.php';
    require 'phpmailer/src/PHPMailer.php';
    require 'phpmailer/src/SMTP.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Cargar configuración
$config = require 'config.php';

/**
 * Envía un código de verificación por email usando PHPMailer
 *
 * @param string $email Email del destinatario
 * @param string $nombre Nombre del destinatario
 * @param string $codigo Código de verificación
 * @return bool True si se envió correctamente, False en caso contrario
 */
function enviarCodigoPorEmail($email, $nombre, $codigo, $config) {
    // Crear nueva instancia de PHPMailer
    $mail = new PHPMailer(true); // true habilita excepciones
    
    try {
        // Configuración del servidor
        $mail->isSMTP();                                      // Usar SMTP
        $mail->Host       = $config['email']['host'];         // Servidor SMTP
        $mail->SMTPAuth   = true;                             // Habilitar autenticación SMTP
        $mail->Username   = $config['email']['username'];     // Usuario SMTP
        $mail->Password   = $config['email']['password'];     // Contraseña SMTP
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   // Habilitar TLS
        $mail->Port       = $config['email']['port'];         // Puerto TCP
        $mail->CharSet    = 'UTF-8';                          // Codificación para caracteres especiales
        
        // Remitente y destinatario
        $mail->setFrom($config['email']['from_email'], $config['email']['from_name']);
        $mail->addAddress($email, $nombre);
        
        // Contenido del email
        $mail->isHTML(true);                                  // Enviar como HTML
        $mail->Subject = 'Tu código de verificación para Cuncunul';
        $mail->Body    = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <h2 style="color: #7e2108;">Restaurante Cuncunul</h2>
                </div>
                <p>Hola <strong>' . htmlspecialchars($nombre) . '</strong>,</p>
                <p>Gracias por registrarte en nuestro sistema de reservaciones. Para completar tu registro, por favor utiliza el siguiente código de verificación:</p>
                <div style="text-align: center; margin: 30px 0;">
                    <div style="font-size: 24px; letter-spacing: 5px; font-weight: bold; padding: 15px; background-color: #f5f5f5; border-radius: 5px; display: inline-block;">
                        ' . $codigo . '
                    </div>
                </div>
                <p>Este código es válido por 30 minutos. Si no has solicitado esta verificación, por favor ignora este correo.</p>
                <p>Saludos,<br>El equipo de Cuncunul</p>
            </div>
        ';
        // Versión de texto plano del email
        $mail->AltBody = "Hola {$nombre}, tu código de verificación para Cuncunul es: {$codigo}. Este código es válido por 30 minutos.";
        
        // Enviar el email
        $mail->send();
        error_log("Email enviado con éxito a {$email}");
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar email: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Simula el envío de un código de verificación (para desarrollo)
 */
function simularEnvioCodigoPorEmail($email, $nombre, $codigo) {
    // Asegurarse que existe la carpeta logs
    if (!file_exists('logs')) {
        mkdir('logs', 0755);
    }
    
    $fecha = date('Y-m-d H:i:s');
    $registro = "[$fecha] Código para $nombre (Email: $email): $codigo\n";
    
    // Guardar en un archivo de texto
    file_put_contents('logs/codigos_enviados.log', $registro, FILE_APPEND);
    
    // También mostrar en el log de errores de PHP para debugging
    error_log("CÓDIGO SIMULADO: $codigo para usuario $nombre ($email)");
    
    return true;
}

/**
 * Función principal para enviar un código de verificación
 */
function enviarCodigo($email, $nombre, $codigo, $config = null) {
    // Si no se pasó configuración, cargar la predeterminada
    if ($config === null) {
        global $config;
    }
    
    // Decidir si enviar realmente o simular
    if ($config['dev_mode']) {
        // En modo desarrollo, simular el envío
        return simularEnvioCodigoPorEmail($email, $nombre, $codigo);
    } else {
        // En producción, enviar email real
        return enviarCodigoPorEmail($email, $nombre, $codigo, $config);
    }
}

// Función para probar el envío de códigos (útil para debugging)
function testEnvioCodigo() {
    global $config;
    
    $email = 'tu_email_de_prueba@gmail.com'; // Cambia esto
    $nombre = 'Usuario de Prueba';
    $codigo = rand(100000, 999999);
    
    echo "Probando envío de código...<br>";
    
    if ($config['dev_mode']) {
        echo "Modo desarrollo: Simulando envío.<br>";
    } else {
        echo "Modo producción: Enviando email real.<br>";
    }
    
    $resultado = enviarCodigo($email, $nombre, $codigo);
    
    if ($resultado) {
        echo "<strong style='color:green'>¡Éxito!</strong> El código fue procesado correctamente.<br>";
        if ($config['dev_mode']) {
            echo "Código simulado: $codigo<br>";
            echo "Ver todos los códigos: <a href='ver_codigos.php'>ver_codigos.php</a>";
        } else {
            echo "El email ha sido enviado a $email.<br>";
        }
    } else {
        echo "<strong style='color:red'>Error.</strong> No se pudo procesar el código.<br>";
        echo "Revisa los logs de PHP para más detalles.";
    }
}

// Si este archivo se llama directamente, ejecutar la prueba
if (basename($_SERVER['SCRIPT_NAME']) === basename(__FILE__)) {
    // Solo ejecutar si se accede directamente a este archivo
    testEnvioCodigo();
}