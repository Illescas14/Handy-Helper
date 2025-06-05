<?php
session_start();
require 'db.php'; // Asegúrate de tener un script que gestione la conexión a la base de datos

if (!isset($_SESSION['user_id'])) {
    header("Location: profile_form.php");
    exit;
}

$user_id = $_SESSION['user_id']; // Asume que el ID del usuario está almacenado en la sesión

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['image'])) {
    // Configura la carpeta de destino para guardar las imágenes
    $targetDir = "../uploads/";
    $fileName = basename($_FILES["image"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    // Verifica si el archivo es una imagen real o un archivo falso
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check !== false) {
        // Permitir solo ciertos formatos de archivo
        $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array(strtolower($fileType), $allowedTypes)) {
            // Sube el archivo al servidor
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                // Inserta el nombre de la imagen en la base de datos
                $stmt = $conn->prepare("INSERT INTO portfolio_images (user_id, image_name) VALUES (?, ?)");
                $stmt->bind_param("is", $user_id, $fileName);
                if ($stmt->execute()) {
                    // Éxito al guardar la imagen
                    header("Location: editar_portafolio.php"); // Redirige a la página de edición de portafolio
                    exit();
                } else {
                    // Error al ejecutar la consulta
                    echo "Error al guardar la imagen en la base de datos: " . $stmt->error;
                }
                $stmt->close();
            } else {
                echo "Error al subir el archivo.";
            }
        } else {
            echo "Solo se permiten archivos JPG, JPEG, PNG y GIF.";
        }
    } else {
        echo "El archivo no es una imagen válida.";
    }
} else {
    echo "Error: Se esperaba una solicitud POST con el archivo de imagen.";
}



// Manejo de la imagen del INE
if (isset($_FILES['image_ine'])) {
    $ineFileName = basename($_FILES["image_ine"]["name"]);
    $ineTargetFilePath = $targetDir . $ineFileName;
    $ineFileType = pathinfo($ineTargetFilePath, PATHINFO_EXTENSION);

    // Verifica si el archivo es una imagen real
    $check = getimagesize($_FILES["image_ine"]["tmp_name"]);
    if ($check !== false) {
        $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array(strtolower($ineFileType), $allowedTypes)) {
            if (move_uploaded_file($_FILES["image_ine"]["tmp_name"], $ineTargetFilePath)) {
                // Inserta el nombre de la imagen del INE en la base de datos
                $stmt = $conn->prepare("UPDATE profiles_image SET image_ine = ? WHERE user_id = ?");
                $stmt->bind_param("si", $ineFileName, $user_id);
                if ($stmt->execute()) {
                    // Éxito al guardar la imagen del INE
                    header("Location: editar_portafolio.php");
                    exit();
                } else {
                    echo "Error al guardar la imagen del INE en la base de datos: " . $stmt->error;
                }
                $stmt->close();
            } else {
                echo "Error al subir el archivo del INE.";
            }
        } else {
            echo "Solo se permiten archivos JPG, JPEG, PNG y GIF para el INE.";
        }
    } else {
        echo "El archivo del INE no es una imagen válida.";
    }
}
else {
echo "Error: Se esperaba una solicitud POST.";
}

$conn->close();
?>
