/**
 * Scroll animations, kept light: one IntersectionObserver for the whole page, and the
 * fade itself is a CSS transition on opacity and transform (handled by the GPU, no
 * JavaScript per frame). See the .reveal-* rules in resources/css/app.css.
 *
 *   data-reveal                       the element fades up when it scrolls into view
 *   data-reveal data-reveal-stagger   each child fades up, those in one row one after another
 *
 * Nothing can stay hidden: when an item comes into view, every item before it on the page
 * is shown too (dragging the scrollbar jumps past sections without them ever "entering"
 * the screen). The inline script in the layout skips all of this for reduced motion.
 */
export function initReveal() {
    const items = [];
    document.querySelectorAll('[data-reveal]').forEach((el) => {
        if (el.hasAttribute('data-reveal-stagger')) {
            [...el.children].forEach((child, i) => {
                child.style.setProperty('--reveal-delay', `${(i % 4) * 80}ms`);
                items.push(child);
            });
        } else {
            items.push(el);
        }
    });

    const show = (el, animate) => {
        if (el.classList.contains('is-revealed')) return;
        if (!animate) el.style.setProperty('--reveal-delay', '0ms');
        el.classList.add('reveal-anim');
        // Let the browser register the start state, then flip to the end state.
        requestAnimationFrame(() => el.classList.add('is-revealed'));
        const done = (e) => {
            if (e.target !== el || e.propertyName !== 'opacity') return; // ignore transitions inside it
            el.classList.remove('reveal-anim'); // hand transitions back to the element's own styles
            el.removeEventListener('transitionend', done);
        };
        el.addEventListener('transitionend', done);
        observer.unobserve(el);
    };

    let next = 0; // items before this index are all shown
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            const index = items.indexOf(entry.target);
            for (; next < index; next++) show(items[next], false); // skipped past: show at once
            show(entry.target, true);
            if (index === next) next++;
        });
    }, { rootMargin: '0px 0px -8% 0px' });

    items.forEach((el) => observer.observe(el));

    // Catch-up for jumps that land where no item is in view (e.g. the footer): once per frame at
    // most, show every item whose top is already above the bottom of the screen.
    let queued = false;
    const catchUp = () => {
        queued = false;
        while (next < items.length && items[next].getBoundingClientRect().top < innerHeight) show(items[next++], false);
        if (next >= items.length) removeEventListener('scroll', onScroll);
    };
    const onScroll = () => { if (!queued) { queued = true; requestAnimationFrame(catchUp); } };
    addEventListener('scroll', onScroll, { passive: true });

    window.__revealStarted = true;
}
