// Entrada en escena de elementos [data-reveal]: sutil, una sola vez y a prueba de fallos.
// Sin JavaScript el contenido queda visible; con JavaScript, nada permanece oculto
// más de ~2s aunque el observer no llegue a dispararse.
export function initReveal() {
    const items = document.querySelectorAll('[data-reveal]');

    if (!items.length) {
        return;
    }

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) {
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-revealed');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -5% 0px' });

    items.forEach((item, index) => {
        // El estado oculto solo existe cuando JS está activo.
        item.classList.add('reveal-pending');
        // Escalonado corto entre vecinos; nunca retrasa más de ~300ms.
        item.style.setProperty('--reveal-delay', `${(index % 6) * 60}ms`);
        observer.observe(item);
    });

    // Red de seguridad: nada queda oculto aunque el observer falle.
    window.setTimeout(() => {
        items.forEach((item) => item.classList.add('is-revealed'));
    }, 1800);
}
