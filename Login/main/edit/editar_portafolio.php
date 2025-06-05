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

// Verifica si se proporciona el parámetro `profile_id` en la URL
if (!isset($_GET['profile_id'])) {
    header("Location: ../dashboard.php");
    exit;
}

$profile_id = $_GET['profile_id'];

// Manejar la subida de nuevas imágenes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['portfolio_images'])) {
        $total_files = count($_FILES['portfolio_images']['name']);
        for ($i = 0; $i < $total_files; $i++) {
            $file_name = $_FILES['portfolio_images']['name'][$i];
            $file_tmp = $_FILES['portfolio_images']['tmp_name'][$i];
            $upload_dir = '../../uploads/';
            $file_path = $upload_dir . basename($file_name);

            if (move_uploaded_file($file_tmp, $file_path)) {
                $stmt = $conn->prepare("INSERT INTO profile_images (profile_id, image_name) VALUES (?, ?)");
                $stmt->bind_param("is", $profile_id, $file_name);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    // Manejar la eliminación de imágenes
    if (isset($_POST['delete_image'])) {
        $image_id = $_POST['delete_image'];
        $stmt = $conn->prepare("DELETE FROM profile_images WHERE id = ?");
        $stmt->bind_param("i", $image_id);
        $stmt->execute();
        $stmt->close();
    }

    // Redirigir a la misma página después de la acción
    header("Location: " . $_SERVER['PHP_SELF'] . "?profile_id=" . $profile_id);
    exit;
}

// Obtener las imágenes actuales del perfil
$stmt = $conn->prepare("SELECT id, image_name FROM profile_images WHERE profile_id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$result = $stmt->get_result();
$portfolio_images = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Portafolio</title>
    <!-- Incluir CSS, JS, u otros recursos necesarios -->


    <style>
        /* Colores base */
        :root {
            --primary-blue: #005f73;
            --secondary-blue: #0a9396;
            --light-blue: #94d2bd;
            --accent-blue: #a0ece5;
            --dark-blue: #023047;
            --white: #ffffff;
        }

        /* General */
        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--light-blue);
            color: var(--dark-blue);
            padding: 20px;
            margin: 0;
            text-align: center;
        }

        h1 {
            color: var(--primary-blue);
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        form {
            background-color: var(--accent-blue);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            display: inline-block;
            text-align: left;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            color: var(--dark-blue);
        }

        input[type="file"] {
            margin-top: 10px;
            padding: 10px;
            border: 2px solid var(--secondary-blue);
            border-radius: 5px;
            background-color: var(--white);
            cursor: pointer;
        }

        button[type="submit"] {
            margin-top: 15px;
            padding: 10px 20px;
            background-color: var(--primary-blue);
            color: var(--white);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: var(--secondary-blue);
        }

        h3 {
            margin-top: 40px;
            color: var(--primary-blue);
            font-size: 1.8rem;
        }

        /* Contenedor de imágenes */
        .portfolio-images {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .portfolio-image {
            position: relative;
            width: 200px;
            height: 200px;
            perspective: 1000px;
        }

        .portfolio-image img {
            width: 100%;
            height: 100%;
            border-radius: 10px;
            transition: transform 0.6s ease-in-out;
            transform-style: preserve-3d;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        }

        .portfolio-image:hover img {
            transform: rotateY(180deg);
        }

        /* Efecto para eliminar imágenes */
        .delete-button {
            background-color: var(--secondary-blue);
            color: var(--white);
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            transition: background-color 0.3s ease;
        }

        .delete-button:hover {
            background-color: var(--primary-blue);
        }
                /* Flecha de regreso */
        .back-arrow {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: var(--accent-blue);
            border: none;
            padding: 10px;
            border-radius: 50%;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .back-arrow:hover {
            background-color: var(--secondary-blue);
        }

        .back-arrow svg {
            fill: var(--white);
            width: 20px;
            height: 20px;
        }
    </style>

</head>
<body>
    <button class="back-arrow" onclick="window.history.back();">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 12H6.83l5.59-5.59L11 5l-7 7 7 7 1.41-1.41L6.83 13H19v-1z"/></svg>
    </button>
    <h1>Editar Portafolio</h1>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?profile_id=' . htmlspecialchars($profile_id); ?>" method="POST" enctype="multipart/form-data">
        <label for="portfolio_images">Agregar nuevas imágenes:</label>
        <input type="file" id="portfolio_images" name="portfolio_images[]" multiple>
        <br>
        <button type="submit">Subir Imágenes</button>
    </form>

    <h3>Imágenes Actuales:</h3>
    <div class="portfolio-images">
        <?php foreach ($portfolio_images as $image): ?>
            <div class="portfolio-image">
                <img src="../../uploads/<?php echo htmlspecialchars($image['image_name']); ?>" alt="Imagen del portafolio">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?profile_id=' . htmlspecialchars($profile_id); ?>" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta imagen?');">
                    <input type="hidden" name="delete_image" value="<?php echo $image['id']; ?>">
                    <button type="submit" class="delete-button">Eliminar Imagen</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
