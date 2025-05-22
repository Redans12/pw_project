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

    // Navegación para las tarjetas del menú
    const cards = document.querySelectorAll(".card");
    cards.forEach(card => {
        card.addEventListener("click", function() {
            const categoria = this.querySelector("h4").textContent;
            let pagina = "";
            
            switch(categoria) {
                case "ENTRADAS":
                    pagina = "entradas.html";
                    break;
                case "ENSALADAS":
                    pagina = "ensaladas.html";
                    break;
                case "CONTEMPORÁNEO":
                    pagina = "contemporaneo.html";
                    break;
                case "PREHISPÁNICO":
                    pagina = "prehispanico.html";
                    break;
                case "EXÓTICO":
                    pagina = "exotico.html";
                    break;
                case "COCTELERÍA":
                    pagina = "cocteleria.html";
                    break;
                case "BEBIDAS":
                    pagina = "bebidas.html";
                    break;
                case "POSTRES":
                    pagina = "postres.html";
                    break;
                default:
                    console.log("Categoría no encontrada");
                    return;
            }
            
            window.location.href = pagina;
        });
    });
});