import Carousel from 'bootstrap/js/dist/carousel';

export function initCarousel() {
    document.querySelectorAll('[data-bs-ride="carousel"]').forEach((element) => {
        Carousel.getOrCreateInstance(element, {
            interval: false,
            keyboard: true,
            pause: true,
            ride: false,
            touch: true,
            wrap: true,
        });
    });
}
