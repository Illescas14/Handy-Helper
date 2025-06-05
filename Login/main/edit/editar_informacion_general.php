<?php
session_start();
require_once '../db.php'; // Asegúrate de que este archivo incluya la configuración de la base de datos y la gestión de la sesión

// Verifica si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../profile_form.php");
    exit;
}

// Obtén el ID de usuario de la sesión
$user_id = $_SESSION['user_id'];

// Inicializa la variable $location
$location = "";

// Verificar si se ha enviado el formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Manejar la actualización de la información general del usuario
    $name = $_POST['name'];
    $location = $_POST['location']; // Nombre del lugar a buscar

    // Usar la API de Google Maps para obtener latitud y longitud
    $apiKey = 'AIzaSyCYn0-poPALDPQ7P6LpcEaZJw7iS9bXs9Y'; // Reemplaza con tu clave API
    $address = urlencode($location);
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=$apiKey";

    $response = file_get_contents($url);
    $response = json_decode($response, true);

    if (isset($response['results'][0])) {
        $latitude = $response['results'][0]['geometry']['location']['lat'];
        $longitude = $response['results'][0]['geometry']['location']['lng'];
    } else {
        echo "<script>alert('No se pudo encontrar la ubicación proporcionada.'); window.location.href='index.html';</script>";
        exit;
    }

    // Actualizar la información general del usuario en la tabla `users`
    $stmt = $conn->prepare("UPDATE users SET name = ?, latitude = ?, longitude = ? WHERE id = ?");
    $stmt->bind_param("ssii", $name, $latitude, $longitude, $user_id);
    $stmt->execute();
    $stmt->close();

    // Manejar la carga de la nueva foto de perfil
    if ($_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $filename = basename($_FILES['profile_image']['name']);
        $upload_dir = '../../uploads/';
        $target_file = $upload_dir . $filename;

        // Mueve el archivo subido al directorio de uploads
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            // Actualiza el nombre de la foto de perfil en la tabla `users`
            $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            $stmt->bind_param("si", $filename, $user_id);
            $stmt->execute();
            $stmt->close();
        } else {
            echo "Error al subir la foto de perfil.";
        }
    }

    // Redirige a la página de perfil después de actualizar
    header("Location: ../perfil.php");
    exit;
}

// Obtén los datos actuales del usuario desde la base de datos
$stmt = $conn->prepare("SELECT name, latitude, longitude, profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $latitude, $longitude, $profile_image);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Información General</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .profile-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .profile-image-container {
            position: relative;
            overflow: hidden;
            border-radius: 50%;
            width: 100px;
            height: 100px;
            margin-right: 20px;
        }

        .profile-image {
            width: 100%;
            height: auto;
            border-radius: 50%;
        }

        input[type="text"], input[type="file"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #48dfd0;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
            width: 100%;
        }

        button:hover {
            background-color: #39b8b0;
        }

        .back-button {
            text-decoration: none;
            color: #333;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Editar Información General</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
            <div class="profile-header">
                <a href="../perfil.php" class="back-button"><i class="fas fa-chevron-left"></i> Volver</a>
                <div class="profile-image-container">
                    <img src="../../uploads/<?php echo htmlspecialchars($profile_image); ?>" alt="Foto de perfil" class="profile-image">
                    <input type="file" name="profile_image" style="position: absolute; top: 0; left: 0; opacity: 0; width: 100%; height: 100%; cursor: pointer;">
                </div>
            </div>
            <label for="name">Nombre:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            <label for="location">Lugar:</label>
            <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($location); ?>" required>
            <button type="submit">Guardar Cambios</button>
        </form>
    </div>
</body>
</html>
