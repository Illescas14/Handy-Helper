<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Verificar si se recibió el id del evento a eliminar
if (isset($_POST['id'])) {
    $event_id = $_POST['id'];

    // Consultar la información del evento antes de eliminarlo
    $query = "SELECT specific_date, day_of_week FROM availability WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $event_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Obtener los datos del evento
        $row = $result->fetch_assoc();
        $specificDateToDelete = $row['specific_date'];
        $dayOfWeek = $row['day_of_week'];

        // Verificar si el evento tiene una fecha específica o es concurrente
        if (!empty($specificDateToDelete)) {
            // Eliminar el evento específico
            $delete_query = "DELETE FROM availability WHERE id = ? AND user_id = ?";
            $delete_stmt = $conn->prepare($delete_query);
            $delete_stmt->bind_param("ii", $event_id, $user_id);
            $delete_stmt->execute();

            if ($delete_stmt->affected_rows > 0) {
                // Éxito al eliminar el evento específico
                echo json_encode(['success' => true]);
                exit();
            } else {
                // Error al eliminar el evento específico
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el evento específico.']);
                exit();
            }
        } else {
            // Insertar un nuevo evento concurrente
            // Calcular la fecha específica del próximo día de la semana
            $today = new DateTime('today');
            $nextDate = new DateTime('next ' . $dayOfWeek, $today->getTimezone());
            $specific_date = $nextDate->format('Y-m-d');

            // Insertar el nuevo evento como ocupado con la fecha específica encontrada
            $insert_query = "INSERT INTO availability (user_id, day_of_week, specific_date, start_time, end_time, status)
                             VALUES (?, ?, ?, '09:00:00', '17:00:00', 'booked')";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("iss", $user_id, $dayOfWeek, $specific_date);

            if ($insert_stmt->execute()) {
                // Éxito al insertar el nuevo evento como ocupado
                echo json_encode(['success' => true]);
                exit();
            } else {
                // Error al insertar el nuevo evento
                echo json_encode(['success' => false, 'message' => 'Error al insertar el nuevo evento como ocupado.']);
                exit();
            }
        }
    } else {
        // No se encontró el evento específico
        echo json_encode(['success' => false, 'message' => 'No se encontró el evento específico para eliminar.']);
        exit();
    }
} else {
    // Si no se recibió el id del evento a eliminar
    echo json_encode(['success' => false, 'message' => 'Falta el ID del evento a eliminar.']);
    exit();
}
?>