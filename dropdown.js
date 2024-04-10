
document.addEventListener('DOMContentLoaded', function () {
    var moduleTypeSelect = document.getElementById('id_moduletype'); // moduletype es el ID del select del formulario para el tipo de modulo

    // Función para obtener el valor de un parámetro específico de la URL

    function getQueryParam(param) {
        var urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }

    var courseId = getQueryParam('course');

    moduleTypeSelect.addEventListener('change', function () {
        // Obtener el tipo de módulo seleccionado
        var type = this.value;

        if (type) {
            // La URL debe apuntar a un script de PHP en tu servidor que pueda responder a la solicitud AJAX.
            var ajaxurl = M.cfg.wwwroot + '/mod/page/get_activities.php?type=' + encodeURIComponent(type) + '&courseid=' + courseId;
            console.log('Entro a dropdown.js y el AJAX URL es:', ajaxurl);
            fetch(ajaxurl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok ' + response.statusText);
                    }
                    return response.json();
                })
                .then(response => {
                    // Obtener todos los selects de 'associatedmoduleid' del DOM
                    console.log('Respuesta del servidor (get_activities):', response.debug); // Muestra la información de depuración en la consola.
                    var activities = response.data; // Asegúrate de utilizar la parte 'data' de la respuesta.

                    var selects = document.querySelectorAll('select[name^="associatedmoduleid"]');

                    // Actualizar cada select con las nuevas actividades
                    selects.forEach(select => {
                        // Limpiar las opciones existentes
                        select.innerHTML = '';
                        // Añadir una opción predeterminada
                        var defaultOption = new Option('Select an activity', ''); // puedo intentar usar el get_string('selectactivity', 'page') si estuviera disponible
                        select.add(defaultOption);

                        // Añadir nuevas opciones
                        activities.forEach(activity => {
                            var option = new Option(activity.name, activity.id);
                            select.add(option);
                        });
                    });
                })
                .catch(error => {
                    console.error('Error fetching activities:', error);
                });
        }
    });
});