<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "handyhelpero";

// Crear conexión con la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar si la conexión falló
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Recuperar el correo electrónico y la contraseña del formulario
$email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Preparar la consulta SQL para buscar el usuario por correo electrónico
$sql = "SELECT id, password, role FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('MySQL prepare error: ' . $conn->error);
}

// Vincular el parámetro y ejecutar la consulta
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si se encontró el usuario
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    var_dump($row['role']);



    // Verificar si la contraseña es correcta
    if (password_verify($password, $row['password'])) {
        // Establecer variables de sesión y redirigir al dashboard
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['email'] = $email;  // Almacenar el correo electrónico en la sesión
        $_SESSION['role'] = $row['role']; // Almacenar el rol en la sesión

      // Redirigir según el rol del usuario
      if ($row['role'] === 'admin') {
        header("Location: main/Firstadmin.php");  // Redirigir al dashboard admin
    } else {
        header("Location: main/dashboard.php");  // Redirigir al dashboard regular
    }
    exit();
} else {
    echo "Invalid email or password.";
    header("location: index.html");
    exit();
}
} else {
echo "Invalid email or password.";
header("location: index.html");
exit();
}


// Cerrar el statement y la conexión
$stmt->close();
$conn->close();
?>