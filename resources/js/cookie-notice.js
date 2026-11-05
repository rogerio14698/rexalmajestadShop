export function initCookieNotice() {
    const notice = document.querySelector('[data-cookie-notice]');
    const dismiss = notice?.querySelector('[data-cookie-dismiss]');

    if (!notice || !dismiss) return;

    if (localStorage.getItem('rex-cookie-notice-dismissed') === '1') {
        notice.hidden = true;
        return;
    }

    notice.hidden = false;
    dismiss.addEventListener('click', () => {
        localStorage.setItem('rex-cookie-notice-dismissed', '1');
        notice.hidden = true;
    });
}
