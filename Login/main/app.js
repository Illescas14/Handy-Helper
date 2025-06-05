document.addEventListener('DOMContentLoaded', function() {
    const searchBar = document.getElementById('searchBar');
    const filterSelect = document.getElementById('filter');
    const dayFilterSelect = document.getElementById('dayFilter');
    const radiusInput = document.getElementById('radius');
    const profilesSection = document.getElementById('profiles');

    // Función para realizar la búsqueda y actualizar las tarjetas de perfiles
    function performSearch() {
        const searchValue = searchBar.value.trim();
        const filterValue = filterSelect.value;
        const dayFilterValue = dayFilterSelect.value;
        const radiusValue = radiusInput.value;

        // Realizar una solicitud AJAX al servidor
        const xhr = new XMLHttpRequest();
        const url = `load_profiles.php?search=${encodeURIComponent(searchValue)}&filter=${encodeURIComponent(filterValue)}&day=${encodeURIComponent(dayFilterValue)}&radius=${encodeURIComponent(radiusValue)}`;
        xhr.open('GET', url, true);

        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 400) {
                // Éxito en la solicitud
                const response = xhr.responseText;
                profilesSection.innerHTML = response; // Actualizar la sección de perfiles con la respuesta del servidor
            } else {
                // Error en la solicitud
                console.error('Error al cargar los perfiles:', xhr.status, xhr.statusText);
            }
        };

        xhr.onerror = function() {
            // Error en la conexión
            console.error('Error de conexión al cargar los perfiles.');
        };

        xhr.send(); // Enviar la solicitud
    }

    // Escuchar cambios en los filtros y en la barra de búsqueda para activar la búsqueda en tiempo real
    searchBar.addEventListener('input', performSearch);
    filterSelect.addEventListener('change', performSearch);
    dayFilterSelect.addEventListener('change', performSearch);
    radiusInput.addEventListener('input', performSearch);

    // Realizar la primera búsqueda al cargar la página
    performSearch();
});
document.addEventListener('DOMContentLoaded', function() {
    fetch('load_notifications.php')
    .then(response => response.json())
    .then(data => {
        const notificationsCount = document.querySelector('.notifications-count');
        notificationsCount.textContent = data.unreadCount;
    });
});
