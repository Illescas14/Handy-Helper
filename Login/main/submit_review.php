<?php
session_start();
include 'db.php'; // Asegúrate de incluir el archivo de conexión a la base de datos

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No estás autenticado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = $_POST['rating'];
    $review_text = $_POST['review_text'];
    $to_user_id = $_POST['to_user_id'];
    $from_user_id = $_SESSION['user_id']; // ID del usuario que escribe la reseña

    // Validar los datos
    if (empty($rating) || empty($review_text) || empty($to_user_id)) {
        echo json_encode(['error' => 'Todos los campos son obligatorios']);
        exit();
    }

    // Evitar que un usuario escriba una reseña para sí mismo
    if ($to_user_id == $from_user_id) {
        echo json_encode(['error' => 'No puedes escribir una reseña para ti mismo']);
        exit();
    }

    // Insertar la reseña en la base de datos
    $query = "INSERT INTO reviews (from_user_id, to_user_id, rating, review_text, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiis", $from_user_id, $to_user_id, $rating, $review_text);

    if ($stmt->execute()) {
        echo json_encode(['success' => 'Reseña enviada con éxito']);
    } else {
        echo json_encode(['error' => 'Error al enviar la reseña']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['error' => 'Método de solicitud no permitido']);
}
?>