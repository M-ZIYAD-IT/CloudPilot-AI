const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

document.addEventListener('DOMContentLoaded', function () {
    initCounter();
    initScrollReveal();
    initTiltCards();
    initCardDeck();
});

function initCounter() {
    const counter = document.querySelector('[data-count-to]');
    if (!counter) return;

    const target = parseInt(counter.dataset.countTo, 10);
    if (prefersReducedMotion) {
        counter.textContent = target;
        return;
    }

    const duration = 1100;
    const start = performance.now();
    const tick = (now) => {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        counter.textContent = Math.round(eased * target);
        if (progress < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
}

function initScrollReveal() {
    const revealTargets = document.querySelectorAll('.anim-reveal');
    if (revealTargets.length === 0) return;

    if (prefersReducedMotion || !('IntersectionObserver' in window)) {
        revealTargets.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.15, rootMargin: '0px 0px -40px 0px' }
    );

    revealTargets.forEach((el) => observer.observe(el));
}

// 3D tilt: rotation tracks cursor position within the card, capped to a
// subtle max angle (overridable per-card via data-tilt-max), reset with a
// spring-like ease on mouseleave. Cards inside a card deck only tilt while
// they're the active (front-facing) card — the deck controller owns their
// resting transform otherwise, and the two would fight over it.
function initTiltCards() {
    if (prefersReducedMotion) return;

    const DEFAULT_MAX_TILT_DEG = 10;

    document.querySelectorAll('[data-tilt]').forEach((card) => {
        const maxTilt = parseFloat(card.dataset.tiltMax) || DEFAULT_MAX_TILT_DEG;
        card.style.transitionDuration = '0.5s';

        const isDeckCard = () => {
            const deckParent = card.closest('.deck-card');
            return deckParent && !deckParent.classList.contains('is-active');
        };

        card.addEventListener('mouseenter', () => {
            if (isDeckCard()) return;
            card.style.transitionDuration = '0.1s';
        });

        card.addEventListener('mousemove', (event) => {
            if (isDeckCard()) return;

            const rect = card.getBoundingClientRect();
            const px = (event.clientX - rect.left) / rect.width;
            const py = (event.clientY - rect.top) / rect.height;

            const rotateY = (px - 0.5) * maxTilt * 2;
            const rotateX = (0.5 - py) * maxTilt * 2;

            card.style.transform = `perspective(800px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(0)`;
            card.style.setProperty('--shine-x', `${px * 100}%`);
            card.style.setProperty('--shine-y', `${py * 100}%`);
        });

        card.addEventListener('mouseleave', () => {
            card.style.transitionDuration = '0.5s';
            card.style.transform = 'perspective(800px) rotateX(0deg) rotateY(0deg) translateZ(0)';
        });
    });
}

// Card deck: a stack of cards in 3D, one in focus and the rest fanned out
// above/below it — blurred, scaled down, receding in depth. Every step wraps
// via modulo, so advancing past the last card continues straight to the
// first (and back past the first continues to the last) — scrolling never
// dead-ends and has to reverse. Click a background card, use the prev/next
// buttons, the dots, or drag to step; autoplay resets its cooldown timer
// after any manual step.
function initCardDeck() {
    const deck = document.getElementById('card-deck');
    if (!deck) return;

    const cards = Array.from(deck.querySelectorAll('.deck-card'));
    const n = cards.length;
    if (n === 0) return;

    if (prefersReducedMotion) deck.classList.add('no-anim');

    let active = 0;
    let autoplayTimer = null;

    const dotsHost = document.querySelector('[data-deck-dots]');
    const dots = [];
    if (dotsHost) {
        cards.forEach((_, i) => {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'deck-dot';
            dot.setAttribute('aria-label', `Show card ${i + 1} of ${n}`);
            dot.addEventListener('click', () => goTo(i));
            dotsHost.appendChild(dot);
            dots.push(dot);
        });
    }

    function render() {
        cards.forEach((card, i) => {
            let offset = i - active;
            const half = Math.floor(n / 2);
            if (offset > half) offset -= n;
            if (offset < -half) offset += n;

            const abs = Math.abs(offset);
            const y = offset * 34;
            const z = -abs * 60;
            const rotateX = offset * 7;
            const scale = 1 - abs * 0.1;
            const opacity = abs === 0 ? 1 : abs === 1 ? 0.55 : 0.28;
            const blurPx = abs === 0 ? 0 : abs === 1 ? 3 : 5;

            card.style.transform = `translate(-50%, -50%) translate3d(0, ${y}px, ${z}px) rotateX(${rotateX}deg) scale(${scale})`;
            card.style.opacity = String(opacity);
            card.style.filter = blurPx ? `blur(${blurPx}px)` : 'none';
            card.style.zIndex = String(50 - abs * 10);
            card.classList.toggle('is-active', abs === 0);
            card.setAttribute('aria-hidden', abs === 0 ? 'false' : 'true');
        });

        dots.forEach((dot, i) => dot.classList.toggle('is-active', i === active));
    }

    function goTo(i) {
        active = ((i % n) + n) % n;
        render();
        resetAutoplay();
    }

    function next() {
        goTo(active + 1);
    }

    function prev() {
        goTo(active - 1);
    }

    cards.forEach((card, i) => {
        card.addEventListener('click', () => {
            if (i !== active) goTo(i);
        });
    });

    document.querySelector('[data-deck-prev]')?.addEventListener('click', prev);
    document.querySelector('[data-deck-next]')?.addEventListener('click', next);

    let dragStartY = null;
    deck.addEventListener('pointerdown', (event) => {
        dragStartY = event.clientY;
    });
    deck.addEventListener('pointerup', (event) => {
        if (dragStartY === null) return;
        const dy = event.clientY - dragStartY;
        if (Math.abs(dy) > 40) {
            dy < 0 ? next() : prev();
        }
        dragStartY = null;
    });

    function startAutoplay() {
        if (prefersReducedMotion) return;
        stopAutoplay();
        autoplayTimer = setInterval(next, 3800);
    }

    function stopAutoplay() {
        if (autoplayTimer) clearInterval(autoplayTimer);
    }

    function resetAutoplay() {
        if (prefersReducedMotion) return;
        startAutoplay();
    }

    deck.addEventListener('mouseenter', stopAutoplay);
    deck.addEventListener('mouseleave', startAutoplay);
    deck.addEventListener('focusin', stopAutoplay);
    deck.addEventListener('focusout', startAutoplay);

    render();
    startAutoplay();
}
