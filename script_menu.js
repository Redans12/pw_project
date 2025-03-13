
let currentSlide = {
    platillos: 0,
    postres: 0,
    bebidas: 0
};

function showTab(tabId) {
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => {
        tab.style.display = 'none';
    });
    document.getElementById(tabId).style.display = 'block';
    showSlide(tabId, currentSlide[tabId]);
}

function showSlide(tabId, slideIndex) {
    const carousel = document.querySelector(`#${tabId} .carousel`);
    const items = carousel.querySelectorAll('.carousel-item');
    const totalSlides = items.length;

    if (slideIndex >= totalSlides) {
        currentSlide[tabId] = 0;
    } else if (slideIndex < 0) {
        currentSlide[tabId] = totalSlides - 1;
    }

    const offset = -currentSlide[tabId] * 100;
    carousel.style.transform = `translateX(${offset}%)`;
}

function nextSlide(tabId) {
    currentSlide[tabId]++;
    showSlide(tabId, currentSlide[tabId]);
}

function prevSlide(tabId) {
    currentSlide[tabId]--;
    showSlide(tabId, currentSlide[tabId]);
}

showTab('platillos');  // Mostrar la primera pestaña por defecto
