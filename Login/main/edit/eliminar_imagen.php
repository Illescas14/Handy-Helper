<?php
session_start();
require_once 'db.php'; // Asegúrate de tener un script que gestione la conexión a la base de datos

if (!isset($_SESSION['user_id'])) {
    header("Location: profile_form.php");
    exit;
}

$user_id = $_SESSION['user_id']; // Asume que el ID del usuario está almacenado en la sesión

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['image_id'])) {
    // $image_id debe ser el identificador único de la imagen a eliminar
    $image_id = $_GET['image_id'];

    // Consulta para obtener el nombre del archivo de imagen
    $stmt = $conn->prepare("SELECT image_name FROM portfolio_images WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $image_id, $user_id);
    $stmt->execute();
    $stmt->bind_result($image_name);

    if ($stmt->fetch()) {
        // Elimina la imagen del directorio de uploads
        $uploadDir = "../uploads/";
        $filePath = $uploadDir . $image_name;
        if (unlink($filePath)) {
            // Elimina la entrada de la base de datos
            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM portfolio_images WHERE id=? AND user_id=?");
            $stmt->bind_param("ii", $image_id, $user_id);
            if ($stmt->execute()) {
                // Éxito al eliminar la imagen
                header("Location: editar_portafolio.php"); // Redirige a la página de edición de portafolio
                exit();
            } else {
                // Error al eliminar la entrada de la base de datos
                echo "Error al eliminar la imagen de la base de datos: " . $stmt->error;
            }
        } else {
            echo "Error al eliminar la imagen del servidor.";
        }
    } else {
        echo "La imagen no fue encontrada en la base de datos.";
    }

    $stmt->close();
} else {
    echo "Error: Se esperaba una solicitud GET con el parámetro image_id.";
}

$conn->close();
?>
