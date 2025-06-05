<?php
session_start();
include '../db.php'; // Asegúrate de que este archivo conecta correctamente a tu base de datos

$profile_name = isset($_SESSION['profile_name']) ? $_SESSION['profile_name'] : 'Administrador';
$current_datetime = date("l, d F Y H:i:s", time());
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Trabajadores</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #007BFF;
            padding: 20px;
            text-align: center;
            color: white;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
        }
        .table-container {
            width: 80%;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #007BFF;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .back-button {
            text-decoration: none;
            color: #007BFF;
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .back-button svg {
            margin-right: 10px;
        }

        
        /* Footer */
        footer {
            background-color: #0288d1;
            color: white;
            padding: 10px 0;
            text-align: center;
            width: 100%;
            margin-top: auto;
        }
        /* Consultas de medios para responsividad */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                align-items: center;
            }

            .table-container {
                width: 90%;
            }
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 20px;
            }
        }

        /* Estilos para los botones */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            color: blue;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }

        /* Estilo para el botón de eliminar */
        .btn-delete {
            background-color: #FF5733; /* Rojo */
            border: 2px solid #CC4626; /* Rojo oscuro */
        }

        .btn-delete:hover {
            background-color: #CC4626;
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.3);
            transform: translateY(-2px);
        }

        .btn-delete:active {
            background-color: #FF5733;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            transform: translateY(0);
        }

        /* Estilo para el botón de actualizar */
        .btn-update {
            background-color: #007BFF; /* Azul */
            border: 2px solid #0056b3; /* Azul oscuro */
        }

        .btn-update:hover {
            background-color: #0056b3;
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.3);
            transform: translateY(-2px);
        }

        .btn-update:active {
            background-color: #007BFF;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            transform: translateY(0);}



    </style>
</head>
<body>

<!-- Cabecera -->
<div class="header">
    <h1>Panel de Administración de Handy Helper</h1>
    <p>Gestiona los trabajadores y sus perfiles</p>
</div>

<div class="container">
    <!-- Botón de regreso alineado a la izquierda -->
    <div style="align-self: flex-start; margin-bottom: 20px;">
            <a href="../Firstadmin.php" class="back-button">
                <!-- Flecha azul -->
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M15 8a.5.5 0 0 1-.5.5H2.707l3.147 3.146a.5.5 0 0 1-.708.708l-4-4a.5.5 0 0 1 0-.708l4-4a.5.5 0 0 1 .708.708L2.707 7.5H14.5A.5.5 0 0 1 15 8z"/>
                </svg>
                Volver
            </a>
        </div>
    <!-- Tabla de Trabajadores -->
    <div class="table-container">
        <h2>Trabajadores</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Especialidad</th>
                    <th>Experiencia</th>
                    <th>Acciones</th> <!-- Nueva columna para acciones -->
                </tr>
            </thead>
            <tbody>
                <!-- PHP para traer los datos de la tabla profiles -->
                <?php
                $conn = new mysqli("localhost", "root", "", "handyhelpero");

                if ($conn->connect_error) {
                    die("Conexión fallida: " . $conn->connect_error);
                }

                $sql = "SELECT profiles.id, users.name, profiles.job_title, profiles.experience 
                        FROM profiles 
                        JOIN users ON profiles.user_id = users.id";

                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['name']}</td>
                                <td>{$row['job_title']}</td>
                                <td>{$row['experience']}</td>
                                <td>
                                    <a href='updateT.php?id={$row['id']}' class='btn'>Actualizar</a>
                                    <a href='deleteT.php?id={$row['id']}' class='btn' onclick=\"return confirm('¿Estás seguro de que deseas eliminar este trabajador?');\">Eliminar</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No se encontraron trabajadores</td></tr>";
                }

                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Botón de regreso -->
<div style="text-align: center; margin-top: 20px;">
    <a href="../Firstadmin.php" class="back-button">
        <!-- Flecha azul -->
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 1-.5.5H2.707l3.147 3.146a.5.5 0 0 1-.708.708l-4-4a.5.5 0 0 1 0-.708l4-4a.5.5 0 0 1 .708.708L2.707 7.5H14.5A.5.5 0 0 1 15 8z"/>
        </svg>
        Volver a la pantalla anterior
    </a>
</div>

<!-- Footer -->
<footer>
    &copy; 2024 Handy Helper - Todos los derechos reservados.
</footer>

</body>
</html>
