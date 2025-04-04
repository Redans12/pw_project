<?php
session_start();

// Verificar si hay datos de reserva y usuario logueado
if (!isset($_SESSION['user_id']) || !isset($_SESSION['reserva'])) {
    header("Location: login.html");
    exit();
}

// Obtener datos de la reserva
$reserva = $_SESSION['reserva'];
$usuario = $_SESSION['user_name'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de tu Reservación - Cuncunul</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link href="https://fonts.googleapis.com/css2?family=Kurale&family=Raleway:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Raleway', sans-serif;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('images/background.jpg') no-repeat center center fixed;
            background-size: cover;
            color: white;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        
        .resumen-container {
            width: 90%;
            max-width: 800px;
            margin: 120px auto 60px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }
        
        .resumen-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .resumen-header h2 {
            font-size: 2.2rem;
            font-family: 'Kurale', serif;
            color: white;
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }
        
        .resumen-header h2:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: #e3ba7e;
        }
        
        .resumen-datos {
            margin-bottom: 30px;
        }
        
        .resumen-datos h3 {
            font-family: 'Kurale', serif;
            font-size: 1.5rem;
            color: #e3ba7e;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .resumen-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .resumen-item strong {
            color: #e3ba7e;
        }
        
        .btn-logout {
            width: 100%;
            padding: 12px;
            background: transparent;
            color: white;
            border: 2px solid white;
            border-radius: 5px;
            font-family: 'Raleway', sans-serif;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        
        .btn-logout:hover {
            background: white;
            color: #7e2108;
        }
        
        .welcome-message {
            font-size: 1.1rem;
            margin-bottom: 30px;
            text-align: center;
        }
        
        @media screen and (max-width: 768px) {
            .resumen-container {
                margin: 80px auto 40px;
                padding: 20px;
            }
            
            .resumen-header h2 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="resumen-container">
        <div class="resumen-header">
            <h2>¡Reservación Confirmada!</h2>
            <p class="welcome-message">Gracias <?php echo htmlspecialchars($usuario); ?>, tu reservación ha sido confirmada.</p>
        </div>
        
        <div class="resumen-datos">
            <h3>Detalles de tu reservación</h3>
            
            <div class="resumen-item">
                <strong>Fecha:</strong> <?php echo htmlspecialchars($reserva['fecha']); ?>
            </div>
            
            <div class="resumen-item">
                <strong>Hora:</strong> <?php echo htmlspecialchars($reserva['hora']); ?>
            </div>
            
            <div class="resumen-item">
                <strong>Número de personas:</strong> <?php echo htmlspecialchars($reserva['personas']); ?>
            </div>
            
            <?php if (!empty($reserva['comentarios'])): ?>
            <div class="resumen-item">
                <strong>Tus comentarios:</strong> <?php echo htmlspecialchars($reserva['comentarios']); ?>
            </div>
            <?php endif; ?>
        </div>
        
        <form action="logout.php" method="post">
            <button type="submit" class="btn-logout">Cerrar sesión</button>
        </form>
    </div>
</body>
</html>