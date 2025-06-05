<?php
session_start();
include '../db.php'; // Asegúrate de que este archivo conecta correctamente a tu base de datos

// Suponiendo que el nombre del perfil del administrador está guardado en la sesión
$profile_name = isset($_SESSION['profile_name']) ? $_SESSION['profile_name'] : 'Administrador';

// Obtener la fecha y hora actual en el formato deseado
$current_datetime = date("l, d F Y H:i:s", time()); // Ejemplo: lunes, 03 septiembre 2024 14:33:59
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Contratos</title>
    <style>
     html, body {
    height: 100%;
    margin: 0;
}

body {
    font-family: Arial, sans-serif;
    background-color: #f0f0f0;
    display: flex;
    flex-direction: column;
}

.header {
    background-color: #007BFF;
    padding: 20px;
    text-align: center;
    color: white;
}

.header h1 {
    margin: 0;
    font-size: 2rem; /* Tamaño relativo */
}

.container {
    flex: 1;
    display: flex;
    flex-direction: column; 
    align-items: center; 
    padding: 20px;
    width: 100%; 
}

.table-container {
    width: 100%; 
    max-width: 1200px; 
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    overflow-x: auto; 
    margin-top: 20px; 
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
    padding: 0.8rem; /* Usar rem para mejor escalabilidad */
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
    align-self: flex-start; /* Alinea el botón a la izquierda */
    margin-bottom: 20px; 
}

/* Footer */
footer {
    background-color: #0288d1;
    color: white;
    padding: 10px 0;
    text-align: center;
    margin-top: auto; 
}

/* Consultas de medios para responsividad */
@media (max-width: 1200px) {
    .header h1 {
        font-size: 1.8rem; /* Tamaño más pequeño en pantallas medianas */
    }
}

@media (max-width: 768px) {
    .container {
        align-items: center; 
    }

    .table-container {
        width: 90%; 
        margin-right: 0;
    }

    h2 {
        font-size: 1.5rem; /* Ajustar tamaño de título */
    }

    th, td {
        padding: 0.5rem; /* Reducir padding en celdas */
    }
}

@media (max-width: 480px) {
    .header h1 {
        font-size: 1.5rem; /* Ajustar tamaño del título en pantallas pequeñas */
    }

    th, td {
        padding: 0.4rem; /* Reducir padding en celdas */
    }

    h2 {
        font-size: 1.2rem; /* Ajustar tamaño del título aún más */
    }

    .back-button {
        font-size: 0.9rem; /* Ajustar tamaño del botón */
    }
}

    </style>
</head>
<body>

    <!-- Cabecera -->
    <div class="header">
        <h1>Panel de Administración de Handy Helper</h1>
        <p>Gestiona los contratos y perfiles</p>
    </div>

    <div class="container">
        <!-- Botón de regreso alineado a la izquierda -->
        <div style="align-self: flex-start; margin-bottom: 20px;">
            <a href="../Firstadmin.php" class="back-button">
                <!-- Flecha azul -->
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M15 8a.5.5 0 0 1-.5.5H2.707l3.147 3.146a.5.5 0 0 1-.708.708l-4-4a.5.5 0 0 1 0-.708l4-4a.5.5 0 0 1 .708.708L2.707 7.5H14.5A.5.5 0 0 1 15 8z"/>
                </svg>
                Volver a la pantalla anterior
            </a>
        </div>

        <!-- Tabla de Contratos -->
        <div class="table-container">
            <h2>Contratos</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID Contrato</th>
                        <th>ID Cliente</th>
                        <th>ID Trabajador</th>
                        <th>Tarea</th>
                        <th>Estado</th>
                        <th>Monto</th>
                        <th>Fecha de Inicio</th>
                        <th>Fecha de Fin</th>
                        <th>Estado de pago</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- PHP para traer los datos de la tabla contracts -->
                    <?php
                    // Conectar a la base de datos
                    $conn = new mysqli("localhost", "root", "", "handyhelpero");

                    if ($conn->connect_error) {
                        die("Conexión fallida: " . $conn->connect_error);
                    }

                    // Consultar la tabla contracts
                    $sql = "SELECT id, requester_id, worker_id, task_id, status, amount, start_date, end_date, payment_status FROM contracts";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$row['id']}</td>
                                    <td>{$row['requester_id']}</td>
                                    <td>{$row['worker_id']}</td>
                                    <td>{$row['task_id']}</td>
                                    <td>{$row['status']}</td>
                                    <td>{$row['amount']}</td>
                                    <td>{$row['start_date']}</td>
                                    <td>{$row['end_date']}</td>
                                    <td>{$row['payment_status']}</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8'>No se encontraron contratos</td></tr>";
                    }

                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        &copy; 2024 Handy Helper - Todos los derechos reservados.
    </footer>

</body>

</html>
