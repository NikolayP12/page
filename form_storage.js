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
// document.addEventListener('DOMContentLoaded', function () {
//     // Retrieves the URL's successful send parameter.
//     const urlParams = new URLSearchParams(window.location.search);
//     const emailSent = urlParams.get('emailSent') === 'true';
//     // Elementos del formulario.
//     const messageBody = document.getElementById('messagebody');
//     const moduleId = document.getElementById('pageid').value;
//     const userId = document.getElementById('userid').value;

//     // Generates a unique key for the localStorage using the module ID and the user ID.
//     const localStorageKey = `messageBody-${moduleId}-${userId}`;

//     if (emailSent) {
//         localStorage.removeItem(localStorageKey); // Clears local storage if the shipment was successful
//     } else {
//         // Loads the saved message from localStorage if sending was unsuccessful
//         const savedMessage = localStorage.getItem(localStorageKey);
//         if (savedMessage) {
//             messageBody.value = savedMessage;
//         }
//     }

//     // Save the message in localStorage by typing
//     messageBody.addEventListener('input', function () {
//         localStorage.setItem(localStorageKey, messageBody.value);
//     });

// });

document.addEventListener('DOMContentLoaded', function () {
    // Retrieve elements from the form
    const messageBody = document.getElementById('messagebody');
    const teacherEmail = document.getElementById('teacheremail');
    const subject = document.getElementById('subject');
    const moduleId = document.getElementById('pageid').value;
    const userId = document.getElementById('userid').value;

    // Generate unique keys for localStorage for each field using the module ID and the user ID
    const messageKey = `messageBody-${moduleId}-${userId}`;
    const emailKey = `teacherEmail-${moduleId}-${userId}`;
    const subjectKey = `subject-${moduleId}-${userId}`;

    // Function to load saved data
    function loadSavedData(key, element) {
        const savedData = localStorage.getItem(key);
        if (savedData) {
            element.value = savedData;
        }
    }

    // Function to save data to localStorage
    function saveToLocalStorage(key, value) {
        localStorage.setItem(key, value);
    }

    // Check if the form was sent successfully
    const urlParams = new URLSearchParams(window.location.search);
    const emailSent = urlParams.get('emailSent') === 'true';

    if (emailSent) {
        // Clears local storage if the shipment was successful
        localStorage.removeItem(messageKey);
        localStorage.removeItem(emailKey);
        localStorage.removeItem(subjectKey);
    } else {
        // Loads the saved message from localStorage if sending was unsuccessful
        loadSavedData(messageKey, messageBody);
        loadSavedData(emailKey, teacherEmail);
        loadSavedData(subjectKey, subject);
    }

    // Event listeners for form fields to save data as the user types
    messageBody.addEventListener('input', () => saveToLocalStorage(messageKey, messageBody.value));
    teacherEmail.addEventListener('input', () => saveToLocalStorage(emailKey, teacherEmail.value));
    subject.addEventListener('input', () => saveToLocalStorage(subjectKey, subject.value));
});