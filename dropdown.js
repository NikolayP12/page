document.addEventListener('DOMContentLoaded', function () {
    var moduleTypeSelect = document.getElementById('id_moduletype');
    var moduleInstanceSelect = document.getElementById('id_moduleinstance');
    var selectedModulesContainer = document.getElementById('selected-modules-container');
    var selectedModuleId = [];

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

        if (moduleId && !selectedModuleId.includes(moduleId)) {
            console.log(selectedModuleId);
            // Crear div para el módulo seleccionado
            selectedModuleId.push(moduleId);
            var selectedModule = document.createElement('div');
            selectedModule.className = 'selected-module';
            selectedModule.textContent = moduleName;
            selectedModule.setAttribute('data-module-id', moduleId);

            // Crear y agregar el botón de eliminar
            var deleteButton = document.createElement('button');
            deleteButton.textContent = '×';
            deleteButton.className = 'delete-module-button';
            deleteButton.type = 'button'; // Asegurar que no envíe el formulario
            deleteButton.onclick = function () {
                selectedModulesContainer.removeChild(selectedModule);
                selectedModuleId = selectedModuleId.filter(function (id) {
                    return id !== moduleId;

                });
                this.value = '';
            };
            selectedModule.appendChild(deleteButton);
            selectedModulesContainer.appendChild(selectedModule);

            // Restablecer el desplegable
            this.value = '';
        }
        this.value = '';

    });
});