<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener todas las notificaciones del usuario
$query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Manejar la eliminación de una notificación individual
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $delete_id = $_POST['delete_id'];
        $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $delete_id, $user_id);
        $stmt->execute();
        $stmt->close();
        header("Location: notificaciones.php");
        exit();
    }

    // Manejar la eliminación de todas las notificaciones
    if (isset($_POST['delete_all'])) {
        $stmt = $conn->prepare("DELETE FROM notifications WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        header("Location: notificaciones.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #b6faf4e4; /* Fondo azul claro */
            color: #333;
            padding: 20px;
        }
        .header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .back-button {
            margin-right: 10px;
            color: black; /* Color del botón de regresar */
            text-decoration: none;
            font-size: 20px;
        }
        h1 {
            color: #004080; /* Azul más oscuro para el título */
        }
        .notification {
            border: 1px solid #cce5ff; /* Borde azul claro */
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            position: relative;
            background-color: #ffffff; /* Fondo blanco para las notificaciones */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .buttons {
            margin-top: 10px;
        }
        .buttons button {
            margin-right: 5px;
        }
        .notification.unread {
            background-color: #f0f8ff; /* Fondo azul muy claro para notificaciones no leídas */
        }
        .delete-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #91E8DF; /* Color del botón de borrar individual */
            color: white;
            border: none;
            padding: 3px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .delete-all-btn {
            background-color: #48dfd0; /* Un tono más fuerte para el botón de borrar todo */
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="header">
        <a href="../dashboard.php" class="back-button"><i class="fas fa-chevron-left"></i></a>
        <h1>Notificaciones</h1>
    </div>
    <?php if (empty($notifications)): ?>
        <p>No tienes notificaciones.</p>
    <?php else: ?>
        <form method="POST" action="">
            <button type="submit" name="delete_all" class="delete-all-btn">Borrar todas las notificaciones</button>
        </form>
        <?php foreach ($notifications as $notification): ?>
            <div class="notification <?php echo $notification['is_read'] ? '' : 'unread'; ?>">
                <p><?php echo htmlspecialchars($notification['content']); ?></p>
                <p><small><?php echo htmlspecialchars($notification['created_at']); ?></small></p>
                <form method="POST" action="" style="display:inline;">
                    <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($notification['id']); ?>">
                    <button type="submit" class="delete-btn">Borrar</button>
                </form>
                <div class="buttons">
                    <?php if ($notification['type'] == 'task_request'): ?>
                        <form action="../verperfil.php" method="get" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($notification['sender_id']); ?>">
                            <button type="submit">Ver Perfil</button>
                        </form>
                        <form action="process_payment.php" method="get" style="display:inline;">
                            <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($notification['request_id']); ?>">
                            <button type="submit">Aceptar</button>
                        </form>
                        <form action="rechazar_solicitud.php" method="post" style="display:inline;">
                            <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($notification['request_id']); ?>">
                            <button type="submit">Rechazar</button>
                        </form>
                    <?php elseif ($notification['type'] == 'message'): ?>
                        <form action="../mensaje/mensaje.php" method="get" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($notification['sender_id']); ?>">
                            <button type="submit">Enviar mensaje</button>
                        </form>
                    <?php elseif ($notification['type'] == 'task_completion'): ?>
                        <form action="confirmar_tarea.php" method="get" style="display:inline;">
                            <input type="hidden" name="contract_id" value="<?php echo htmlspecialchars($notification['contract_id']); ?>">
                            <button type="submit">Finalizar Tarea</button>
                        </form>
                    <?php elseif ($notification['type'] == 'payment_status'): ?>
                        <p><?php echo htmlspecialchars($notification['content']); ?></p>
                        <form action="../mensaje/mensaje.php" method="get" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($notification['sender_id']); ?>">
                            <button type="submit">Enviar Mensaje</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
