import { animate, inView } from 'motion';

/**
 * Scroll animations (Motion, the framework-free version of Framer Motion).
 *
 *   data-reveal                       the element fades up when it scrolls into view
 *   data-reveal data-reveal-stagger   each child fades up in turn as it scrolls into view
 *
 * The inline script in the layout adds `reveal-ready` to <html> so CSS can hide these
 * before the first paint (no flash). People who ask for reduced motion, or a browser
 * where this script fails, simply see everything at once.
 */
const EASE = [0.22, 1, 0.36, 1];

function reveal(el, delay = 0) {
    inView(el, () => {
        animate(el, { opacity: [0, 1], y: [28, 0] }, { duration: 0.7, delay, ease: EASE });
    }, { amount: 0.15, margin: '0px 0px -40px 0px' });
}

export function initReveal() {
    document.querySelectorAll('[data-reveal]').forEach((el) => {
        if (el.hasAttribute('data-reveal-stagger')) {
            // Children in the same row come in one after another; each waits for its own scroll.
            [...el.children].forEach((child, i) => reveal(child, (i % 4) * 0.09));
        } else {
            reveal(el);
        }
    });
    window.__revealStarted = true;
}
