<?php
include '../db.php'; // Conectar a la base de datos

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Preparar la declaración SQL para eliminar el trabajador
    $stmt = $conn->prepare("DELETE FROM profiles WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redirigir después de eliminar
        header("Location: trabajadores.php");
    } else {
        echo "Error al eliminar el trabajador: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
