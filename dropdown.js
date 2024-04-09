document.addEventListener('DOMContentLoaded', function () {
    var moduleTypeSelect = document.getElementById('id_moduletype'); // moduletype es el ID del select del formulario para el tipo de modulo
    moduleTypeSelect.addEventListener('change', function () {
        // Obtener el tipo de módulo seleccionado
        var type = this.value;
        if (type) {
            // La URL debe apuntar a un script de PHP en tu servidor que pueda responder a la solicitud AJAX.
            // La ruta debe ser relativa a la raíz de tu instalación de Moodle.
            var ajaxurl = M.cfg.wwwroot + '/mod/page/get_activities.php?type=' + encodeURIComponent(type);

            // Realizar la solicitud AJAX
            fetch(ajaxurl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok ' + response.statusText);
                    }
                    return response.json();
                })
                .then(activities => {
                    // Obtener todos los selects de 'associatedmoduleid' del DOM
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
