const focusableSelector = 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';

export function initMenu() {
    const trigger = document.querySelector('[data-menu-trigger]');
    const dialog = document.querySelector('[data-menu-dialog]');
    const page = document.querySelector('[data-menu-background]');

    if (!trigger || !dialog) {
        return;
    }

    let previousFocus = null;
    let animation;

    const animate = async (opening) => {
        const { gsap } = await import('gsap');

        animation?.kill();
        animation = gsap.timeline();

        if (opening) {
            animation.fromTo(
                dialog.querySelectorAll('[data-menu-item]'),
                { autoAlpha: 0, y: 18 },
                { autoAlpha: 1, y: 0, duration: 0.38, stagger: 0.045, ease: 'power2.out' },
            );
        } else {
            animation.to(dialog.querySelectorAll('[data-menu-item]'), {
                autoAlpha: 0,
                y: 10,
                duration: 0.18,
                stagger: 0.015,
                ease: 'power1.in',
            });
        }
    };

    const open = () => {
        if (trigger.getAttribute('aria-expanded') === 'true') {
            return;
        }

        previousFocus = document.activeElement;
        dialog.hidden = false;
        dialog.setAttribute('aria-hidden', 'false');
        trigger.setAttribute('aria-expanded', 'true');
        trigger.setAttribute('aria-label', 'Cerrar menú');
        page?.setAttribute('inert', '');
        document.body.classList.add('menu-is-open');
        dialog.querySelector(focusableSelector)?.focus();
        void animate(true);
    };

    const close = () => {
        if (trigger.getAttribute('aria-expanded') !== 'true') {
            return;
        }

        trigger.setAttribute('aria-expanded', 'false');
        trigger.setAttribute('aria-label', 'Abrir menú');
        dialog.setAttribute('aria-hidden', 'true');
        page?.removeAttribute('inert');
        document.body.classList.remove('menu-is-open');

        const finishClose = () => {
            dialog.hidden = true;
            previousFocus?.focus();
            previousFocus = null;
        };

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            finishClose();
        } else {
            void animate(false).then(finishClose);
        }
    };

    // El mismo botón abre y cierra: nunca hay dos botones superpuestos.
    trigger.addEventListener('click', () => {
        trigger.getAttribute('aria-expanded') === 'true' ? close() : open();
    });
    dialog.querySelectorAll('a[href]').forEach((link) => link.addEventListener('click', close));

    document.addEventListener('keydown', (event) => {
        if (trigger.getAttribute('aria-expanded') !== 'true') {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            close();
        } else if (event.key === 'Tab') {
            const focusable = [...dialog.querySelectorAll(focusableSelector)];
            const first = focusable[0];
            const last = focusable.at(-1);

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last?.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first?.focus();
            }
        }
    });

    document.querySelectorAll('[data-open-menu]').forEach((element) => {
        element.addEventListener('click', open);
        element.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                open();
            }
        });
    });
}
