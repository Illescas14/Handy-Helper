<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Perfil</title>
    <link rel="stylesheet" href="form.css">
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #e0f7fa;
            color: #0d47a1;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: auto;
        }

        .container {
            background-color: #bbdefb;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 800px;
            margin: auto;
        }

        .logo-container {
            display: flex;
            justify-content: center;
            margin-bottom: 60px;
        }

        .logo {
            width: 180px;
            height: auto;
            border-radius: 50%; /* Esto hace que la imagen tenga forma de círculo */
            object-fit: cover; /* Asegura que la imagen cubra el área del círculo */
            animation: spin 4s infinite linear;
            margin-left: auto; /* Ajusta el valor según lo necesites */
        }

        @keyframes spin {
            from {
                transform: rotateY(0deg);
            }
            to {
                transform: rotateY(360deg);
            }
        }

        .signin-signup {
            background-color: #e3f2fd;
            padding: 20px;
            border-radius: 15px;
        }

        .title {
            text-align: center;
            color: #1a237e;
            margin-bottom: 20px;
        }

        .input-field {
            position: relative;
            margin-bottom: 15px;
        }

        .input-field i {
            position: absolute;
            top: 50%;
            left: 10px;
            transform: translateY(-50%);
            color: #1a237e;
            pointer-events: none; /* Evitar eventos del puntero */
        }

        .input-field input,
        .input-field textarea,
        .input-field select {
            width: calc(100% - 30px); /* Ajustar ancho para evitar superposición con íconos */
            padding: 10px 10px 10px 30px; /* Agregar padding para hacer espacio para el ícono */
            background-color: #e8f5e9;
            border: 1px solid #64b5f6;
            border-radius: 10px;
            color: #0d47a1;
            font-size: 16px;
            outline: none;
        }

        .input-field textarea {
            resize: none;
            height: 100px;
        }

        .input-field input[type="file"] {
            padding: 5px;
            background-color: #bbdefb;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background-color: #1e88e5;
            border: none;
            border-radius: 20px;
            color: white;
            font-size: 20px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 20px;
            opacity: 0.5;
            pointer-events: none;
        }

        .btn.enabled {
            opacity: 1;
            pointer-events: auto;
        }

        .add-button,
        .remove-button,
        .add-task-button,
        .remove-task-button,
        .add-availability-button,
        .remove-availability-button {
            background-color: #64b5f6;
            color: white;
            border: none;
            padding: 10px 10px;
            margin-top: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            display: block; /* Asegurar que los botones ocupen todo el ancho */
            width: 100%; /* Hacer que los botones ocupen todo el ancho */
        }

        .add-button:hover,
        .remove-button:hover,
        .add-task-button:hover,
        .remove-task-button:hover,
        .add-availability-button:hover,
        .remove-availability-button:hover {
            background-color: #1e88e5;
        }

        .tasks-container,
        .availability-container {
            margin-bottom: 15px;
        }

        .availability-input {
            margin-bottom: 10px;
        }

        h2 {
            color: #1a237e;
            background-color: #bbdefb;
            padding: 10px;
            border-radius: 10px;
            text-align: center;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        /* Responsividad */
        @media (max-width: 600px) {
            .logo {
                width: 80px;
            }

            .btn {
                font-size: 18px;
            }

            .input-field input,
            .input-field textarea,
            .input-field select {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="logo-container">
        <img src="../../assets/imagenes/logo.jpeg" alt="Logo" class="logo">
    </div>
    <div class="container">
        <div class="forms-container">
            <div class="signin-signup">
                <form action="create_profile.php" method="POST" class="sign-in-form" enctype="multipart/form-data">
                    <h2 class="title">Crea tu perfil</h2>

                    <div id="professions-container">
                        <div class="profession-container">
                            <div class="input-field">
                                <i class="fas fa-user"></i>
                                <input type="text" placeholder="Profesión" name="job_title[]" required>
                            </div>
                            <div class="tasks-container">
                                <div class="task-container">
                                    <div class="task-input-field">
                                        <input type="text" placeholder="Tarea" name="tasks[0][]" required>
                                        <input type="text" placeholder="Precio" name="prices[0][]" required>
                                    </div>
                                    <button type="button" class="remove-task-button" onclick="removeTask(this)">Eliminar Tarea</button>
                                </div>
                            </div>
                            <button type="button" class="add-task-button" onclick="addTask(this, 0)">Añadir Tarea</button>
                            <div class="input-field">
                                <i class="fas fa-briefcase"></i>
                                <textarea placeholder="Experiencia" name="experience[]" required></textarea>
                            </div>
                            <div class="input-field">
                                <h3>Inserta tu portafolio</h3>
                                <i class="fas fa-images"></i>
                                <input type="file" name="portfolio_images_0[]" multiple required>
                            </div>
                            <div class="input-field">
                                 <h3>Inserta tu INE</h3>
                                 <i class="fas fa-id-card"></i>
                                <input type="file" name="image_ine" required>
                            </div>
                            
                            <button type="button" class="remove-button" onclick="removeProfession(this)">Eliminar Profesión</button>
                        </div>
                    </div>
                    <button type="button" class="add-button" onclick="addProfession()">Añadir Profesión</button>

                    <h2>Días disponibles</h2>
                    <div id="availability-container">
                        <div class="availability-input">
                            <div class="input-field">
                                <i class="fas fa-calendar-alt"></i>
                                <select name="days[]" multiple required>
                                    <option value="Monday">Lunes</option>
                                    <option value="Tuesday">Martes</option>
                                    <option value="Wednesday">Miércoles</option>
                                    <option value="Thursday">Jueves</option>
                                    <option value="Friday">Viernes</option>
                                    <option value="Saturday">Sábado</option>
                                    <option value="Sunday">Domingo</option>
                                </select>
                            </div>
                            <div class="input-field">
                                <i class="fas fa-clock"></i>
                                <input type="time" name="start_time[]" required>
                                <input type="time" name="end_time[]" required>
                            </div>
                            <div class="input-field">
                                <i class="fas fa-check-circle"></i>
                                <select name="availability_status[]" required>
                                    <option value="available">Disponible</option>
                                    <option value="booked">Ocupado</option>
                                </select>
                            </div>
                            <button type="button" class="remove-availability-button" onclick="removeAvailability(this)">Eliminar Día</button>
                        </div>
                    </div>
                    <button type="button" class="add-availability-button" onclick="addAvailability()">Añadir Día</button>

                    <div class="checkbox-container">
                        <input type="checkbox" id="termsCheckbox" onchange="toggleButton()">
                        <label for="terms">
                            <a href="../TermCondiTra.php" target="_blank">Acepto los términos y condiciones</a>
                        </label>
                    </div>

                    <input type="submit" value="CREAR" class="btn">
                </form>
            </div>
        </div>
    </div>

    <script>
        let professionIndex = 1;
        let availabilityIndex = 1;

        function addProfession() {
            const container = document.getElementById('professions-container');
            const newProfession = document.createElement('div');
            newProfession.className = 'profession-container';
            newProfession.innerHTML = `
                <div class="input-field">
                    <i class="fas fa-user"></i>
                    <input type="text" placeholder="Profesión" name="job_title[]" required>
                </div>
                <div class="tasks-container">
                    <div class="task-container">
                        <div class="task-input-field">
                            <input type="text" placeholder="Tarea" name="tasks[${professionIndex}][]" required>
                            <input type="text" placeholder="Precio" name="prices[${professionIndex}][]" required>
                        </div>
                        <button type="button" class="remove-task-button" onclick="removeTask(this)">Eliminar Tarea</button>
                    </div>
                </div>
                <button type="button" class="add-task-button" onclick="addTask(this, ${professionIndex})">Añadir Tarea</button>
                <div class="input-field">
                    <i class="fas fa-briefcase"></i>
                    <textarea placeholder="Experiencia" name="experience[]" required></textarea>
                </div>
                <div class="input-field">
                <h3>Inserta tu foto de perfil</h3>
                    <i class="fas fa-images"></i>
                    <input type="file" name="portfolio_images_${professionIndex}[]" multiple required>
                </div>
                 <div class="input-field">
                                 <h3>Inserta tu INE</h3>
                                 <i class="fas fa-id-card"></i>
                                <input type="file" name="image_ine" required>
                            </div>
                <button type="button" class="remove-button" onclick="removeProfession(this)">Eliminar Profesión</button>
            `;
            container.appendChild(newProfession);
            professionIndex++;
        }

        function addTask(button, index) {
            const container = button.previousElementSibling;
            const newTask = document.createElement('div');
            newTask.className = 'task-container';
            newTask.innerHTML = `
                <div class="task-input-field">
                    <input type="text" placeholder="Tarea" name="tasks[${index}][]" required>
                    <input type="text" placeholder="Precio" name="prices[${index}][]" required>
                </div>
                <button type="button" class="remove-task-button" onclick="removeTask(this)">Eliminar Tarea</button>
            `;
            container.appendChild(newTask);
        }

        function removeTask(button) {
            const container = button.parentElement.parentElement;
            container.removeChild(button.parentElement);
        }

        function removeProfession(button) {
            const container = document.getElementById('professions-container');
            container.removeChild(button.parentElement);
        }

        function addAvailability() {
            const container = document.getElementById('availability-container');
            const newAvailability = document.createElement('div');
            newAvailability.className = 'availability-input';
            newAvailability.innerHTML = `
                <div class="input-field">
                    <i class="fas fa-calendar-alt"></i>
                    <select name="days[]" multiple required>
                        <option value="Monday">Lunes</option>
                        <option value="Tuesday">Martes</option>
                        <option value="Wednesday">Miércoles</option>
                        <option value="Thursday">Jueves</option>
                        <option value="Friday">Viernes</option>
                        <option value="Saturday">Sábado</option>
                        <option value="Sunday">Domingo</option>
                    </select>
                </div>
                <div class="input-field">
                    <i class="fas fa-clock"></i>
                    <input type="time" name="start_time[]" required>
                    <input type="time" name="end_time[]" required>
                </div>
                <div class="input-field">
                    <i class="fas fa-check-circle"></i>
                    <select name="availability_status[]" required>
                        <option value="available">Disponible</option>
                        <option value="booked">Ocupado</option>
                    </select>
                </div>
                <button type="button" class="remove-availability-button" onclick="removeAvailability(this)">Eliminar Día</button>
            `;
            container.appendChild(newAvailability);
            availabilityIndex++;
        }

        function removeAvailability(button) {
            const container = button.parentElement;
            container.remove();
        }

        function toggleButton() {
            const checkbox = document.getElementById('termsCheckbox');
            const button = document.querySelector('.btn');

            if (checkbox.checked) {
                button.classList.add('enabled');
            } else {
                button.classList.remove('enabled');
            }
        }
    </script>
</body>

</html>
