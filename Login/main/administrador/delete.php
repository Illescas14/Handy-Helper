<?php
include '../db.php'; // Conectar a la base de datos

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Preparar la declaración SQL para eliminar el usuario
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redirigir después de eliminar
        header("Location: Firstadmin.php");
    } else {
        echo "Error al eliminar el usuario: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
