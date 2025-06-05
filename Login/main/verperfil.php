<?php
session_start();
include 'db.php'; // Incluir el archivo de conexión a la base de datos

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

// Obtener el ID del perfil de la URL
if (isset($_GET['id'])) {
    $pro_id = $_GET['id'];

    // Función para obtener todos los perfiles del usuario
    function getUserProfiles($conn, $pro_id)
    {
        $query = "SELECT users.name, users.profile_image, profiles.job_title, profiles.experience, profiles.id AS profile_id,
                     users.latitude, users.longitude
              FROM users
              LEFT JOIN profiles ON users.id = profiles.user_id
              WHERE users.id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $pro_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $profiles = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['profile_id'] !== null) {
                $profile_id = $row['profile_id'];
                $row['portfolio_images'] = getProfileImages($conn, $profile_id);
                $row['tasks'] = getProfileTasks($conn, $profile_id);
            }
            // Convertir las coordenadas en ciudad y estado
            $locationInfo = getLocationInfo($row['latitude'], $row['longitude']);
            $row['city'] = $locationInfo['city'];
            $row['state'] = $locationInfo['state'];
            $profiles[] = $row;
        }
        return $profiles;
    }

    // Función para obtener la información de ubicación desde las coordenadas
    function getLocationInfo($latitude, $longitude)
    {
        if (empty($latitude) || empty($longitude)) {
            return ['city' => '', 'state' => ''];
        }

        $apiKey = 'AIzaSyCYn0-poPALDPQ7P6LpcEaZJw7iS9bXs9Y'; // Reemplazar con tu propia clave de API de Google Maps

        // URL para la solicitud de Geocodificación Inversa
        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$latitude},{$longitude}&key={$apiKey}";

        // Realizar la solicitud HTTP
        $response = file_get_contents($url);
        if ($response === false) {
            // Manejar el error de solicitud HTTP
            return ['city' => '', 'state' => ''];
        }

        // Decodificar la respuesta JSON
        $data = json_decode($response, true);

        // Verificar si se encontraron resultados
        $city = '';
        $state = '';
        if (isset($data['results'][0])) {
            foreach ($data['results'][0]['address_components'] as $component) {
                if (in_array('locality', $component['types'])) {
                    $city = $component['long_name'];
                } elseif (in_array('administrative_area_level_1', $component['types'])) {
                    $state = $component['long_name'];
                }
            }
        }

        return [
            'city' => $city,
            'state' => $state
        ];
    }

    // Función para obtener las imágenes del portafolio de un perfil
    function getProfileImages($conn, $profile_id)
    {
        $query = "SELECT image_name FROM profile_images WHERE profile_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $profile_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Función para obtener las tareas y precios de un perfil
    function getProfileTasks($conn, $profile_id)
    {
        $query = "SELECT id AS task_id, task_name, price FROM tasks WHERE profile_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $profile_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Función para obtener las reseñas de un usuario
    function getUserReviews($conn, $pro_id)
    {
        $query = "SELECT reviews.rating, reviews.review_text, reviews.created_at, users.name 
                  FROM reviews 
                  JOIN users ON reviews.from_user_id = users.id 
                  WHERE reviews.to_user_id = ? 
                  ORDER BY reviews.created_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $pro_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $reviews = [];
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }
        return $reviews;
    }

    // Función para obtener la disponibilidad del usuario
    function getUserAvailability($conn, $pro_id)
    {
        $query = "SELECT id, day_of_week, specific_date, start_time, end_time, status 
                  FROM availability 
                  WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $pro_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Obtener todos los perfiles del usuario
    $userProfiles = getUserProfiles($conn, $pro_id);

    // Obtener las reseñas del usuario
    $userReviews = getUserReviews($conn, $pro_id);

    // Obtener la disponibilidad completa del usuario
    $userAvailability = getUserAvailability($conn, $pro_id);

    // Verificar si se encontraron perfiles
    $hasProfiles = !empty($userProfiles);

    // Obtener la información general del usuario (nombre, foto de perfil, ubicación)
    $userInfo = [
        'name' => $userProfiles[0]['name'],
        'profile_image' => $userProfiles[0]['profile_image'],
        'city' => $userProfiles[0]['city'],
        'state' => $userProfiles[0]['state']
    ];

    // Generar eventos para el calendario en PHP
    $events = [];
    $startDate = new DateTime('first day of this month');
    $endDate = new DateTime('last day of this year');
    $today = new DateTime('today');

    foreach ($userAvailability as $entry) {
        if (!empty($entry['day_of_week']) && !empty($entry['specific_date'])) {
            // Día concurrente con día de la semana y fecha específica
            $dayOfWeek = $entry['day_of_week'];
            $dayOfWeekNumber = array_search($dayOfWeek, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']) + 1;

            // Iterar desde hoy hasta la fecha de fin
            $currentDate = clone $startDate;
            if ($today > $startDate) {
                $currentDate = clone $today;
            }

            while ($currentDate <= $endDate) {
                // Si el día de la semana coincide y es la fecha específica
                if ($currentDate->format('N') == $dayOfWeekNumber && $currentDate->format('Y-m-d') == $entry['specific_date']) {
                    // Configurar evento que ocupe todo el día
                    $events[] = [
                        'id' => $entry['id'],
                        'start' => $currentDate->format('Y-m-d'),
                        'end' => $currentDate->format('Y-m-d'),
                        'backgroundColor' => $entry['status'] === 'available' ? 'green' : 'transparent',
                        'borderColor' => $entry['status'] === 'available' ? 'green' : 'transparent',
                        'allDay' => true // Indica que es un evento de todo el día
                    ];
                }
                $currentDate->modify('+1 day'); // Asegurarse de iterar diariamente
            }
        } elseif (!empty($entry['day_of_week'])) {
            // Día concurrente sin fecha específica
            $dayOfWeek = $entry['day_of_week'];
            $dayOfWeekNumber = array_search($dayOfWeek, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']) + 1;

            // Iterar desde hoy hasta la fecha de fin
            $currentDate = clone $startDate;
            if ($today > $startDate) {
                $currentDate = clone $today;
            }

            while ($currentDate <= $endDate) {
                // Si el día de la semana coincide
                if ($currentDate->format('N') == $dayOfWeekNumber) {
                    // Verificar si hay un día específico ocupado en esta fecha
                    $isSpecificDateOccupied = false;
                    foreach ($userAvailability as $specificEntry) {
                        if (
                            !empty($specificEntry['specific_date']) &&
                            $specificEntry['specific_date'] == $currentDate->format('Y-m-d') &&
                            $specificEntry['status'] === 'booked'
                        ) {
                            $isSpecificDateOccupied = true;
                            break;
                        }
                    }

                    // Solo agregar el evento si no está ocupado específicamente
                    if (!$isSpecificDateOccupied) {
                        $events[] = [
                            'id' => $entry['id'],
                            'start' => $currentDate->format('Y-m-d'),
                            'end' => $currentDate->format('Y-m-d'),
                            'backgroundColor' => $entry['status'] === 'available' ? 'green' : 'red',
                            'borderColor' => $entry['status'] === 'available' ? 'green' : 'red',
                            'allDay' => true // Indica que es un evento de todo el día
                        ];
                    }
                }
                $currentDate->modify('+1 day'); // Asegurarse de iterar diariamente
            }
        } elseif (!empty($entry['specific_date'])) {
            // Día específico
            $specificDate = new DateTime($entry['specific_date']);
            if ($specificDate >= $today) {
                $events[] = [
                    'id' => $entry['id'],
                    'start' => $specificDate->format('Y-m-d'),
                    'end' => $specificDate->format('Y-m-d'),
                    'backgroundColor' => $entry['status'] === 'available' ? 'green' : 'red',
                    'borderColor' => $entry['status'] === 'available' ? 'green' : 'red',
                    'allDay' => true // Indica que es un evento de todo el día
                ];
            }
        }
    }

} else {
    echo "ID de perfil no especificado.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfiles del Usuario</title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css"
        integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">


</head>

<body>
    <header style="background-color: #48dfd0; padding: 20px; color: white;">
    
    </header>
    <main class="container profile-container" style="padding: 20px;">
        <!-- Columna izquierda: Perfil -->
        <div class="left-column">
            <!-- Información general del usuario -->
            <div class="card mb-3" style="border-color: #48dfd0;">
                <div class="card-body">
                    <div class="profile-header">
                        <a href="dashboard.php" class="back-button" onclick="history.back()">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <div class="profile-image-container ml-3">
                            <img src="../uploads/<?php echo $userInfo['profile_image']; ?>" alt="Foto de perfil"
                                class="profile-image" onclick="toggleImageSize(this)">
                        </div>
                        <div class="ml-3">
                            <h3><?php echo $userInfo['name']; ?></h3>
                            <p><strong>Ubicación:</strong> <?php echo $userInfo['city'] . ', ' . $userInfo['state']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Menú desplegable para profesiones -->
            <?php if (!empty($userProfiles)): ?>
                <?php $hasProfiles = false; ?>
                <?php foreach ($userProfiles as $profile): ?>
                    <?php if (!empty($profile['job_title'])): ?>
                        <?php $hasProfiles = true; break; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if ($hasProfiles): ?>
                    <div class="card mb-3" style="border-color: #48dfd0;">
                        <div class="card-body">
                            <h3>Profesiones</h3>
                            <select id="professionDropdown" class="form-control" style="border-color: #48dfd0;">
                                <?php foreach ($userProfiles as $index => $profile): ?>
                                    <?php if (!empty($profile['job_title'])): ?>
                                        <option value="<?php echo $profile['profile_id']; ?>" <?php if ($index == 0) echo 'selected'; ?>>
                                            <?php echo $profile['job_title']; ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            <!-- Calendario de disponibilidad -->
            <?php if (!empty($events)): ?>
                <div class="card mb-3" style="border-color: #48dfd0;">
                    <div class="card-body">
                        <h3>Calendario de disponibilidad</h3>
                        <div id="calendar"></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <!-- Columna derecha: Información de la profesión y reseñas -->
        <div class="right-column">
            <?php if (!empty($userProfiles)): ?>
                <?php $hasProfiles = false; ?>
                <?php foreach ($userProfiles as $profile): ?>
                    <?php if (!empty($profile['job_title'])): ?>
                        <?php $hasProfiles = true; break; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if ($hasProfiles): ?>
                    <div id="professionInfo">
                        <?php foreach ($userProfiles as $index => $profile): ?>
                            <?php if (!empty($profile['job_title'])): ?>
                                <div class="card profession-card" id="profession-<?php echo $profile['profile_id']; ?>" <?php if ($index == 0) echo 'style="display: block;"'; ?> style="border-color: #48dfd0;">
                                    <div class="card-body">
                                        <div class="info-card">
                                            <div class="card-section">
                                                <h3><?php echo $profile['job_title']; ?></h3>
                                                <p><strong>Experiencia:</strong> <?php echo $profile['experience']; ?></p>
                                            </div>
                                            <div class="card-section">
                                                <h3>Tareas y Precios</h3>
                                                <?php foreach ($profile['tasks'] as $task): ?>
                                                    <div class="task-item">
                                                        <p><strong><?php echo $task['task_name']; ?>:</strong>
                                                            $<?php echo $task['price']; ?> MXN</p>
                                                        <a href="pago/contratar.php?task_id=<?php echo $task['task_id']; ?>&worker_id=<?php echo $pro_id; ?>" 
                                                            class="btn btn-primary" style="background-color: #48dfd0; border-color: #48dfd0;">Contratar</a>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <div class="card mt-3">
                                            <div class="card-header" style="background-color: #48dfd0; color: white;">
                                                <h3>Portafolio</h3>
                                            </div>
                                            <div class="card-body portfolio">
                                                <div class="portfolio-images">
                                                    <?php foreach ($profile['portfolio_images'] as $image): ?>
                                                        <div class="portfolio-image">
                                                            <img src="../uploads/<?php echo $image['image_name']; ?>"
                                                                alt="Imagen de portafolio" class="img-thumbnail"
                                                                onclick="toggleImageSize(this)">
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Sección de reseñas -->
        <div class="container mt-5">
            <div class="card" style="border-color: #48dfd0;">
                <div class="card-header" style="background-color: #48dfd0; color: white;">Reseñas de los Servicios</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-4 text-center">
                            <h1 class="text-warning mt-4 mb-4">
                                <b><span id="average_rating">0.0</span> / 5</b>
                            </h1>
                            <div class="mb-3" id="star_rating_display">
                                <!-- Estrellas aquí se actualizarán dinámicamente -->
                            </div>
                            <h3>
                                <span id="total_review">0</span> Reseñas totales
                            </h3>
                        </div>
                        <div class="col-sm-4">
                            <p>
                            <div class="progress-label-left"><b>5</b> <i class="fas fa-star text-warning"></i></div>
                            <div class="progress-label-right">(<span id="total_5_star_review">0</span>)</div>
                            <div class="progress">
                                <div class="progress-bar bg-warning" role="progressbar" aria-valuenow="0"
                                    aria-valuemin="0" aria-valuemax="100" id="5_star_progress"></div>
                            </div>
                            </p>
                            <p>
                            <div class="progress-label-left"><b>4</b> <i class="fas fa-star text-warning"></i></div>
                            <div class="progress-label-right">(<span id="total_4_star_review">0</span>)</div>
                            <div class="progress">
                                <div class="progress-bar bg-warning" role="progressbar" aria-valuenow="0"
                                    aria-valuemin="0" aria-valuemax="100" id="4_star_progress"></div>
                            </div>
                            </p>
                            <p>
                            <div class="progress-label-left"><b>3</b> <i class="fas fa-star text-warning"></i></div>
                            <div class="progress-label-right">(<span id="total_3_star_review">0</span>)</div>
                            <div class="progress">
                                <div class="progress-bar bg-warning" role="progressbar" aria-valuenow="0"
                                    aria-valuemin="0" aria-valuemax="100" id="3_star_progress"></div>
                            </div>
                            </p>
                            <p>
                            <div class="progress-label-left"><b>2</b> <i class="fas fa-star text-warning"></i></div>
                            <div class="progress-label-right">(<span id="total_2_star_review">0</span>)</div>
                            <div class="progress">
                                <div class="progress-bar bg-warning" role="progressbar" aria-valuenow="0"
                                    aria-valuemin="0" aria-valuemax="100" id="2_star_progress"></div>
                            </div>
                            </p>
                            <p>
                            <div class="progress-label-left"><b>1</b> <i class="fas fa-star text-warning"></i></div>
                            <div class="progress-label-right">(<span id="total_1_star_review">0</span>)</div>
                            <div class="progress">
                                <div class="progress-bar bg-warning" role="progressbar" aria-valuenow="0"
                                    aria-valuemin="0" aria-valuemax="100" id="1_star_progress"></div>
                            </div>
                            </p>
                        </div>
                        <div class="col-sm-4 text-center">
                            <h3 class="mt-4 mb-3">Reseñas</h3>
                            <button type="button" class="btn btn-primary" data-toggle="modal"
                                data-target="#reviewModal">Escribir reseña</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-5" id="review_content"></div>
        </div>

        <!-- Modal para escribir reseñas -->
        <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="reviewModalLabel">Escribir reseña</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="review_form">
                            <div class="form-group">
                                <label for="review_rating">Calificación</label>
                                <select class="form-control" id="review_rating">
                                    <option value="1">1 estrella</option>
                                    <option value="2">2 estrellas</option>
                                    <option value="3">3 estrellas</option>
                                    <option value="4">4 estrellas</option>
                                    <option value="5">5 estrellas</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="review_text">Reseña</label>
                                <textarea class="form-control" id="review_text" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Enviar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script>
            $(document).ready(function () {
                var reviews = <?php echo json_encode($userReviews); ?>;
                var totalReviews = reviews.length;
                var averageRating = 0;
                var starCounts = [0, 0, 0, 0, 0];

                // Calcular promedio y contar estrellas
                reviews.forEach(function (review) {
                    averageRating += parseInt(review.rating);
                    starCounts[review.rating - 1]++;
                });

                if (totalReviews > 0) {
                    averageRating = (averageRating / totalReviews).toFixed(1);
                }

                $("#average_rating").text(averageRating);
                $("#total_review").text(totalReviews);

                // Actualizar estrellas según el promedio
                var starRatingDisplay = $("#star_rating_display");
                starRatingDisplay.empty(); // Limpiar cualquier contenido previo

                // Mostrar estrellas completas
                for (var i = 0; i < Math.floor(averageRating); i++) {
                    starRatingDisplay.append('<i class="fas fa-star text-warning"></i>');
                }

                // Mostrar media estrella si aplica
                if (averageRating % 1 !== 0) {
                    starRatingDisplay.append('<i class="fas fa-star-half-alt text-warning"></i>');
                }

                // Mostrar estrellas vacías para completar hasta 5 estrellas
                for (var i = Math.ceil(averageRating); i < 5; i++) {
                    starRatingDisplay.append('<i class="fas fa-star star-light"></i>');
                }

                // Actualizar barra de progreso de estrellas
                for (var i = 0; i < 5; i++) {
                    var starCount = starCounts[i];
                    var percentage = (totalReviews > 0) ? ((starCount / totalReviews) * 100).toFixed(1) : 0;
                    $("#total_" + (i + 1) + "_star_review").text(starCount);
                    $("#" + (i + 1) + "_star_progress").css("width", percentage + "%");
                }

                // Mostrar reseñas en el contenido de reseñas
                var reviewContent = $("#review_content");
                reviews.forEach(function (review) {
                    var reviewHTML = `
            <div class="card mt-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-2">
                            <div class="rating">
                                ${'<i class="fas fa-star text-warning"></i>'.repeat(review.rating)}
                                ${'<i class="fas fa-star star-light"></i>'.repeat(5 - review.rating)}
                            </div>
                            <div class="review-date">${new Date(review.created_at).toLocaleDateString()}</div>
                        </div>
                        <div class="col-sm-10">
                            <h5>${review.name}</h5>
                            <p>${review.review_text}</p>
                        </div>
                    </div>
                </div>
            </div>`;
                    reviewContent.append(reviewHTML);
                });

                // Manejar el envío del formulario de reseña
                $("#review_form").submit(function (event) {
                    event.preventDefault();

                    var rating = $("#review_rating").val();
                    var reviewText = $("#review_text").val();

                    $.ajax({
                        url: 'submit_review.php', // Cambia esta URL por la de tu archivo PHP que maneja la inserción de reseñas
                        method: 'POST',
                        data: {
                            rating: rating,
                            review_text: reviewText,
                            to_user_id: <?php echo $pro_id; ?>
                        },
                        success: function (response) {
                            alert('Reseña enviada con éxito');
                            location.reload(); // Recargar la página para mostrar la nueva reseña
                        }
                    });
                });
            });

        </script>












        <div id="overlay" class="overlay" onclick="toggleImageSize()">
            <img id="overlayImg" src="" alt="Imagen en grande">
        </div>
        <script>
            // Función para cambiar el tamaño de la imagen
            function toggleImageSize(img) {
                const overlay = document.getElementById('overlay');
                const overlayImg = document.getElementById('overlayImg');
                if (overlay.style.display === "none" || overlay.style.display === "") {
                    overlay.style.display = "flex";
                    overlayImg.src = img.src;
                } else {
                    overlay.style.display = "none";
                }
            }
        </script>


        <script>
            $(document).ready(function () {
                // Mostrar la primera tarjeta de profesión al cargar la página
                $('#professionInfo .profession-card').hide(); // Ocultar todas las tarjetas inicialmente
                $('#professionInfo .profession-card:first').show(); // Mostrar la primera tarjeta de profesión

                // Manejar el cambio de la selección de la profesión
                $('#professionDropdown').change(function () {
                    var selectedProfession = $(this).val();
                    $('#professionInfo .profession-card').hide(); // Ocultar todas las tarjetas de profesión
                    $('#profession-' + selectedProfession).show(); // Mostrar la tarjeta de profesión seleccionada
                });
            });
        </script>



        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var calendarEl = document.getElementById('calendar');
                var events = <?php echo json_encode($events); ?>;

                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    events: events,
                    editable: false, // Hace que los eventos no sean editables
                    selectable: false // Hace que las fechas no sean seleccionables
                });

                calendar.render();
            });
        </script>


        <script>
            function toggleImageSize(image) {
                const overlay = document.getElementById('overlay');
                const overlayImg = document.getElementById('overlayImg');
                if (image) {
                    overlayImg.src = image.src;
                    overlay.classList.add('active');
                } else {
                    overlay.classList.remove('active');
                    overlayImg.src = '';
                }
            }
        </script>

