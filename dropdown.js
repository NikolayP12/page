document.addEventListener('DOMContentLoaded', function () {
    var moduleTypeSelect = document.getElementById('id_moduletype');
    var moduleInstanceSelect = document.getElementById('id_moduleinstance');
    var selectedModulesContainer = document.getElementById('selected-modules-container');
    var hiddenSelectedModuleIds = document.getElementById('selectedmoduleids');
    var hiddenSelectedModuleNames = document.getElementById('selectedmodulenames');
    var courseId = document.getElementById('courseid').value;

    var arraySelectedModuleName = [];
    var arraySelectedModuleId = [];

    function updateHiddenIdsField() {
        if (hiddenSelectedModuleIds) { // Asegurarse de que el elemento existe
            hiddenSelectedModuleIds.value = arraySelectedModuleId.join(', ');
            console.log(hiddenSelectedModuleIds.value);
        } else {
            console.error('Hidden ids field not found');
        }
    }

    function updateHiddenNamesField() {
        if (hiddenSelectedModuleNames) { // Asegurarse de que el elemento existe
            hiddenSelectedModuleNames.value = arraySelectedModuleName.join(', ');
            console.log(hiddenSelectedModuleNames.value);
        } else {
            console.error('Hidden names field not found');
        }
    }

    //var courseId = getQueryParam('course');
    // var courseId = ideCurso.value;
    // console.log(courseId);

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

        if (moduleId && !arraySelectedModuleId.includes(moduleId) && !arraySelectedModuleName.includes(moduleName)) {
            arraySelectedModuleId.push(moduleId);
            arraySelectedModuleName.push(moduleName);

            updateHiddenIdsField();
            updateHiddenNamesField();

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
                arraySelectedModuleId = arraySelectedModuleId.filter(function (id) {
                    return id !== moduleId;
                });

                arraySelectedModuleName = arraySelectedModuleName.filter(function (name) {
                    return name !== moduleName;
                });

                updateHiddenIdsField();
                updateHiddenNamesField();

                this.value = '';
            };

            updateHiddenIdsField();
            updateHiddenNamesField();

            selectedModule.appendChild(deleteButton);
            selectedModulesContainer.appendChild(selectedModule);

            this.value = '';
        }
        this.value = '';
    });
});