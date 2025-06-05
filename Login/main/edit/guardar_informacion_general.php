<?php
session_start();
require_once 'db.php'; // Asegúrate de tener un script que gestione la conexión a la base de datos

if (!isset($_SESSION['user_id'])) {
    header("Location: profile_form.php");
    exit;
}

$user_id = $_SESSION['user_id']; // Asume que el ID del usuario está almacenado en la sesión

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibe los datos del formulario
    $name = $_POST['name'];
    $location = $_POST['location'];

    // Actualiza la información en la base de datos
    $stmt = $conn->prepare("UPDATE users SET name=?, location=? WHERE id=?");
    $stmt->bind_param("ssi", $name, $location, $user_id);

    if ($stmt->execute()) {
        // Éxito al actualizar
        header("Location: perfil.php"); // Redirige al usuario al perfil después de guardar los cambios
        exit();
    } else {
        // Error al ejecutar la consulta
        echo "Error al actualizar la información: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