</body>

</html>

<style>
    body {
        background-color: #f8f9fa;
        color: #343a40;
    }

    .container {
        max-width: 1200px;
    }

    .profile-header {
        display: flex;
        align-items: center;
    }

    .profile-image-container {
        width: 100%;
        height: auto;
        overflow: hidden;
        border-radius: 50%;
        border: 2px solid #343a40;
    }

    .profile-image {
        width: 100%;
        height: auto;
    }

    .info-card {
        margin-bottom: 20px;
    }

    .card-section {
        margin-bottom: 20px;
    }

    .task-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .portfolio-images {
        display: flex;
        flex-wrap: wrap;
    }

    .portfolio-images img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        margin: 5px;
    }

    .enlarged {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(1.5);
        z-index: 1000;
        border-radius: 0;
        border: none;
        width: auto;
        height: auto;
    }

    .overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        z-index: 999;
        justify-content: center;
        align-items: center;
    }

    .overlay.active {
        display: flex;
    }

    .left-column,
    .right-column {
        padding: 15px;
    }

    .left-column {
        width: 40%;
    }

    .right-column {
        width: 60%;
    }

    @media (max-width: 768px) {

        .left-column,
        .right-column {
            width: 100%;
        }

        .left-column {
            margin-bottom: 20px;
        }
    }
</style>
<style>
    .progress-label-left {
        float: left;
        margin-right: 0.5em;
        line-height: 1em;
    }

    .progress-label-right {
        float: right;
        margin-left: 0.3em;
        line-height: 1em;
    }

    .star-light {
        color: #e9ecef;
    }

    .profile-container {
        display: flex;
        flex-wrap: wrap;
    }

    .left-column {
        flex: 1;
        min-width: 300px;
    }

    .right-column {
        flex: 2;
        min-width: 300px;
    }

    .overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .overlay img {
        max-width: 90%;
        max-height: 90%;
    }
</style>