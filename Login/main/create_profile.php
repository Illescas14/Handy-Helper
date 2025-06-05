<?php
session_start();
require_once 'db.php'; // Asegúrate de tener un script que gestione la conexión a la base de datos

if (!isset($_SESSION['user_id'])) {
    header("Location: profile_form.php");
    exit;
}

$user_id = $_SESSION['user_id']; // Asume que el ID del usuario está almacenado en la sesión

// Asumiendo que otros datos del formulario también son procesados aquí
$job_titles = $_POST['job_title'];
$tasks = $_POST['tasks'];
$prices = $_POST['prices'];
$experiences = $_POST['experience'];
$portfolio_images = $_FILES['portfolio_images'];

$availability_days = $_POST['days'];
$availability_start_times = $_POST['start_time'];
$availability_end_times = $_POST['end_time'];
$availability_statuses = $_POST['availability_status'];

// Inicia la transacción
$conn->begin_transaction();

try {
    // Recorre cada profesión y guarda los datos en la tabla `profiles`
    foreach ($job_titles as $key => $job_title) {
        // Inserta el perfil
        $stmt = $conn->prepare("INSERT INTO profiles (user_id, job_title, experience) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $job_title, $experiences[$key]);
        $stmt->execute();
        $profile_id = $stmt->insert_id; // Obtiene el ID del perfil insertado
        $stmt->close();

        // Maneja la carga de múltiples archivos de imágenes
        if (!empty($portfolio_images['name'][$key][0])) {
            $upload_dir = '../uploads/';
            foreach ($portfolio_images['tmp_name'][$key] as $file_key => $tmp_name) {
                $filename = basename($portfolio_images['name'][$key][$file_key]);
                $fileType = pathinfo($filename, PATHINFO_EXTENSION);
                $allowedTypes = array('jpg', 'png', 'jpeg', 'gif');
                if (!in_array(strtolower($fileType), $allowedTypes)) {
                    throw new Exception("Tipo de archivo no permitido: $filename");
                }

                $target_file = $upload_dir . $filename;
                if (move_uploaded_file($tmp_name, $target_file)) {
                    $stmt = $conn->prepare("INSERT INTO profile_images (profile_id, image_name) VALUES (?, ?)");
                    $stmt->bind_param("is", $profile_id, $filename);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    throw new Exception("Error al subir archivo: $filename");
                }
            }
        }

        // Inserta las tareas y precios en la tabla `tasks`
        foreach ($tasks[$key] as $task_index => $task_name) {
            $price = $prices[$key][$task_index];

            $stmt = $conn->prepare("INSERT INTO tasks (profile_id, task_name, price) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $profile_id, $task_name, $price);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Inserta disponibilidad
    foreach ($availability_days as $index => $day) {
        $start_time = $availability_start_times[$index];
        $end_time = $availability_end_times[$index];
        $status = $availability_statuses[$index];

        // Inserta en la tabla `availability`
        $stmt = $conn->prepare("INSERT INTO availability (user_id, day_of_week, start_time, end_time, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $day, $start_time, $end_time, $status);
        $stmt->execute();
        $stmt->close();
    }

    // Commit de la transacción
    $conn->commit();
    header("Location: perfil.php"); // Redirige al usuario a perfil.php después de crear el perfil correctamente
    exit;

} catch (Exception $e) {
    $conn->rollback(); // Revierte la transacción en caso de error
    echo "Error: " . $e->getMessage();
}

$conn->close();
?>