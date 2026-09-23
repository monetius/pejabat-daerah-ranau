// Pejabat Daerah Ranau — shared site script

async function loadInclude(placeholderId, url) {
  const el = document.getElementById(placeholderId);
  if (!el) return;
  // window.SITE_BASE is set per-page in a small inline <script> before this
  // file loads — "" for a domain root deploy, "/repo-name" for a GitHub
  // Pages project site served from a subpath.
  const base = window.SITE_BASE || '';
  const fullUrl = base + url;
  try {
    const res = await fetch(fullUrl);
    if (!res.ok) throw new Error(`${fullUrl} responded ${res.status}`);
    el.outerHTML = await res.text(); // replace the placeholder itself, not just its contents
  } catch (err) {
    // If this page is opened straight from disk (file://) fetch() is blocked by
    // the browser's CORS rules and this will fail — the site needs to be served
    // over http(s) (any static host) for the shared navbar/footer to load.
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

function initTextSize() {
  const buttons = document.querySelectorAll('.access-tools button[data-size]');
  if (!buttons.length) return;

  const STORAGE_KEY = 'pdr-text-size';
  const root = document.documentElement;

  const applySize = (size) => {
    if (size === 'base') {
      root.removeAttribute('data-size');
    } else {
      root.setAttribute('data-size', size);
    }
    buttons.forEach((btn) => {
      const isActive = btn.dataset.size === size;
      btn.classList.toggle('is-active', isActive);
      btn.setAttribute('aria-pressed', String(isActive));
    });
  };

  let saved = 'base';
  try {
    saved = localStorage.getItem(STORAGE_KEY) || 'base';
  } catch (err) {
    // localStorage unavailable (private browsing, etc.) — fall back to 'base'
  }
  applySize(saved);

  buttons.forEach((btn) => {
    btn.addEventListener('click', () => {
      const size = btn.dataset.size;
      applySize(size);
      try {
        localStorage.setItem(STORAGE_KEY, size);
      } catch (err) {
        // ignore write failures
      }
    });
  });
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

document.addEventListener('DOMContentLoaded', async () => {
  // Nav and footer are shared fragments fetched from one file each — see
  // /assets/includes/navbar.html and /assets/includes/footer.html.
  // Everything that touches nav elements has to wait until this resolves.
  await Promise.all([
    loadInclude('navbar-placeholder', '/assets/includes/navbar.html'),
    loadInclude('footer-placeholder', '/assets/includes/footer.html'),
  ]);

  initNavbar();
  initTextSize();
  initHeroParallax();
});
