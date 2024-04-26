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
 * JavaScript logic for local storing the body of the message that the student
 * wanted to send to the teacher.
 * 
 * @package     mod_page
 * @copyright   2024 Nikolay <nikolaypn2002@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
document.addEventListener('DOMContentLoaded', function () {
    // Retrieves the URL's successful send parameter.
    const urlParams = new URLSearchParams(window.location.search);
    const emailSent = urlParams.get('emailSent') === 'true';
    // Elementos del formulario.
    const messageBody = document.getElementById('messagebody');
    const moduleId = document.getElementById('pageid').value;
    const userId = document.getElementById('userid').value;

    // Generates a unique key for the localStorage using the module ID and the user ID.
    const localStorageKey = `messageBody-${moduleId}-${userId}`;

    if (emailSent) {
        localStorage.removeItem(localStorageKey); // Clears local storage if the shipment was successful
    } else {
        // Loads the saved message from localStorage if sending was unsuccessful
        const savedMessage = localStorage.getItem(localStorageKey);
        if (savedMessage) {
            messageBody.value = savedMessage;
        }
    }

    // Save the message in localStorage by typing
    messageBody.addEventListener('input', function () {
        localStorage.setItem(localStorageKey, messageBody.value);
    });

});