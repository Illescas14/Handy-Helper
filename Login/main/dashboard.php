<?php
session_start();
include 'db.php'; // Asegúrate de que este archivo conecta correctamente a tu base de datos

// Verifica si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html"); // Redirige si no está logueado
    exit;
}

$user_id = $_SESSION['user_id'];

// Consulta para verificar si el usuario ya ha creado un perfil
$sql = "SELECT COUNT(*) as profile_count FROM profiles WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Determina si mostrar o no el botón de crear perfil
$showCreateProfileButton = $row['profile_count'] == 0;

// Consulta para obtener el número de notificaciones no leídas
$sql_notifications = "SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0";
$stmt_notifications = $conn->prepare($sql_notifications);
$stmt_notifications->bind_param("i", $user_id);
$stmt_notifications->execute();
$result_notifications = $stmt_notifications->get_result();
$unread_notifications = $result_notifications->fetch_assoc()['unread_count'];

$stmt->close();
$stmt_notifications->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Perfiles</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCYn0-poPALDPQ7P6LpcEaZJw7iS9bXs9Y&libraries=places"></script>
</head>

<body>
    <header>
        <div class="logo-container">
            <img src="../../assets/imagenes/logo.jpeg" alt="Logo" class="logo">
        </div>
        <div class="nav-container">
            <form id="searchForm" action="load_profiles.php" method="GET">
                <input type="text" name="search" placeholder="Buscar perfiles..." id="searchBar" class="search-bar">
                <label for="filter">Ordenar por:</label>
                <select id="filter" name="filter">
                    <option value="default">Por defecto</option>
                    <option value="best_rated">Mejor valorado</option>
                </select>
                <label for="dayFilter">Disponibilidad por día:</label>
                <select id="dayFilter" name="day">
                    <option value="">Todos los dias</option>
                    <option value="Monday">Lunes</option>
                    <option value="Tuesday">Martes</option>
                    <option value="Wednesday">Miércoles</option>
                    <option value="Thursday">Jueves</option>
                    <option value="Friday">Viernes</option>
                    <option value="Saturday">Sábado</option>
                    <option value="Sunday">Domingo</option>
                </select>
                <label for="radius">Radio de búsqueda (km):</label>
                <input type="number" id="radius" name="radius" min="0" max="100" step="1" value="0">
            </form>

            <nav class="main-nav">
                <ul>
                    <li><a href="perfil.php"><i class="fas fa-user icon"></i></a></li>
                    <li><a href="mensaje/chats.php"><i class="fas fa-comments icon"></i></a></li>
                    <li class="notifications">
                        <a href="pago/notificaciones.php">
                            <i class="fas fa-bell icon"></i> 
                            <span class="notifications-count"><?php echo $unread_notifications; ?></span>
                        </a>
                    </li>
                    <?php if ($showCreateProfileButton): ?>
                        <li><a href="profile_form.php">Crear Perfil</a></li>
                    <?php endif; ?>
                    <li><a href="../index.html">Cerrar sesión</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <section id="profiles" class="profile-grid">
            <?php include 'load_profiles.php'; ?>
        </section>
    </main>
    <script src="app.js"></script>
</body>

</html>
