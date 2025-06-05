<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];

    // Obtener información de la solicitud
    $query = "SELECT * FROM requests WHERE id = ? AND worker_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $request_id, $user_id);
    $stmt->execute();
    $task_request = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($task_request) {
        // Marcar la tarea como rechazada en la base de datos
        $query = "UPDATE requests SET status = 'rejected' WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close();

        // Crear una notificación para el solicitante de la tarea
        $query = "INSERT INTO notifications (user_id, content, type, created_at) VALUES (?, ?, 'task_rejection', NOW())";
        $content = "Tu solicitud de tarea ha sido rechazada.";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $task_request['client_id'], $content);
        $stmt->execute();
        $stmt->close();

        header("Location: notificaciones.php");
        exit();
    } else {
        echo "Solicitud no encontrada.";
    }
} else {
    echo "Solicitud no válida.";
}
?>
