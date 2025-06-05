<?php
session_start();
include 'db.php'; // Asegúrate de que este archivo conecta correctamente a tu base de datos


// Suponiendo que el nombre del perfil del administrador está guardado en la sesión
// Puedes modificar este valor dependiendo de cómo se maneje el inicio de sesión
$profile_name = isset($_SESSION['profile_name']) ? $_SESSION['profile_name'] : 'Administrador';
//$user_id = isset($_SESSION['user_id']) ? $user_id = $_SESSION['user_id'] : 'Administrador';

// Obtener la fecha y hora actual en el formato deseado
//$current_datetime = date("l, d F Y H:i:s", time()); // Ejemplo: lunes, 03 septiembre 2024 14:33:59
// Configurar la zona horaria para Ciudad de México
date_default_timezone_set('America/Mexico_City');

// Obtener la fecha y hora actual en el formato deseado
$current_datetime = date("l, d F Y H:i:s");

// Mostrar la fecha y hora actual
//echo "Fecha y hora actual: " . $current_datetime;

?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #e0f7fa;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Estilo del contenedor principal */
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }

        /* Cabecera */
        header {
            background-color: #0288d1;
            color: white;
            padding: 20px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        header img {
            height: 100px;
    width: 100px; /* Asegúrate de que el logo sea cuadrado */
    border-radius: 50%; /* Hace el logo circular */
    margin-right: 20px; /* Espacio entre el logo y el texto */
    position: absolute;
    top: 50px;
    left: 55px;
    animation: spin 4s linear infinite; /* Animación de rotación */
}

@keyframes spin {
            from {
                transform: rotateY(0deg);
            }
            to {
                transform: rotateY(360deg);
            }
        }



        header h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }

        .welcome-message {
            margin: 10px 0;
            font-size: 18px;
        }

        .date-time {
            font-size: 14px;
            margin-bottom: 10px;
        }

        /* Botón de cerrar sesión */
        .logout-button {
            position: absolute;
            right: 20px;
            top: 25px;
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-transform: uppercase;
        }

        .logout-button:hover {
            background-color: #c62828;
        }

        /* Botones de navegación */
        .nav-buttons {
            margin: 30px 0;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }

        .nav-buttons button {
            background-color: #4fc3f7;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 18px;
            color: white;
            cursor: pointer;
            margin: 10px;
            width: 200px;
            text-transform: uppercase;
            transition: 0.3s;
        }

        .nav-buttons button:hover {
            background-color: #039be5;
        }

        /* Footer */
        footer {
          
            background-color: #0288d1;
            color: white;
            padding: 10px 0;
            text-align: center;
            width: 100%;
            /*position: relative;
            bottom: 0;*/
            margin-top: auto;
        }
         /* Consultas de medios para responsividad */
         @media (max-width: 768px) {
            header {
                flex-direction: column;
                text-align: center;
            }

            header img {
                margin-right: 0;
                margin-bottom: 10px;
            }

            .nav-buttons {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            header h1 {
                font-size: 20px;
            }

            .welcome-message {
                font-size: 16px;
            }

            .date-time {
                font-size: 12px;
            }

            .nav-buttons button {
                font-size: 16px;
                padding: 12px;
                max-width: 100%;
            }
        }

    </style>
</head>
<body>

    <!-- Cabecera con logo, mensaje de bienvenida y cerrar sesión -->
    <header>

        <img src="../../assets/imagenes/logo.jpeg" alt="Logo" class="logo">
    
        <div>
        <h1>Panel de Administración</h1>
        <div class="welcome-message">Bienvenido, <?php echo htmlspecialchars($profile_name); ?></div>
        <div class="date-time"><?php echo $current_datetime; ?></div>
    </div>
        <form action="../index.html" method="POST">
            <button type="submit" class="logout-button">Cerrar Sesión</button>
        </form>
    </header>

    <!-- Contenedor principal -->
    <div class="container">
        <!-- Botones de navegación -->
        <div class="nav-buttons">
            <button onclick="location.href='administrador/usuario.php'">Usuarios</button>
            <button onclick="location.href='administrador/trabajadores.php'">Trabajadores</button>
            <button onclick="location.href='administrador/contratos.php'">Contratos</button>
            
        </div>
    </div>

    <!-- Footer -->
    <footer>
        &copy; 2024 Handy Helper - Todos los derechos reservados.
    </footer>

</body>
</html>
