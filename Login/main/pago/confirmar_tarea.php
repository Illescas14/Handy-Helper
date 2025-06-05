<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['contract_id'])) {
    $contract_id = $_GET['contract_id'];

    // Obtener el contrato
    $query = "SELECT * FROM contracts WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $contract_id);
    $stmt->execute();
    $contract = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($contract) {
        // Verificar si el usuario es el cliente o el trabajador
        if ($contract['requester_id'] == $user_id) {
            // Confirmación por el cliente
            $query = "UPDATE contracts SET status = 'completed', end_date = NOW(), payment_status = 'completed' WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $contract_id);
            $stmt->execute();
            $stmt->close();

            // Notificar al trabajador que la tarea ha sido confirmada
            $notification_content = "El cliente ha confirmado que la tarea fue completada. Tu dinero sera transferido";
            $query = "INSERT INTO notifications (user_id, type, content, contract_id) VALUES (?, 'task_completed', ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("isi", $contract['worker_id'], $notification_content, $contract_id);
            $stmt->execute();
            $stmt->close();

        } elseif ($contract['worker_id'] == $user_id) {
            // Confirmación por el trabajador
            $query = "UPDATE contracts SET status = 'completed' WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $contract_id);
            $stmt->execute();
            $stmt->close();

            // Notificar al cliente que la tarea ha sido completada
            $notification_content = "El trabajador ha confirmado que la tarea fue completada.";
            $query = "INSERT INTO notifications (user_id, type, content, contract_id) VALUES (?, 'task_completed', ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("isi", $contract['requester_id'], $notification_content, $contract_id);
            $stmt->execute();
            $stmt->close();
        }

        header("Location: notificaciones.php");
        exit();
    } else {
        echo "Contrato no encontrado.";
    }
}
?>
