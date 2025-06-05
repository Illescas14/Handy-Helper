<?php
session_start();
require '../db.php'; // Asume que tienes un archivo db.php con la conexión a la base de datos

$profile_id = $_GET['id']; // Obtener el ID del perfil desde la URL
$user_id = $_SESSION['user_id']; // Obtener el ID del usuario desde la sesión

// Consultar la base de datos para obtener la información del perfil
$profile_info_sql = "SELECT name, profile_image FROM users WHERE id = ?";
$stmt_info = $conn->prepare($profile_info_sql);
$stmt_info->bind_param("i", $profile_id);
$stmt_info->execute();
$profile_info = $stmt_info->get_result()->fetch_assoc();

if (!$profile_info) {
    die('Perfil no encontrado.'); // Asegúrate de manejar este caso adecuadamente en tu aplicación
}

// Consulta para obtener los mensajes entre los usuarios
$sql = "SELECT m.*, u.name AS sender_name, u.profile_image AS sender_image, 
               r.name AS receiver_name, r.profile_image AS receiver_image 
        FROM messages m
        JOIN users u ON u.id = m.sender_id
        JOIN users r ON r.id = m.receiver_id
        WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $user_id, $profile_id, $profile_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="men.css">
</head>

<body>
    <header>
        <div class="left-header">
            <a href="chats.php" class="back-button" onclick="history.back()"><i class="fas fa-chevron-left"></i></a>
            <img src="../../uploads/<?php echo htmlspecialchars($profile_info['profile_image']); ?>" alt="Foto de perfil">
        </div>
        <h2><?php echo htmlspecialchars($profile_info['name']); ?></h2>
        <div class="right-header"></div> <!-- Mantiene el espaciado y alineación -->
    </header>

    <div class="messages">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class='message <?php echo $row['sender_id'] == $user_id ? "my-message" : "their-message"; ?>'>
                <p><?php echo htmlspecialchars($row['message']); ?></p>
            </div>
        <?php endwhile; ?>
    </div>

    <div class="message-input">
        <form action="send_message.php" method="post">
            <input type="hidden" name="receiver_id" value="<?php echo $profile_id; ?>">
            <input type="hidden" name="sender_id" value="<?php echo $user_id; ?>">
            <textarea name="message" placeholder="Escribe tu mensaje aquí..." autocomplete="off"></textarea>
            <button type="submit"><i class="fas fa-paper-plane"></i></button>
        </form>
    </div>
</body>

</html>