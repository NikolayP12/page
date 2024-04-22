document.addEventListener('DOMContentLoaded', function () {
    // Initialize DOM elements.
    var moduleTypeSelect = document.getElementById('id_moduletype');
    var moduleInstanceSelect = document.getElementById('id_moduleinstance');
    var selectedModulesContainer = document.getElementById('selected-modules-container');
    var courseId = document.getElementById('courseid').value;

    // Store selected module IDs and names.
    var arraySelectedModuleName = [];
    var arraySelectedModuleId = [];

    // Fetch and update module instance options on module type change.
    moduleTypeSelect.addEventListener('change', function () {
        var type = this.value;

        if (type) {
            // Construct and send the AJAX request.
            var ajaxurl = M.cfg.wwwroot + '/mod/page/get_activities.php?type=' + encodeURIComponent(type) + '&courseid=' + courseId;
            fetch(ajaxurl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    // Clear existing options and add a default 'Select an activity' option.
                    moduleInstanceSelect.innerHTML = '';
                    var defaultOption = new Option('Select an activity', '');
                    moduleInstanceSelect.add(defaultOption);

                    // Ensures that data.data exists and is an array of activities.
                    if (data.data && Array.isArray(data.data)) {
                        // Fills the module instance select with fetched activities.
                        data.data.forEach(activity => {
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

    // Handles the selection of a module instance.
    moduleInstanceSelect.addEventListener('change', function () {
        var moduleId = this.value;
        var moduleName = this.options[this.selectedIndex].text;

        if (moduleId && !arraySelectedModuleId.includes(moduleId) && !arraySelectedModuleName.includes(moduleName)) {
            arraySelectedModuleId.push(moduleId);
            arraySelectedModuleName.push(moduleName);

            var selectedModule = document.createElement('div');
            selectedModule.className = 'selected-module';
            selectedModule.textContent = moduleName;
            selectedModule.setAttribute('data-module-id', moduleId);

            // Creates and adds the delete button.
            var deleteButton = document.createElement('button');
            deleteButton.textContent = '×';
            deleteButton.className = 'delete-module-button';
            deleteButton.type = 'button';
            deleteButton.onclick = function () {
                // Removes the module from UI and selection arrays.
                selectedModulesContainer.removeChild(selectedModule);
                arraySelectedModuleId = arraySelectedModuleId.filter(function (id) {
                    return id !== moduleId;
                });

                arraySelectedModuleName = arraySelectedModuleName.filter(function (name) {
                    return name !== moduleName;
                });

                this.value = '';
            };

            selectedModule.appendChild(deleteButton);
            selectedModulesContainer.appendChild(selectedModule);

            this.value = '';
        }
        this.value = '';
    });
});