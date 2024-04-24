// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * JavaScript logic for dynamically updating module instance dropdown
 * based on selected module type and managing selected modules list.
 * 
 * @package     mod_page
 * @copyright   2024 Nikolay <nikolaypn2002@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

document.addEventListener('DOMContentLoaded', function () {
    // Initialization of DOM elements required for dropdown functionality.
    var moduleTypeSelect = document.getElementById('id_moduletype');
    var moduleInstanceSelect = document.getElementById('id_moduleinstance');
    var selectedModulesContainer = document.getElementById('selected-modules-container');
    var courseId = document.getElementById('courseid').value;

    // Arrays to store selected module IDs and names to prevent duplicates.
    var arraySelectedModuleName = [];
    var arraySelectedModuleId = [];

    // Event listener to update module instances dropdown when module type changes.
    moduleTypeSelect.addEventListener('change', function () {
        var type = this.value;

        if (type) {
            // Construct AJAX URL for fetching module instances based on the selected module type and course ID.
            var ajaxurl = M.cfg.wwwroot + '/mod/page/get_activities.php?type=' + encodeURIComponent(type) + '&courseid=' + courseId;

            // Perform AJAX request to fetch module instances.
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

    // Event listener to handle selection of module instances.
    moduleInstanceSelect.addEventListener('change', function () {
        var moduleId = this.value;
        var moduleName = this.options[this.selectedIndex].text;

        // Check if the module is already selected; prevent duplicates in the UI.
        if (moduleId && !arraySelectedModuleId.includes(moduleId) && !arraySelectedModuleName.includes(moduleName)) {
            // Add module ID and name to tracking arrays.
            arraySelectedModuleId.push(moduleId);
            arraySelectedModuleName.push(moduleName);

            // Create a new div element for displaying the selected module.
            var selectedModule = document.createElement('div');
            selectedModule.className = 'selected-module';
            selectedModule.textContent = moduleName;
            selectedModule.setAttribute('data-module-id', moduleId);

            // Create a delete button for removing the module from selection.
            var deleteButton = document.createElement('button');
            deleteButton.textContent = '×';
            deleteButton.className = 'delete-module-button';
            deleteButton.type = 'button';
            deleteButton.onclick = function () {
                // Remove the module div from the container and update the arrays.
                selectedModulesContainer.removeChild(selectedModule);
                arraySelectedModuleId = arraySelectedModuleId.filter(function (id) {
                    return id !== moduleId;
                });
                arraySelectedModuleName = arraySelectedModuleName.filter(function (name) {
                    return name !== moduleName;
                });

                this.value = '';
            };

            // Append delete button to the module div and the module div to the container.
            selectedModule.appendChild(deleteButton);
            selectedModulesContainer.appendChild(selectedModule);

            this.value = '';
        }

        // Reset the dropdown to default after selection to be ready for a new selection.
        this.value = '';
    });
});