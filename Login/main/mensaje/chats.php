<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Chats</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="chats.css"> <!-- Verifica que el archivo CSS esté correctamente vinculado aquí -->
</head>

<body>
    <header class="app-header">
        <div class="container__header">
            <a href="../dashboard.php" class="back-button"><i class="fas fa-chevron-left"></i></a>
            <h1>Chats</h1>
        </div>
    </header>
    <div class="chat-list">
        <?php
        session_start();
        require '../db.php';
        $user_id = $_SESSION['user_id'];
        $sql = "SELECT u.id, u.name, u.profile_image, MAX(m.created_at) as last_message_time
            FROM messages m
            JOIN users u ON u.id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END
            WHERE (m.sender_id = ? OR m.receiver_id = ?) AND m.sender_id <> m.receiver_id
            GROUP BY u.id, u.name, u.profile_image
            ORDER BY last_message_time DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $user_id, $user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()): ?>
            <div class="chat-entry card">
                <a href="mensaje.php?id=<?php echo $row['id']; ?>" class="chat-link">
                    <img src="../../uploads/<?php echo $row['profile_image']; ?>" alt="User Image" class="user-image">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($row['name']); ?></div>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
</body>

</html>