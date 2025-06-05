<?php
session_start(); // Inicia la sesión para manejar cualquier dato de sesión necesario

// Configuración de conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "handyhelpero";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Recoger los datos del formulario
$name = isset($_POST['name']) ? $conn->real_escape_string($_POST['name']) : null;
$email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : null;
$password = isset($_POST['password']) ? $_POST['password'] : null;
$shareLocation = isset($_POST['shareLocation']) ? true : false;
$city = isset($_POST['city']) ? $conn->real_escape_string($_POST['city']) : null;
$state = isset($_POST['state']) ? $conn->real_escape_string($_POST['state']) : null;
$termsAccepted = isset($_POST['terms']) ? true : false; // Recoger el estado de aceptación de términos
// Validación inicial de los campos requeridos
if (!$name || !$email || !$password) {
    echo "<script>alert('Por favor complete todos los campos requeridos y acepte los terminos y condiciones.'); window.location.href='index.html';</script>";
    exit;
}

// Verificar si el correo electrónico ya está registrado
$checkEmail = "SELECT email FROM users WHERE email = ?";
$checkStmt = $conn->prepare($checkEmail);
if (!$checkStmt) {
    echo "Preparation failed: (" . $conn->errno . ") " . $conn->error;
    exit;
}
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();
if ($checkResult->num_rows > 0) {
    echo "<script>alert('Este correo electrónico ya está registrado.'); window.location.href='registro.html';</script>";
    exit;
}

// Manejo de la carga del archivo de imagen
$profile_image = '';
$targetDir = "uploads/";  // Asegúrate de que esta carpeta tiene los permisos adecuados
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
    $fileName = basename($_FILES['profile_image']['name']);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    // Permitir ciertos formatos de archivo
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
    if (in_array($fileType, $allowTypes)) {
        // Subir el archivo al servidor
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFilePath)) {
            $profile_image = $fileName;
        } else {
            echo "<script>alert('Sorry, there was an error uploading your file.'); window.location.href='index.html';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Sorry, only JPG, JPEG, PNG, & GIF files are allowed to upload.'); window.location.href='index.html';</script>";
        exit;
    }
}

// Encriptar la contraseña antes de guardarla en la base de datos
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Si el usuario acepta compartir la ubicación, usamos la API de Google Maps para obtener la latitud y longitud
$latitude = null;
$longitude = null;
if ($shareLocation) {
    if (isset($_POST['latitude']) && isset($_POST['longitude'])) {
        $latitude = $_POST['latitude'];
        $longitude = $_POST['longitude'];
    }
} else {
    // Si el usuario proporciona una ubicación manual (ciudad y estado)
    if ($city && $state) {
        $address = urlencode("$city, $state");
        $apiKey = 'AIzaSyCYn0-poPALDPQ7P6LpcEaZJw7iS9bXs9Y';
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
    }
}
// Después de recoger los datos del formulario...
$role = isset($_POST['role']) ? $conn->real_escape_string($_POST['role']) : 'user'; // Asigna el rol

// Asegúrate de que el rol sea uno válido (user/admin)
if (!in_array($role, ['user', 'admin'])) {
    $role = 'user'; // Valor por defecto
}

// Insertar los datos en la base de datos
$sql = "INSERT INTO users (name, email, password, profile_image, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "Preparation failed: (" . $conn->errno . ") " . $conn->error;
    exit;
}
$stmt->bind_param("ssssdd", $name, $email, $hashed_password, $profile_image, $latitude, $longitude);

// Ejecutar la consulta y verificar si fue exitosa
if ($stmt->execute()) {
    echo "<script>alert('Nuevo usuario creado exitosamente.'); window.location.href='index.html';</script>";
    exit();
} else {
    echo "Error al insertar: " . $stmt->error;
}

// Cerrar la declaración y la conexión
$stmt->close();
$conn->close();
?>