// Pejabat Daerah Ranau — shared site script

async function loadInclude(placeholderId, url) {
  const el = document.getElementById(placeholderId);
  if (!el) return;
  const base = window.SITE_BASE || '';
  const fullUrl = base + url;
  try {
    const res = await fetch(fullUrl);
    if (!res.ok) throw new Error(`${fullUrl} responded ${res.status}`);
    el.outerHTML = await res.text(); 
  } catch (err) {

    console.error('Could not load', fullUrl, err);
  }
}

function initNavbar() {
  const navbar = document.querySelector('.navbar');
  const hamburger = document.getElementById('hamburger');
  const navLinks = document.getElementById('navLinks');
  const dropdowns = document.querySelectorAll('.dropdown');
  const isMobile = () => window.matchMedia('(max-width: 860px)').matches;

  if (hamburger && navLinks) {
    hamburger.addEventListener('click', () => {
      const isOpen = navLinks.classList.toggle('active');
      hamburger.setAttribute('aria-expanded', String(isOpen));
    });
  }

  dropdowns.forEach((dropdown) => {
    const toggle = dropdown.querySelector('.dropdown-toggle');
    const menu = dropdown.querySelector('.dropdown-menu');
    if (!toggle || !menu) return;

    toggle.addEventListener('click', () => {
      if (!isMobile()) return;
      const isOpen = menu.classList.toggle('active');
      dropdown.classList.toggle('active', isOpen);
      toggle.setAttribute('aria-expanded', String(isOpen));
    });
  });

  document.addEventListener('click', (e) => {
    if (!isMobile()) return;
    dropdowns.forEach((dropdown) => {
      if (!dropdown.contains(e.target)) {
        const menu = dropdown.querySelector('.dropdown-menu');
        const toggle = dropdown.querySelector('.dropdown-toggle');
        if (menu) menu.classList.remove('active');
        dropdown.classList.remove('active');
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
      }
    });
  });

  const currentPath = window.location.pathname.replace(/\/index\.html$/, '/');
  document.querySelectorAll('.nav-links a').forEach((link) => {
    const linkPath = new URL(link.href, window.location.href).pathname.replace(/\/index\.html$/, '/');
    if (linkPath === currentPath) link.classList.add('is-active');
  });

  if (navbar) {
    const onScroll = () => navbar.classList.toggle('is-condensed', window.scrollY > 40);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }
}

function initHeroParallax() {
  const heroMedia = document.querySelector('.hero-media');
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!heroMedia || prefersReducedMotion) return;

  const hero = document.querySelector('.hero');
  let ticking = false;

  const updateParallax = () => {
    const rect = hero.getBoundingClientRect();
    if (rect.bottom > 0 && rect.top < window.innerHeight) {
      heroMedia.style.transform = `translate3d(0, ${rect.top * 0.35}px, 0)`;
    }
    ticking = false;
  };

  window.addEventListener('scroll', () => {
    if (!ticking) {
      window.requestAnimationFrame(updateParallax);
      ticking = true;
    }
  }, { passive: true });

  updateParallax();
}

function initLanguageSwitch() {
  const toggle = document.getElementById('langToggle');
  const menu = document.getElementById('langMenu');
  if (!toggle || !menu) return;

  toggle.addEventListener('click', (e) => {
    e.stopPropagation();
    const isOpen = menu.classList.toggle('active');
    toggle.setAttribute('aria-expanded', String(isOpen));
  });

  document.addEventListener('click', (e) => {
    if (!menu.contains(e.target) && e.target !== toggle) {
      menu.classList.remove('active');
      toggle.setAttribute('aria-expanded', 'false');
    }
  });

  menu.querySelectorAll('[data-lang]').forEach((btn) => {
    btn.addEventListener('click', () => setSiteLanguage(btn.getAttribute('data-lang')));
  });
}

// Loads Google's page-translation widget in the background and drives it
// through a plain language menu instead of its default UI.
function loadGoogleTranslate() {
  if (document.getElementById('google_translate_element')) return;

  const holder = document.createElement('div');
  holder.id = 'google_translate_element';
  holder.style.display = 'none';
  document.body.appendChild(holder);

  window.googleTranslateElementInit = function () {
    new google.translate.TranslateElement(
      {
        pageLanguage: 'ms',
        includedLanguages: 'ms,en,zh-CN,ta,ko',
        autoDisplay: false,
      },
      'google_translate_element'
    );
  };

  const script = document.createElement('script');
  script.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
  document.body.appendChild(script);
}

function setSiteLanguage(lang) {
  const host = window.location.hostname;
  const clearCookie = (domain) => {
    document.cookie = `googtrans=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;${domain}`;
  };
  const setCookie = (value, domain) => {
    document.cookie = `googtrans=${value}; path=/;${domain}`;
  };

  clearCookie('');
  clearCookie(` domain=${host};`);

  if (lang === 'ms') {
    // Back to the original Bahasa Melayu content — just drop the cookie.
    window.location.reload();
    return;
  }

  const value = `/ms/${lang}`;
  setCookie(value, '');
  setCookie(value, ` domain=${host};`);
  window.location.reload();
}

document.addEventListener('DOMContentLoaded', async () => {
  await Promise.all([
    loadInclude('navbar-placeholder', '/assets/includes/navbar.html'),
    loadInclude('footer-placeholder', '/assets/includes/footer.html'),
  ]);

  initNavbar();
  initHeroParallax();
  initLanguageSwitch();
  loadGoogleTranslate();
});