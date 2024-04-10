document.addEventListener('DOMContentLoaded', function () {
    var moduleTypeSelect = document.getElementById('id_moduletype');
    var moduleInstanceSelect = document.getElementById('id_moduleinstance');
    var selectedModulesContainer = document.getElementById('selected-modules-container');

    // Función para obtener el valor de un parámetro específico de la URL
    function getQueryParam(param) {
        var urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }

    var courseId = getQueryParam('course');

    // Actualizar desplegable de instancias de módulo cuando cambie el tipo de módulo
    moduleTypeSelect.addEventListener('change', function () {
        var type = this.value;

        if (type) {
            var ajaxurl = M.cfg.wwwroot + '/mod/page/get_activities.php?type=' + encodeURIComponent(type) + '&courseid=' + courseId;
            console.log('Entro a dropdown.js y el AJAX URL es:', ajaxurl);
            fetch(ajaxurl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    // Limpiar las opciones existentes
                    moduleInstanceSelect.innerHTML = '';
                    // Añadir una opción predeterminada
                    var defaultOption = new Option('Select an activity', '');
                    moduleInstanceSelect.add(defaultOption);

                    // Añadir nuevas opciones
                    if (data.data && Array.isArray(data.data)) { // Asegurarse de que data.data existe y es un array
                        data.data.forEach(activity => { // Usar data.data para acceder al array de actividades
                            var option = new Option(activity.name, activity.id);
                            moduleInstanceSelect.add(option);
                        });
                    } else {
                        console.error('Invalid response format:', data);
                    }
                })
                .catch(error => {
                    console.error('Error fetching activities:', error);
                });
        }
    });

    // Manejar la selección de una instancia de módulo
    moduleInstanceSelect.addEventListener('change', function () {
        var moduleId = this.value;
        var moduleName = this.options[this.selectedIndex].text;

        if (moduleId) {
            // Agregar visualmente el módulo al contenedor de módulos seleccionados
            var selectedModule = document.createElement('div');
            selectedModule.textContent = moduleName;
            selectedModule.setAttribute('data-module-id', moduleId);
            selectedModulesContainer.appendChild(selectedModule);

            // Limpiar el desplegable de instancias de módulo para una nueva selección
            this.value = ''; // Esto puede depender de cómo quieres gestionar la UI
        }
    });
});