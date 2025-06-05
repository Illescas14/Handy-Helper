<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['request_id'])) {
    $request_id = $_GET['request_id'];

    // Obtener la información de la solicitud de tarea
    $query = "SELECT * FROM requests WHERE id = ? AND worker_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $request_id, $user_id);
    $stmt->execute();
    $task_request = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($task_request) {
        // Verificar si el campo client_id existe y no es nulo
        if (isset($task_request['client_id']) && !empty($task_request['client_id'])) {
            $client_id = $task_request['client_id'];
        } else {
            echo "Error: No se encontró el client_id en la solicitud.";
            exit();
        }

        // Obtener el precio de la tarea
        $query = "SELECT price FROM tasks WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $task_request['task_id']);
        $stmt->execute();
        $stmt->bind_result($amount);
        $stmt->fetch();
        $stmt->close();

        if ($amount === null) {
            echo "Error: No se encontró el precio de la tarea.";
            exit();
        }

        // Simulación de la lógica de pago
        $payment_successful = true; // Simula que el pago fue exitoso

        if ($payment_successful) {
            // Actualizar estado de la solicitud a 'accepted'
            $query = "UPDATE requests SET status = 'accepted' WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $request_id);
            $stmt->execute();
            $stmt->close();

            // Crear el contrato con los datos de la solicitud
            $query = "INSERT INTO contracts (requester_id, worker_id, task_id, status, amount, start_date, payment_status) VALUES (?, ?, ?, 'accepted', ?, NOW(), 'pending')";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iiid", $client_id, $user_id, $task_request['task_id'], $amount);
            $stmt->execute();
            $contract_id = $stmt->insert_id; // Obtener el ID del contrato recién creado
            $stmt->close();

            // Obtener el nombre del trabajador
            $query = "SELECT name FROM users WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($worker_name);
            $stmt->fetch();
            $stmt->close();

            // Crear notificación para el solicitante (cliente)
            $content = "Tu tarea ha sido aceptada. Por favor, contacta a " . htmlspecialchars($worker_name) . " para más detalles.";
            $query = "INSERT INTO notifications (user_id, content, type, created_at, sender_id) VALUES (?, ?, 'message', NOW(), ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("isi", $client_id, $content, $user_id);
            $stmt->execute();
            $stmt->close();

            // Crear una notificación para el cliente de que su solicitud fue aceptada
            $notification_content = "Tu solicitud ha sido aceptada. Ahora debes confirmar cuando la tarea esté completada.";
            $query = "INSERT INTO notifications (user_id, type, content, contract_id) VALUES (?, 'task_completion', ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("isi", $client_id, $notification_content, $contract_id);
            $stmt->execute();
            $stmt->close();

            // Crear una notificación para el trabajador también
            $notification_content_worker = "Has aceptado una solicitud. Confirma cuando completes la tarea.";
            $query = "INSERT INTO notifications (user_id, type, content, contract_id) VALUES (?, 'task_completion', ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("isi", $user_id, $notification_content_worker, $contract_id);
            $stmt->execute();
            $stmt->close();

            // Redirigir a la página de notificaciones
            header("Location: notificaciones.php");
            exit();
        } else {
            echo "El pago no se pudo procesar.";
        }
    } else {
        echo "Solicitud no encontrada.";
    }
} else {
    echo "Solicitud no válida.";
}
?>
