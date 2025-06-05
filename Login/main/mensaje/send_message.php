<?php
// Iniciar sesión
session_start();

// Conectar a la base de datos
require '../db.php';

// Recibir los datos del formulario
$sender_id = $_POST['sender_id'];
$receiver_id = $_POST['receiver_id'];
$message = $_POST['message'];

// Insertar el nuevo mensaje en la base de datos
$sql = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $sender_id, $receiver_id, $message);
$stmt->execute();

// Obtener el nombre del remitente
$query = "SELECT name FROM users WHERE id = ?";
$stmt_name = $conn->prepare($query);
$stmt_name->bind_param("i", $sender_id);
$stmt_name->execute();
$sender_name = $stmt_name->get_result()->fetch_assoc()['name'];

// Crear contenido de la notificación
$notification_content = "Has recibido un nuevo mensaje de $sender_name.";

// Insertar la notificación en la base de datos
$notification_query = "INSERT INTO notifications (user_id, type, content) VALUES (?, 'message', ?)";
$stmt_notification = $conn->prepare($notification_query);
$stmt_notification->bind_param("is", $receiver_id, $notification_content);
$stmt_notification->execute();

$stmt->close();
$stmt_name->close();
$stmt_notification->close();
$conn->close();

// Redireccionar de nuevo al chat
header("Location: mensaje.php?id=$receiver_id");
exit();
?>
