<?php
include '../db.php'; // Conectar a la base de datos

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $job_title = $_POST['job_title'];
    $experience = $_POST['experience'];

    // Preparar la declaración SQL para actualizar el trabajador
    $stmt = $conn->prepare("UPDATE profiles SET job_title = ?, experience = ? WHERE id = ?");
    $stmt->bind_param("ssi", $job_title, $experience, $id);

    if ($stmt->execute()) {
        // Redirigir después de actualizar
        header("Location: trabajadores.php");
    } else {
        echo "Error al actualizar el trabajador: " . $stmt->error;
    }

    $stmt->close();
} elseif (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Consultar el trabajador para obtener los datos actuales
    $stmt = $conn->prepare("SELECT job_title, experience FROM profiles WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($job_title, $experience);
    $stmt->fetch();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Trabajador</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 50%;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #007BFF;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .form-group button {
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .form-group button:hover {
            background-color: #0056b3;
        }
        .back-button {
            text-decoration: none;
            color: #007BFF;
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .back-button svg {
            margin-right: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Actualizar Trabajador</h2>
    <form action="updateT.php" method="POST">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
        <div class="form-group">
            <label for="job_title">Especialidad:</label>
            <input type="text" id="job_title" name="job_title" value="<?php echo htmlspecialchars($job_title); ?>" required>
        </div>
        <div class="form-group">
            <label for="experience">Experiencia:</label>
            <input type="text" id="experience" name="experience" value="<?php echo htmlspecialchars($experience); ?>" required>
        </div>
        <div class="form-group">
            <button type="submit">Actualizar</button>
        </div>
    </form>
    <a href="../Firstadmin.php" class="back-button">
        <!-- Flecha azul -->
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 1-.5.5H2.707l3.147 3.146a.5.5 0 0 1-.708.708l-4-4a.5.5 0 0 1 0-.708l4-4a.5.5 0 0 1 .708.708L2.707 7.5H14.5A.5.5 0 0 1 15 8z"/>
        </svg>
        Volver a la pantalla anterior
    </a>
</div>

</body>
</html>
