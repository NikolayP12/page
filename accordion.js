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
 * JavaScript logic for dynamically expand the email form
 * 
 * @package     mod_page
 * @copyright   2024 Nikolay <nikolaypn2002@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Add event listener for DOM content loaded to ensure all HTML elements are fully loaded.
document.addEventListener("DOMContentLoaded", function () {
    // Select all elements with the class 'accordion-title'.
    var accordionTitles = document.querySelectorAll(".accordion-title");

    // Loop through each accordion title to apply functionality.
    accordionTitles.forEach(function (accordionTitle) {
        // Get the next element sibling, expected to be the accordion content.
        var accordionContent = accordionTitle.nextElementSibling;

        // Initially hide the accordion content.
        accordionContent.style.display = "none";
        // Remove 'open' class initially for all accordion titles.
        accordionTitle.classList.remove("open");

        // Add click event listener to each accordion title.
        accordionTitle.addEventListener("click", function () {
            // Toggle display based on current visibility of the accordion content.
            var isHidden = accordionContent.style.display === "none";
            accordionContent.style.display = isHidden ? "block" : "none";
            // Toggle 'open' class on the accordion title to indicate whether it's expanded or collapsed.
            accordionTitle.classList.toggle("open");
        });
    });
});