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
  const isMobile = () => window.matchMedia('(max-width: 1280px)').matches;

  if (hamburger && navLinks) {
    const setMenu = (open) => {
      navLinks.classList.toggle('active', open);
      hamburger.setAttribute('aria-expanded', String(open));
      hamburger.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
      if (navbar) navbar.classList.toggle('menu-open', open);
      document.body.classList.toggle('menu-open', open);
    };
    hamburger.addEventListener('click', () => setMenu(!navLinks.classList.contains('active')));
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') setMenu(false);
    });
    window.addEventListener('resize', () => {
      if (!isMobile()) setMenu(false);
    });
    navLinks.addEventListener('click', (e) => {
      if (e.target.closest('a')) setMenu(false);
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

// Shared fade + blur used by the hero text and the quick access cards.
// progress: 0 = fully visible, 1 = fully faded and blurred.
function applyFadeBlur(el, progress) {
  el.style.opacity = progress === 0 ? '' : String(1 - progress);
  el.style.filter = progress === 0 ? '' : `blur(${progress * 10}px)`;
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
        applyFadeBlur(heroContent, progress);
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

function initSmoothScroll() {
  // Eased mouse-wheel scrolling: the page glides toward the wheel target
  // instead of jumping in steps. Touch screens, trackpad-free keyboard use,
  // scrollbar dragging and anchor links keep working natively.
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  if (!window.matchMedia('(pointer: fine)').matches) return;

  const root = document.documentElement;
  let target = window.scrollY;
  let current = window.scrollY;
  let raf = 0;
  let last = 0;
  const TAU = 110; // ms, bigger = floatier

  const maxScroll = () => Math.max(0, root.scrollHeight - window.innerHeight);

  const canScrollInside = (el, dy) => {
    for (; el && el !== document.body && el !== root; el = el.parentElement) {
      const oy = getComputedStyle(el).overflowY;
      if ((oy === 'auto' || oy === 'scroll') && el.scrollHeight > el.clientHeight) {
        if (dy < 0 ? el.scrollTop > 0 : el.scrollTop + el.clientHeight < el.scrollHeight - 1) return true;
      }
    }
    return false;
  };

  const tick = (now) => {
    const dt = Math.min(64, now - last);
    last = now;
    current += (target - current) * (1 - Math.exp(-dt / TAU));
    if (Math.abs(target - current) < 0.5) current = target;
    window.scrollTo({ top: current, behavior: 'instant' });
    raf = current === target ? 0 : requestAnimationFrame(tick);
  };

  window.addEventListener('wheel', (e) => {
    if (e.defaultPrevented || e.ctrlKey || e.metaKey) return; // pinch-zoom etc.
    if (root.classList.contains('a11y-no-motion')) return;
    if (Math.abs(e.deltaX) > Math.abs(e.deltaY)) return;
    if (getComputedStyle(document.body).overflow === 'hidden') return; // menu/modal open
    if (canScrollInside(e.target, e.deltaY)) return;
    const unit = e.deltaMode === 1 ? 32 : e.deltaMode === 2 ? window.innerHeight : 1;
    e.preventDefault();
    if (!raf) { current = window.scrollY; target = current; }
    target = Math.max(0, Math.min(maxScroll(), target + e.deltaY * unit));
    if (!raf) { last = performance.now(); raf = requestAnimationFrame(tick); }
  }, { passive: false });

  // keep in sync when the page is scrolled by other means (keys, scrollbar, anchors)
  window.addEventListener('scroll', () => {
    if (!raf || Math.abs(window.scrollY - current) > 3) {
      if (Math.abs(window.scrollY - current) > 3) {
        cancelAnimationFrame(raf); raf = 0;
      }
      if (!raf) { current = target = window.scrollY; }
    }
  }, { passive: true });
}

function initAccessibility() {
  // Floating accessibility button (bottom-left). The language translator lives
  // inside its panel, together with text-size and display options.
  const root = document.documentElement;
  const KEY = 'a11y-prefs';
  const SIZES = [90, 100, 115, 130, 150];
  const defaults = { size: 1, contrast: false, links: false, font: false, motion: false };
  let prefs = Object.assign({}, defaults);
  try { Object.assign(prefs, JSON.parse(localStorage.getItem(KEY) || '{}')); } catch (_) { }

  const apply = () => {
    root.style.fontSize = prefs.size === 1 ? '' : SIZES[prefs.size] + '%';
    root.classList.toggle('a11y-contrast', prefs.contrast);
    root.classList.toggle('a11y-links', prefs.links);
    root.classList.toggle('a11y-font', prefs.font);
    root.classList.toggle('a11y-no-motion', prefs.motion);
    try { localStorage.setItem(KEY, JSON.stringify(prefs)); } catch (_) { }
  };

  const langs = [['ms', 'Bahasa Melayu'], ['en', 'English'], ['zh-CN', '中文'], ['ta', 'தமிழ்'], ['ko', '한국어']];
  const m = document.cookie.match(/googtrans=\/[^/]+\/([^;]+)/);
  const curLang = m ? decodeURIComponent(m[1]) : 'ms';
  const toggles = [['contrast', 'Kontras tinggi', 'fa-circle-half-stroke'], ['links', 'Garis bawah pautan', 'fa-underline'], ['font', 'Fon mudah baca', 'fa-font'], ['motion', 'Hentikan animasi', 'fa-pause']];

  const wrap = document.createElement('div');
  wrap.className = 'a11y notranslate';
  wrap.setAttribute('translate', 'no');
  wrap.innerHTML =
    '<button type="button" class="a11y-btn" aria-label="Aksesibiliti / Accessibility" aria-expanded="false" aria-controls="a11yPanel"><i class="fa-solid fa-universal-access" aria-hidden="true"></i></button>' +
    '<div class="a11y-panel" id="a11yPanel" role="dialog" aria-label="Aksesibiliti / Accessibility" hidden>' +
    '<div class="a11y-head"><strong>Aksesibiliti</strong><button type="button" class="a11y-reset">Tetap semula</button></div>' +
    '<h3><i class="fa-solid fa-language" aria-hidden="true"></i> Bahasa / Language</h3>' +
    '<div class="a11y-langs">' + langs.map(([c, n]) => '<button type="button" data-lang="' + c + '" aria-pressed="' + (c === curLang) + '">' + n + '</button>').join('') + '</div>' +
    '<h3><i class="fa-solid fa-text-height" aria-hidden="true"></i> Saiz teks / Text size</h3>' +
    '<div class="a11y-size"><button type="button" data-size="-1" aria-label="Kecilkan teks">A−</button><span class="a11y-size-val"></span><button type="button" data-size="1" aria-label="Besarkan teks">A+</button></div>' +
    '<h3><i class="fa-solid fa-eye" aria-hidden="true"></i> Paparan / Display</h3>' +
    '<div class="a11y-opts">' + toggles.map(([k, n, ic]) => '<button type="button" data-opt="' + k + '"><i class="fa-solid ' + ic + '" aria-hidden="true"></i><span>' + n + '</span></button>').join('') + '</div>' +
    '</div>';
  document.body.appendChild(wrap);

  const btn = wrap.querySelector('.a11y-btn');
  const panel = wrap.querySelector('.a11y-panel');
  const sizeVal = wrap.querySelector('.a11y-size-val');

  const sync = () => {
    sizeVal.textContent = SIZES[prefs.size] + '%';
    wrap.querySelectorAll('[data-opt]').forEach((b) => b.setAttribute('aria-pressed', String(prefs[b.dataset.opt])));
    wrap.querySelector('[data-size="-1"]').disabled = prefs.size <= 0;
    wrap.querySelector('[data-size="1"]').disabled = prefs.size >= SIZES.length - 1;
    apply();
  };

  const setOpen = (open) => {
    panel.hidden = !open;
    btn.setAttribute('aria-expanded', String(open));
  };
  btn.addEventListener('click', (e) => { e.stopPropagation(); setOpen(panel.hidden); });
  document.addEventListener('click', (e) => { if (!wrap.contains(e.target)) setOpen(false); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !panel.hidden) { setOpen(false); btn.focus(); } });

  wrap.querySelectorAll('[data-lang]').forEach((b) => b.addEventListener('click', () => setSiteLanguage(b.dataset.lang)));
  wrap.querySelectorAll('[data-size]').forEach((b) => b.addEventListener('click', () => {
    prefs.size = Math.max(0, Math.min(SIZES.length - 1, prefs.size + Number(b.dataset.size)));
    sync();
  }));
  wrap.querySelectorAll('[data-opt]').forEach((b) => b.addEventListener('click', () => {
    prefs[b.dataset.opt] = !prefs[b.dataset.opt];
    sync();
  }));
  wrap.querySelector('.a11y-reset').addEventListener('click', () => { prefs = Object.assign({}, defaults); sync(); });

  sync();
}

function initPdCarousel() {
  const root = document.getElementById('pdCarousel');
  if (!root) return;
  const cards = Array.from(root.querySelectorAll('.pd-card'));
  const info = root.querySelector('.pd-info');
  const title = root.querySelector('.pd-title');
  const desc = root.querySelector('.pd-desc');
  const link = root.querySelector('.pd-link');
  const credit = root.querySelector('.pd-credit');
  const count = root.querySelector('.pd-count');
  const n = cards.length;
  let active = 0;

  const layout = () => {
    cards.forEach((card, i) => {
      const pos = (i - active + n) % n;
      card.dataset.pos = pos < 3 ? String(pos) : 'hidden';
      card.tabIndex = pos === 0 ? 0 : -1;
    });
  };

  const text = () => {
    const c = cards[active];
    title.textContent = c.dataset.title;
    desc.textContent = c.dataset.desc;
    link.href = c.getAttribute('href');
    credit.textContent = c.dataset.credit || '';
    credit.hidden = !c.dataset.credit;
    count.textContent = String(active + 1).padStart(2, '0') + ' / ' + String(n).padStart(2, '0');
  };

  const go = (dir) => {
    active = (active + dir + n) % n;
    layout();
    info.classList.add('is-changing');
    setTimeout(() => { text(); info.classList.remove('is-changing'); }, 180);
  };

  root.querySelector('.pd-prev').addEventListener('click', () => go(-1));
  root.querySelector('.pd-next').addEventListener('click', () => go(1));

  // clicking a card behind the front one brings it forward; the front card opens its page
  cards.forEach((card, i) => card.addEventListener('click', (e) => {
    const pos = (i - active + n) % n;
    if (pos === 0) return;
    e.preventDefault();
    go(pos);
  }));

  root.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') go(-1);
    else if (e.key === 'ArrowRight') go(1);
  });

  // swipe on touch screens
  let x0 = null;
  root.addEventListener('touchstart', (e) => { x0 = e.touches[0].clientX; }, { passive: true });
  root.addEventListener('touchend', (e) => {
    if (x0 === null) return;
    const dx = e.changedTouches[0].clientX - x0;
    x0 = null;
    if (Math.abs(dx) > 40) go(dx < 0 ? 1 : -1);
  });

  layout();
  text();
}

