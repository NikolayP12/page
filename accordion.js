document.addEventListener("DOMContentLoaded", function () {
    var accordionTitles = document.querySelectorAll(".accordion-title");

    accordionTitles.forEach(function (accordionTitle) {
        var accordionContent = accordionTitle.nextElementSibling;

        accordionContent.style.display = "none";
        accordionTitle.classList.remove("open");

        accordionTitle.addEventListener("click", function () {
            var isHidden = accordionContent.style.display === "none";
            accordionContent.style.display = isHidden ? "block" : "none";
            accordionTitle.classList.toggle("open");
        });
    });
});