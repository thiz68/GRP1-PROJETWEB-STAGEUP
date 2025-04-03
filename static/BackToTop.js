document.addEventListener("DOMContentLoaded", function () {
    const backToTopButton = document.getElementById("backToTop");

    if (backToTopButton) {
        // Masquer le bouton au chargement de la page
        backToTopButton.classList.remove("show");

        window.addEventListener("scroll", function () {
            if (window.scrollY > 300) { // Affiche le bouton après 300px de scroll
                backToTopButton.classList.add("show");
            } else {
                backToTopButton.classList.remove("show");
            }
        });

        backToTopButton.addEventListener("click", function () {
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        });
    }
});