function initCardBlur() {
  // A card fades and blurs as it enters/leaves at the bottom of the screen.
  // No effect at the top, so cards stay sharp when they scroll under the navbar.
  const cards = Array.from(document.querySelectorAll('.quick-access-card'));
  if (!cards.length) return;
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  const last = new WeakMap();
  let ticking = false;

  const update = () => {
    cards.forEach((card) => {
      const rect = card.getBoundingClientRect();
      // bottom edge only: card eases in as it rises into view
      const progress = Math.min(Math.max((rect.bottom - window.innerHeight) / rect.height, 0), 1);
      const rounded = Math.round(progress * 100) / 100;
      if (last.get(card) === rounded) return;
      last.set(card, rounded);
      applyFadeBlur(card, rounded);
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

// Dark mode: saved choice wins, otherwise follow the OS setting.
// The <head> snippet applies it before first paint; this wires up the button.
function initTheme() {
  const root = document.documentElement;
  const btn = document.getElementById('themeToggle');
  if (!btn) return;
  const KEY = 'theme';
  const icon = btn.querySelector('i');
  const mq = window.matchMedia('(prefers-color-scheme: dark)');

  const saved = () => { try { return localStorage.getItem(KEY); } catch (_) { return null; } };
  const isDark = () => root.getAttribute('data-theme') === 'dark';

  const render = () => {
    const dark = isDark();
    btn.setAttribute('aria-pressed', String(dark));
    btn.setAttribute('aria-label', dark ? 'Tukar ke mod cerah' : 'Tukar ke mod gelap');
    btn.title = dark ? 'Mod cerah' : 'Mod gelap';
    if (icon) icon.className = 'fa-solid ' + (dark ? 'fa-sun' : 'fa-moon');
  };

  const setTheme = (theme, persist) => {
    root.classList.add('theme-anim');
    root.setAttribute('data-theme', theme);
    if (persist) { try { localStorage.setItem(KEY, theme); } catch (_) { } }
    render();
    setTimeout(() => root.classList.remove('theme-anim'), 300);
  };

  btn.addEventListener('click', () => setTheme(isDark() ? 'light' : 'dark', true));
  // Follow OS changes only while the visitor hasn't picked a theme themselves
  mq.addEventListener('change', (e) => { if (!saved()) setTheme(e.matches ? 'dark' : 'light', false); });
  render();
}

document.addEventListener('DOMContentLoaded', async () => {
  await Promise.all([
    loadInclude('navbar-placeholder', 'assets/includes/navbar.html'),
    loadInclude('footer-placeholder', 'assets/includes/footer.html'),
  ]);

  initNavbar();
  initTheme();
  initHeroParallax();
  initBackToTop();
  initLanguageSwitch();
  initAccessibility();
  loadGoogleTranslate();
  initOfficeHours();
  initSmoothScroll();
  initPdCarousel();
  initCardBlur();
});