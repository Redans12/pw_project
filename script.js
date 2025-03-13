let slideIndex = 0;
const slides = document.querySelectorAll(".slide");
const totalSlides = slides.length;

function showSlide(index) {
    slides.forEach((slide, i) => {
        slide.classList.remove("active");
        if (i === index) {
            slide.classList.add("active");
        }
    });
}

function moveSlide(step) {
    slideIndex += step;
    if (slideIndex >= totalSlides) {
        slideIndex = 0;
    } else if (slideIndex < 0) {
        slideIndex = totalSlides - 1;
    }
    showSlide(slideIndex);
}

// Cambio automático de imágenes cada 3 segundos
setInterval(() => {
    moveSlide(1);
}, 5000);

// Mostrar la primera imagen al cargar la página
showSlide(slideIndex);

function scrollToSection(event, sectionId) {
    event.preventDefault();
    const section = document.getElementById(sectionId);
    const navbarHeight = document.getElementById('navbar').offsetHeight;
    const sectionTop = section.offsetTop - navbarHeight;
    window.scrollTo({
        top: sectionTop,
        behavior: 'smooth'
    });
}

function loadMenu(event) {
    event.preventDefault();
    const menuContent = document.getElementById('menu-content');
    fetch('menu.html')
        .then(response => response.text())
        .then(data => {
            menuContent.innerHTML = data;
            window.scrollTo({
                top: menuContent.offsetTop - document.getElementById('navbar').offsetHeight,
                behavior: 'smooth'
            });
        })
        .catch(error => console.error('Error al cargar el menú:', error));
}
