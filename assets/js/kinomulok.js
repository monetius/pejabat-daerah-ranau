// bikin kasi senang kerja

// Site root, worked out from where this script itself is served
// (assets/js/kinomulok.js -> two levels up). Works at a domain root, under a
// subpath, or on localhost, so no hardcoded folder name is needed anywhere.
const SITE_ROOT = new URL('../../', document.currentScript.src).pathname;

async function loadInclude(placeholderId, url) {
  const el = document.getElementById(placeholderId);
  if (!el) return;
  const fullUrl = SITE_ROOT + url;
  try {
    const res = await fetch(fullUrl);
    if (!res.ok) throw new Error(`${fullUrl} responded ${res.status}`);
    const html = await res.text();
    // Includes write links as {{base}}page.html; swap in the real site root.
    el.outerHTML = html.replaceAll('{{base}}', SITE_ROOT);
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
  const heroMedia = document.querySelector('.hero-media, .page-header-media');
  const heroContent = document.querySelector('.hero-content');
  const heroLayers = document.querySelectorAll('.hero-layer');
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if ((!heroMedia && !heroContent && !heroLayers.length) || prefersReducedMotion) return;

  const hero = document.querySelector('.hero, .page-header');
  let ticking = false;

  const updateParallax = () => {
    const rect = hero.getBoundingClientRect();
    if (rect.bottom > 0 && rect.top < window.innerHeight) {
      if (heroMedia) {
        heroMedia.style.transform = `translate3d(0, ${rect.top * 0.35}px, 0)`;
      }
      // Homepage: tiap lapisan bergerak pada kelajuan berbeza (data-speed)
      // untuk kesan kedalaman; jalur pokok teh kekal tetap di tepi bawah.
      heroLayers.forEach((layer) => {
        const speed = parseFloat(layer.dataset.speed) || 0;
        layer.style.transform = `translate3d(0, ${-rect.top * speed}px, 0)`;
      });
      if (heroContent) {
        // Fades and blurs the hero text as it scrolls up and out of view,
        // fully gone by the time the hero itself scrolls off screen.
        const progress = Math.min(Math.max(-rect.top / rect.height, 0), 1);
        heroContent.style.opacity = String(1 - progress);
        heroContent.style.filter = `blur(${progress * 10}px)`;
      }
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

function initBackToTop() {
  const btn = document.createElement('button');
  btn.type = 'button';
  btn.className = 'back-to-top';
  btn.setAttribute('aria-label', 'Kembali ke atas');
  btn.innerHTML =
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" ' +
    'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"/></svg>';
  document.body.appendChild(btn);

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  btn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
  });

  const onScroll = () => btn.classList.toggle('is-visible', window.scrollY > 400);
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });
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

// -----------------------------------------------------------------------
// | FOR AUTOMATED TIMING LOGIC, DO NOT TOUCH!!!!! |
// ---------------------------------------------------------------------
// Working hours, as [openHour, closeHour] pairs (fractional hours are
// allowed, e.g. 11.5 = 11:30). Rest time is NOT excluded here: the counter
// is shown as "open" all day and only switches to "Rehat" during rest time.
const WEEKDAY_HOURS = [[8, 17]];
const FRIDAY_HOURS = [[8, 17]];

// Rest time, as [startHour, endHour].
// Monday–Thursday 1:00–2:00 pm; Friday 11:30 am–2:00 pm.
const WEEKDAY_REST = [13, 14];
const FRIDAY_REST = [11.5, 14];

function getRestForDay(day) {
  if (day >= 1 && day <= 4) return WEEKDAY_REST;
  if (day === 5) return FRIDAY_REST;
  return null;
}

function getHoursForDay(day) {
  if (day >= 1 && day <= 4) return WEEKDAY_HOURS;
  if (day === 5) return FRIDAY_HOURS;
  return []; // Saturday, Sunday
}

function formatHour(h) {
  const hour = Math.floor(h);
  const minutes = Math.round((h - hour) * 60);
  const displayHour = hour > 12 ? hour - 12 : hour;
  const mm = minutes === 0 ? '00' : String(minutes).padStart(2, '0');
  const period = hour < 12 ? 'pagi' : hour === 12 && minutes === 0 ? 'tengah hari' : 'petang';
  return `${displayHour}:${mm} ${period}`;
}

// -----------------------------------------------------------------------
// | FOR AUTOMATED TIMING LOGIC, DO NOT TOUCH!!!!! |
// ---------------------------------------------------------------------
// regular hours only.
function initOfficeHours() {
  const box = document.getElementById('status');
  const txt = document.getElementById('status-text');

  const dayNames = ['Ahad', 'Isnin', 'Selasa', 'Rabu', 'Khamis', 'Jumaat', 'Sabtu'];
  const weekdayAbbr = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

  const parts = {};
  new Intl.DateTimeFormat('en-US', {
    timeZone: 'Asia/Kuala_Lumpur',
    weekday: 'short',
    hour: 'numeric',
    minute: 'numeric',
    hourCycle: 'h23',
  })
    .formatToParts(new Date())
    .forEach((p) => { parts[p.type] = p.value; });

  const today = weekdayAbbr.indexOf(parts.weekday);
  const now = Number(parts.hour) + Number(parts.minute) / 60;
  let open = null;
  let next = null;

  for (let i = 0; i < 8 && !open && !next; i++) {
    const day = (today + i) % 7;
    const hours = getHoursForDay(day);
    for (const [start, end] of hours) {
      if (i === 0 && now >= start && now < end) { open = end; break; }
      if (i > 0 || now < start) { next = { daysAhead: i, day, start }; break; }
    }
  }

  if (box && txt && today > -1) {
    const rest = getRestForDay(today);
    const resting = !!open && !!rest && now >= rest[0] && now < rest[1];
    box.classList.toggle('is-open', !!open && !resting);
    box.classList.toggle('is-rest', resting);
    txt.innerHTML = resting
      ? `Waktu rehat <small>Buka semula ${formatHour(rest[1])}</small>`
      : open
        ? 'Buka sekarang'
        : `Tutup sekarang <small>Buka semula ${next.daysAhead === 0 ? 'hari ini' : next.daysAhead === 1 ? 'esok' : dayNames[next.day]
        }, ${formatHour(next.start)}</small>`;
    box.hidden = false;
    txt.parentNode.style.flexWrap = 'wrap';
  }

  document.querySelectorAll('[data-days]').forEach((row) => {
    if (row.dataset.days.split(' ').includes(String(today))) row.classList.add('is-today');
  });

  document.querySelectorAll('[data-copy]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const label = btn.textContent;
      navigator.clipboard.writeText(btn.dataset.copy).then(() => {
        btn.textContent = 'Disalin';
        setTimeout(() => { btn.textContent = label; }, 1600);
      });
    });
  });
}

