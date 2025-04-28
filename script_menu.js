document.addEventListener("DOMContentLoaded", function() {
    const enlaces = document.querySelectorAll("nav ul li a");
    enlaces.forEach(enlace => {
        enlace.addEventListener("click", function(e) {
            e.preventDefault();
            const seccion = document.querySelector(this.getAttribute("href"));
            if (seccion) {
                window.scrollTo({
                    top: seccion.offsetTop - 50,
                    behavior: "smooth"
                });
            }
        });
    });
});