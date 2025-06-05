<?php
session_start();
include '../db.php';

// Verifica si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$task_id = $_GET['task_id'];
$worker_id = $_GET['worker_id'];

// Consultar la tarea, el precio y el nombre del trabajador
$query = "SELECT t.task_name, t.price, u.name AS worker_name FROM tasks t 
          JOIN profiles p ON t.profile_id = p.id 
          JOIN users u ON p.user_id = u.id 
          WHERE t.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $task_id);
$stmt->execute();
$task = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Obtener el nombre del cliente
$query = "SELECT name FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Procesar la solicitud cuando el formulario es enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_date = $_POST['request_date'];
    $card_number = $_POST['card_number'];
    $expiry_date = $_POST['expiry_date'];
    $cvv = $_POST['cvv'];
    $amount = $task['price'];

    // Insertar la solicitud en la tabla 'requests'
    $stmt = $conn->prepare("INSERT INTO requests (task_id, worker_id, client_id, request_date, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iiis", $task_id, $worker_id, $user_id, $request_date);
    $stmt->execute();
    $request_id = $stmt->insert_id; // Obtener el ID de la solicitud recién creada
    $stmt->close();

    // Crear el contenido de la notificación con el nombre del cliente
    $content = "Nueva solicitud de " . $client['name'] . " para la tarea {$task['task_name']} el día {$request_date}.";
    
    // Insertar la notificación para el trabajador
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, content, sender_id, request_id) VALUES (?, 'task_request', ?, ?, ?)");
    $stmt->bind_param("isii", $worker_id, $content, $user_id, $request_id);
    $stmt->execute();
    $stmt->close();

    // Redirigir a una página de confirmación o notificaciones
    header("Location: notificaciones.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contratar Tarea</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #b6faf4e4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            margin: auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #48dfd0;
            text-align: center;
        }
        p {
            margin: 10px 0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-group button {
            width: 100%;
            padding: 10px;
            background-color: #48dfd0; /* Color principal */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .form-group button:hover {
            background-color: #36c1b0; /* Color más oscuro al pasar el ratón */
        }
        .terms-container {
            display: flex;
            align-items: center;
            margin: 15px 0;
        }
        .terms-container input {
            margin-right: 10px;
        }
        .back-button {
            background-color: #ff6b6b; /* Color para el botón de volver */
            margin-right: 5px;
        }
        .back-button:hover {
            background-color: #ff4c4c; /* Color más oscuro al pasar el ratón */
        }
        @media (max-width: 600px) {
            .container {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Contratar: <?php echo htmlspecialchars($task['task_name']); ?></h2>
        <p><strong>Precio:</strong> $<?php echo htmlspecialchars($task['price']); ?> MXN</p>
        <p><strong>Trabajador:</strong> <?php echo htmlspecialchars($task['worker_name']); ?></p>

        <form method="POST" action="">
            <div class="form-group">
                <label for="request_date">Fecha de la tarea:</label>
                <input type="date" id="request_date" name="request_date" required>
            </div>
            <div class="form-group">
                <label for="card_number">Número de tarjeta:</label>
                <input type="text" id="card_number" name="card_number" required>
            </div>
            <div class="form-group">
                <label for="expiry_date">Fecha de expiración:</label>
                <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/AA" required>
            </div>
            <div class="form-group">
                <label for="cvv">CVV:</label>
                <input type="text" id="cvv" name="cvv" required>
            </div>
            <div class="terms-container">
                <input type="checkbox" id="terms" required onchange="toggleSubmitButton()">
                <label for="terms">
                    <a href="../../TermCondiTra.php" target="_blank">Acepto los términos y condiciones</a>
                </label>
            </div>
            <div class="form-group" style="display: flex; justify-content: space-between;">
                <button class="back-button" type="button" onclick="window.history.back()" style="flex: 1;">Regresar</button>
                <button type="submit" id="submit-button" disabled style="flex: 1;">Solicitar</button>
            </div>
        </form>
    </div>

    <script>
        function toggleSubmitButton() {
            const checkbox = document.getElementById('terms');
            const submitButton = document.getElementById('submit-button');
            submitButton.disabled = !checkbox.checked;
        }
    </script>
</body>
</html>
