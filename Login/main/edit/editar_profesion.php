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

// Verificar si se ha enviado el formulario de edición o eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_profile']) && $_POST['delete_profile'] == 1) {
        // Eliminar tareas asociadas al perfil
        $stmt = $conn->prepare("DELETE FROM tasks WHERE profile_id = ?");
        $stmt->bind_param("i", $profile_id);
        $stmt->execute();
        $stmt->close();

        // Eliminar imágenes asociadas al perfil
        $stmt = $conn->prepare("DELETE FROM profile_images WHERE profile_id = ?");
        $stmt->bind_param("i", $profile_id);
        $stmt->execute();
        $stmt->close();

        // Eliminar el perfil
        $stmt = $conn->prepare("DELETE FROM profiles WHERE id = ?");
        $stmt->bind_param("i", $profile_id);
        $stmt->execute();
        $stmt->close();

        // Redirigir a la página de perfil después de eliminar
        header("Location: ../perfil.php");
        exit;
    } else {
        // Obtener los datos del formulario
        $job_title = $_POST['job_title'];
        $experience = $_POST['experience'];

        // Actualizar la profesión en la tabla `profiles`
        $stmt = $conn->prepare("UPDATE profiles SET job_title = ?, experience = ? WHERE id = ?");
        $stmt->bind_param("ssi", $job_title, $experience, $profile_id);
        $stmt->execute();
        $stmt->close();

        // Eliminar tareas existentes para el perfil antes de insertar las nuevas
        $stmt = $conn->prepare("DELETE FROM tasks WHERE profile_id = ?");
        $stmt->bind_param("i", $profile_id);
        $stmt->execute();
        $stmt->close();

        // Obtener las tareas y precios del formulario
        $tasks = $_POST['tasks'];
        $prices = $_POST['prices'];

        // Insertar las nuevas tareas y precios en la tabla `tasks`
        $stmt = $conn->prepare("INSERT INTO tasks (profile_id, task_name, price) VALUES (?, ?, ?)");
        foreach ($tasks as $key => $task_name) {
            $price = $prices[$key];

            $stmt->bind_param("isd", $profile_id, $task_name, $price);
            $stmt->execute();
        }
        $stmt->close();

        // Redirigir a la página de perfil después de actualizar
        header("Location: ../perfil.php");
        exit;
    }
}

// Obtén los datos actuales de la profesión y sus tareas desde la base de datos
$stmt = $conn->prepare("SELECT job_title, experience FROM profiles WHERE id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$stmt->bind_result($job_title, $experience);
$stmt->fetch();
$stmt->close();

// Obtener las tareas actuales del perfil
$stmt = $conn->prepare("SELECT task_name, price FROM tasks WHERE profile_id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$result = $stmt->get_result();
$tasks = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Profesión</title>
    <!-- Incluir CSS, JS, u otros recursos necesarios -->


    <style>
        /* Definición de colores */
        :root {
            --primary-color: #a0ece5;
            --secondary-color: #0096c7;
            --accent-color: #023e8a;
            --button-hover-color: #0077b6;
            --white-color: #ffffff;
            --text-color: #023047;
        }

        /* Estilos generales */
        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--primary-color);
            color: var(--text-color);
            margin: 0;
            padding: 20px;
            text-align: center;
        }

        /* Título centrado */
        h1 {
            font-size: 3rem;
            color: var(--accent-color);
            margin-bottom: 40px;
        }


        /* Formulario */
        form {
            max-width: 600px;
            margin: 0 auto;
            background-color: var(--white-color);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Espaciado entre etiquetas y entradas */
        label {
            display: block;
            margin-top: 20px;
            font-weight: bold;
            text-align: left;
        }
        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border: 1px solid var(--accent-color);
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 1rem;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        /* Contenedores de tareas */
        .task-item {
            margin-top: 20px;
            padding: 15px;
            background-color: var(--primary-color);
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Contenedor de los botones */
        .button-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        /* Botones estilizados */
        .button {
            background-color: var(--secondary-color);
            color: var(--white-color);
            padding: 20px 40px;
            border: none;
            border-radius: 10px;
            font-size: 1.2rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 200px;
            text-align: center;
        }

        .button:hover {
            background-color: var(--button-hover-color);
        }

        /* Cuadros alrededor de los botones */
        .button-box {
            background-color: var(--white-color);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            width: 220px;
        }

        /* Flecha de regreso */
        .back-arrow {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: var(--accent-color);
            border: none;
            padding: 10px;
            border-radius: 50%;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .back-arrow:hover {
            background-color: var(--button-hover-color);
        }

        .back-arrow svg {
            fill: var(--white-color);
            width: 20px;
            height: 20px;
        }
    </style>
</head>
<body>

    <!-- Flecha de regreso -->
    <button class="back-arrow" onclick="window.history.back();">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 12H6.83l5.59-5.59L11 5l-7 7 7 7 1.41-1.41L6.83 13H19v-1z"/></svg>
    </button>
    <h1>Editar Profesión</h1>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?profile_id=' . htmlspecialchars($profile_id); ?>" method="POST">
        <label for="job_title">Título del trabajo:</label>
        <input type="text" id="job_title" name="job_title" value="<?php echo htmlspecialchars($job_title); ?>">
        <br>
        <label for="experience">Experiencia:</label>
        <textarea id="experience" name="experience"><?php echo htmlspecialchars($experience); ?></textarea>
        <br>
        <h3>Tareas y Precios:</h3>
        <?php foreach ($tasks as $key => $task): ?>
            <div class="task-item">
                <label for="task_<?php echo $key; ?>">Tarea:</label>
                <input type="text" id="task_<?php echo $key; ?>" name="tasks[]" value="<?php echo htmlspecialchars($task['task_name']); ?>">
                <label for="price_<?php echo $key; ?>">Precio:</label>
                <input type="text" id="price_<?php echo $key; ?>" name="prices[]" value="<?php echo htmlspecialchars($task['price']); ?>">
                <?php if ($key > 0): ?>
                    <br>
                    <br>
        
        
                    <button type="button" class="remove-task">Eliminar Tarea</button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <button type="button" id="add-task">Agregar Tarea</button>
        <br>
        <br>
        <button type="submit">Guardar Cambios</button>
        
    </form>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?profile_id=' . htmlspecialchars($profile_id); ?>" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta profesión?');">
        <input type="hidden" name="delete_profile" value="1">
        <button type="submit" class="delete-button">Eliminar Profesión</button>
    </form>

    <!-- JavaScript para manejar la adición y eliminación dinámica de tareas -->
    <script>
        document.getElementById('add-task').addEventListener('click', function() {
            var taskItem = document.createElement('div');
            taskItem.classList.add('task-item');
            taskItem.innerHTML = `
                <label>Tarea:</label>
                <input type="text" name="tasks[]">
                <label>Precio:</label>
                <input type="text" name="prices[]">
                <button type="button" class="remove-task">Eliminar Tarea</button>
            `;
            taskItem.querySelector('.remove-task').addEventListener('click', function() {
                taskItem.remove();
            });
            document.querySelector('form').insertBefore(taskItem, document.getElementById('add-task'));
        });

        var removeButtons = document.querySelectorAll('.remove-task');
        removeButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                button.parentNode.remove();
            });
        });
    </script>
</body>
</html>