function initCardBlur() {
  // Same effect as the hero text: a card fades and blurs as it leaves the
  // screen, at the top (under the navbar) and at the bottom, so it works in
  // both scroll directions.
  const cards = Array.from(document.querySelectorAll('.quick-access-card'));
  if (!cards.length) return;
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  const last = new WeakMap();
  let ticking = false;

  const update = () => {
    // navbar is injected from an include, so look it up on each update
    const navbar = document.querySelector('.navbar');
    const line = navbar ? navbar.getBoundingClientRect().bottom : 72;
    cards.forEach((card) => {
      const rect = card.getBoundingClientRect();
      // top edge: scrolling down, card passes under the navbar
      const out = Math.min(Math.max((line - rect.top) / rect.height, 0), 1);
      // bottom edge: scrolling up, card leaves below the screen (and eases in on the way back)
      const below = Math.min(Math.max((rect.bottom - window.innerHeight) / rect.height, 0), 1);
      const progress = Math.max(out, below);
      const rounded = Math.round(progress * 100) / 100;
      if (last.get(card) === rounded) return;
      last.set(card, rounded);
      card.style.opacity = rounded === 0 ? '' : String(1 - rounded);
      card.style.filter = rounded === 0 ? '' : `blur(${rounded * 10}px)`;
    });
    ticking = false;
  };

  window.addEventListener('scroll', () => {
    if (!ticking) {
      window.requestAnimationFrame(update);
      ticking = true;
    }
  }, { passive: true });
  window.addEventListener('resize', update);

  update();
}

document.addEventListener('DOMContentLoaded', async () => {
  await Promise.all([
    loadInclude('navbar-placeholder', 'assets/includes/navbar.html'),
    loadInclude('footer-placeholder', 'assets/includes/footer.html'),
  ]);

  initNavbar();
  initHeroParallax();
  initBackToTop();
  initLanguageSwitch();
  loadGoogleTranslate();
  initOfficeHours();
  initCardBlur();
});