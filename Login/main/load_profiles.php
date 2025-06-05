<?php
session_start();
include 'db.php'; // Asegúrate de que este archivo conecta correctamente a tu base de datos

// Verifica si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html"); // Redirige si no está logueado
    exit;
}

// Obtener ID de usuario actual
$user_id = $_SESSION['user_id'];

// Obtener coordenadas del usuario actual
$sql = "SELECT latitude, longitude FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_coordinates = $result->fetch_assoc();
$user_latitude = $user_coordinates['latitude'];
$user_longitude = $user_coordinates['longitude'];
$stmt->close();

// Obtener parámetros de búsqueda
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'default';
$day = isset($_GET['day']) ? $_GET['day'] : '';
$radius = isset($_GET['radius']) ? $_GET['radius'] : 0;

// Construir la consulta SQL base
$sql = "SELECT u.id, u.name, u.profile_image, GROUP_CONCAT(DISTINCT p.job_title SEPARATOR ', ') as job_titles
        FROM users u
        INNER JOIN profiles p ON u.id = p.user_id
        LEFT JOIN tasks t ON p.id = t.profile_id
        WHERE u.id != ?"; // Excluir el perfil del usuario actual

// Inicializar los parámetros de bind
$bind_types = "i";
$bind_values = [$user_id];

// Aplicar filtros adicionales según los parámetros de búsqueda
if (!empty($search)) {
    $sql .= " AND (u.name LIKE ? OR p.job_title LIKE ? OR t.task_name LIKE ?)";
    $searchParam = "%$search%";
    $bind_types .= "sss";
    $bind_values[] = $searchParam;
    $bind_values[] = $searchParam;
    $bind_values[] = $searchParam;
}

// Filtrar por día de disponibilidad
if (!empty($day)) {
    $sql .= " AND EXISTS (
                 SELECT 1
                 FROM availability a
                 WHERE u.id = a.user_id
                 AND (a.day_of_week = ? OR a.specific_date = ?)
             )";
    $bind_types .= "ss";
    $bind_values[] = $day;
    $bind_values[] = $day;
}

// Filtrar por radio de búsqueda (coordenadas)
if ($radius > 0) {
    $sql .= " AND (ACOS(SIN(RADIANS(u.latitude)) * SIN(RADIANS(?)) + COS(RADIANS(u.latitude)) * COS(RADIANS(?)) * COS(RADIANS(? - u.longitude))) * 6371) <= ?";
    $bind_types .= "dddi";
    $bind_values[] = $user_latitude;
    $bind_values[] = $user_latitude;
    $bind_values[] = $user_longitude;
    $bind_values[] = $radius;
}

// Agrupar por usuario para evitar duplicados
$sql .= " GROUP BY u.id";

// Ordenar resultados según el filtro seleccionado
switch ($filter) {
    case 'best_rated':
        $sql .= " ORDER BY (SELECT AVG(rating) FROM reviews WHERE to_user_id = u.id) DESC";
        break;
    case 'default':
    default:
        // Orden por defecto o ningún orden especificado
        break;
}

// Preparar la consulta SQL
$stmt = $conn->prepare($sql);

// Bind parameters
$stmt->bind_param($bind_types, ...$bind_values);

// Ejecutar consulta
$stmt->execute();
$result = $stmt->get_result();

// Mostrar los perfiles encontrados
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Mostrar la tarjeta de perfil
        $imagePath = $row['profile_image'] ? '../uploads/' . $row['profile_image'] : 'path/to/default/image.jpg';
        echo "<div class='profile-card'>";
        echo "<img src='" . htmlspecialchars($imagePath) . "' alt='Profile Image' class='profile-img'>";
        echo "<div class='profile-info'>";
        echo "<h3>" . htmlspecialchars($row['name']) . "</h3>";
        echo "<p>" . htmlspecialchars($row['job_titles']) . "</p>";
        echo "</div>";
        echo "<button onclick='location.href=\"verperfil.php?id={$row['id']}\"' class='profile-button'>Ver Perfil</button>";
        echo "</div>";
    }
} else {
    echo "<p>No hay perfiles disponibles.</p>";
}

// Cerrar conexión y statement
$stmt->close();
$conn->close();
?>