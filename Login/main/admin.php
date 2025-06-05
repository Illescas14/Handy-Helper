<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f8f8;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        h1, h2 {
            text-align: center;
            color: #006f6f;
        }

        .container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
            margin: 20px 0;
        }

        .table-container {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            padding: 10px;
            background-color: #a0ece5;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #006f6f;
        }

        th {
            background-color: #006f6f;
            color: #fff;
            padding: 10px;
            text-align: center;
        }

        td {
            padding: 8px;
            text-align: center;
            background-color: #e6f9f9;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #006f6f;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }

        .btn:hover {
            background-color: #004d4d;
        }

    </style>
</head>
<body>

    <h1>Panel de Administración</h1>

    <div class="container">

        <!-- Tabla de Trabajadores (Profile) -->
        <div class="table-container">
            <h2>Trabajadores</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Especialidad</th>
                        <th>Experiencia</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- PHP para traer los datos de la tabla profile -->
                    <?php
                    // Conectar a la base de datos
                    $conn = new mysqli("localhost", "root", "", "handyhelpero");

                    if ($conn->connect_error) {
                        die("Conexión fallida: " . $conn->connect_error);
                    }

                    // Consultar la tabla profile
                    $sql = "SELECT profiles.id, users.name , profiles.job_title, profiles.experience FROM profiles join users on profiles.user_id= users.id";
                    
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$row['id']}</td>
                                    <td>{$row['name']}</td>
                                    <td>{$row['job_title']}</td>
                                    <td>{$row['experience']}</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No se encontraron trabajadores</td></tr>";
                    }

                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Tabla de Contratos (Contracts) -->
        <div class="table-container">
            <h2>Contratos</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID Contrato</th>
                        <th>ID Cliente</th>
                        <th>ID Trabajador</th>
                        <th> Tarea</th>
                        <th>Estado de monto</th>
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
                    $sql = "SELECT id, requester_id, worker_id, status, amount, start_date, end_date, payment_status FROM contracts";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$row['id']}</td>
                                    <td>{$row['requester_id']}</td>
                                    <td>{$row['worker_id']}</td>
                                    <td>{$row['status']}</td>
                                    <td>{$row['amount']}</td>
                                    <td>{$row['start_date']}</td>
                                    <td>{$row['end_date']}</td>
                                    <td>{$row['payment_status']}</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No se encontraron contratos</td></tr>";
                    }

                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Tabla de Usuarios (Users) -->
        <div class="table-container">
            <h2>Usuarios</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID Usuario</th>
                        <th>Nombre</th>
                        <th>Correo Electrónico</th>
                        <th>Rol</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- PHP para traer los datos de la tabla users -->
                    <?php
                    // Conectar a la base de datos
                    $conn = new mysqli("localhost", "root", "", "handyhelpero");

                    if ($conn->connect_error) {
                        die("Conexión fallida: " . $conn->connect_error);
                    }

                    // Consultar la tabla users
                    $sql = "SELECT id, name, email, role FROM users";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$row['id']}</td>
                                    <td>{$row['name']}</td>
                                    <td>{$row['email']}</td>
                                    <td>{$row['role']}</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>No se encontraron usuarios</td></tr>";
                    }

                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>
