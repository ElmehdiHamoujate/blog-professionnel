/* ══════════════════════════════
   NAVBAR: shrink on scroll + mobile
══════════════════════════════ */
const navbar    = document.getElementById('navbar');
const navToggle = document.getElementById('navToggle');
const navLinks  = document.querySelector('.nav-links');

window.addEventListener('scroll', () => {
    if (navbar) navbar.classList.toggle('scrolled', window.scrollY > 40);
});

if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
        navLinks.classList.toggle('open');
        navToggle.classList.toggle('open');
    });
    // Close on link click
    navLinks.querySelectorAll('a').forEach(link =>
        link.addEventListener('click', () => {
            navLinks.classList.remove('open');
            navToggle.classList.remove('open');
        })
    );
}

/* ══════════════════════════════
   SCROLL-REVEAL (fade-up)
══════════════════════════════ */
const observer = new IntersectionObserver(
    entries => entries.forEach(e => {
        if (e.isIntersecting) {
            e.target.classList.add('visible');
            observer.unobserve(e.target);
        }
    }),
    { threshold: 0.1, rootMargin: '0px 0px -40px 0px' }
);

document.querySelectorAll(
    '.section-label, .section-title, .section-sub, ' +
    '.about-text, .about-card, .skill-group, ' +
    '.timeline-card, .post-card, .contact-info, .cta-box-inner'
).forEach(el => {
    el.classList.add('fade-up');
    observer.observe(el);
});

/* ══════════════════════════════
   SMOOTH ANCHOR SCROLL
══════════════════════════════ */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', e => {
        const target = document.querySelector(anchor.getAttribute('href'));
        if (target) {
            e.preventDefault();
            const top = target.getBoundingClientRect().top + window.scrollY - 72;
            window.scrollTo({ top, behavior: 'smooth' });
        }
    });
});

/* ══════════════════════════════
   HERO TYPING EFFECT (optional)
══════════════════════════════ */
const heroTitle = document.querySelector('.hero-title');
if (heroTitle) {
    const text   = heroTitle.textContent;
    heroTitle.textContent = '';
    heroTitle.style.visibility = 'visible';
    let i = 0;
    const type = () => {
        if (i < text.length) {
            heroTitle.textContent += text[i++];
            setTimeout(type, 28);
        }
    };
    setTimeout(type, 600);
}

/* ══════════════════════════════
   STATS COUNTER ANIMATION
══════════════════════════════ */
function animateCounter(el) {
    const target = parseInt(el.textContent, 10);
    if (isNaN(target)) return;
    let start = 0;
    const duration = 1200;
    const step = timestamp => {
        if (!start) start = timestamp;
        const progress = Math.min((timestamp - start) / duration, 1);
        el.textContent = Math.floor(progress * target);
        if (progress < 1) requestAnimationFrame(step);
        else el.textContent = target;
    };
    requestAnimationFrame(step);
}

const statsObserver = new IntersectionObserver(entries => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            e.target.querySelectorAll('.stat strong').forEach(animateCounter);
            statsObserver.unobserve(e.target);
        }
    });
}, { threshold: 0.5 });

const statsBlock = document.querySelector('.hero-stats');
if (statsBlock) statsObserver.observe(statsBlock);

/* ══════════════════════════════
   ACTIVE NAV LINK ON SCROLL
══════════════════════════════ */
const sections = document.querySelectorAll('section[id], header[id]');
const navAnchors = document.querySelectorAll('.nav-links a[href^="/#"], .nav-links a[href^="#"]');

window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(s => {
        if (window.scrollY >= s.offsetTop - 100) current = s.id;
    });
    navAnchors.forEach(a => {
        a.classList.remove('nav-active');
        if (a.getAttribute('href').endsWith(current)) a.classList.add('nav-active');
    });
}, { passive: true });
