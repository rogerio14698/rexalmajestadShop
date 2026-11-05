import './bootstrap';
import { initCarousel } from './carousel';
import { initMenu } from './menu';
import { initCookieNotice } from './cookie-notice';
import { initReveal } from './reveal';

document.addEventListener('DOMContentLoaded', () => {
    initCarousel();
    initMenu();
    initCookieNotice();
    initReveal();

    if (document.querySelector('[data-orb-scene]')) {
        void import('./scene').then(({ initOrbScene }) => initOrbScene());
    }
});
