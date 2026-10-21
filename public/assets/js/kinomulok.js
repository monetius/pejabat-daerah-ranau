function initNavbar() {
  const navbar = document.querySelector('.navbar');
  const english = navbar?.getAttribute('lang') === 'en';
  const hamburger = document.getElementById('hamburger');
  const navLinks = document.getElementById('navLinks');
  const dropdowns = document.querySelectorAll('.dropdown');
  const isMobile = () => window.matchMedia('(max-width: 1280px)').matches;

  if (hamburger && navLinks) {
    const setMenu = (open) => {
      navLinks.classList.toggle('active', open);
      hamburger.setAttribute('aria-expanded', String(open));
      hamburger.setAttribute('aria-label', english ? (open ? 'Close menu' : 'Open menu') : (open ? 'Tutup menu' : 'Buka menu'));
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
    if (document.documentElement.classList.contains('a11y-no-motion')) {
      heroLayers.forEach((layer) => { layer.style.transform = ''; });
      if (heroMedia) heroMedia.style.transform = '';
      if (heroContent) applyFadeBlur(heroContent, 0);
      return;
    }
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

  document.addEventListener('site:accessibility-change', updateParallax);
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
    window.scrollTo({ top: 0, behavior: reduceMotion || document.documentElement.classList.contains('a11y-no-motion') ? 'auto' : 'smooth' });
  });

  const onScroll = () => btn.classList.toggle('is-visible', window.scrollY > 400);
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });
}

function initLanguageSwitch() {
  const machineLanguage = document.cookie.match(/googtrans=\/[^/]+\/([^;]+)/)?.[1];
  if (machineLanguage && !['ms', 'en'].includes(machineLanguage)) {
    const navbar = document.querySelector('.navbar');
    navbar?.classList.remove('notranslate');
    navbar?.removeAttribute('translate');
  }
  const toggle = document.getElementById('langToggle');
  const menu = document.getElementById('langMenu');
  if (!toggle || !menu) return;

  const setOpen = (open) => {
    menu.hidden = !open;
    toggle.setAttribute('aria-expanded', String(open));
  };
  const currentLanguage = machineLanguage || document.querySelector('.navbar')?.getAttribute('lang') || 'ms';
  const languageCodes = { ms: 'BM', en: 'EN', 'zh-CN': '中文', ta: 'தமிழ்', ko: '한국어' };
  toggle.querySelector('[data-language-label]').textContent = languageCodes[currentLanguage] || 'BM';
  menu.querySelectorAll('[data-lang]').forEach((button) => {
    button.setAttribute('aria-pressed', String(button.dataset.lang === currentLanguage));
    button.addEventListener('click', (event) => {
      event.preventDefault();
      setSiteLanguage(button.dataset.lang);
    });
  });
  toggle.addEventListener('click', () => setOpen(menu.hidden));
  document.addEventListener('click', (event) => {
    if (!toggle.contains(event.target) && !menu.contains(event.target)) setOpen(false);
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !menu.hidden) {
      setOpen(false);
      toggle.focus();
    }
  });
  document.querySelector('[data-site-language-form]')?.addEventListener('focusout', (event) => {
    if (!event.currentTarget.contains(event.relatedTarget)) setOpen(false);
  });
}

// Loads Google's page-translation widget in the background and drives it
// through a plain language menu instead of its default UI.
function loadGoogleTranslate() {
  if (!/(?:^|;\s*)googtrans=\/ms\/(?:en|zh-CN|ta|ko)(?:;|$)/.test(document.cookie)) return;
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

  const nativeForm = document.querySelector('[data-site-language-form]');
  if (nativeForm && ['ms', 'en'].includes(lang)) {
    if (lang === 'en') {
      setCookie('/ms/en', '');
      setCookie('/ms/en', ` domain=${host};`);
    }
    nativeForm.querySelector('[name="translate_page"]').value = '1';
    nativeForm.requestSubmit(nativeForm.querySelector(`button[value="${lang}"]`));
    return;
  }

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

  document.addEventListener('site:accessibility-change', () => {
    if (root.classList.contains('a11y-no-motion')) {
      cancelAnimationFrame(raf);
      raf = 0;
      current = target = window.scrollY;
    }
  });

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
  // Floating accessibility controls for text size and display preferences.
  const root = document.documentElement;
  const KEY = 'a11y-prefs';
  const SIZES = [25, 40, 50, 65, 80, 90, 100, 115, 130, 150, 175, 200];
  const defaults = { size: 100, contrast: false, links: false, font: false, motion: false, spacing: false, focus: false };
  let prefs = Object.assign({}, defaults);
  try {
    const saved = JSON.parse(localStorage.getItem(KEY) || '{}');
    const size = saved.sizePercent ?? [90, 100, 115, 130, 150][saved.size];
    prefs.size = SIZES.includes(size) ? size : 100;
    for (const key of Object.keys(defaults).filter((key) => key !== 'size')) {
      prefs[key] = typeof saved[key] === 'boolean' ? saved[key] : defaults[key];
    }
  } catch (_) { }

  const apply = () => {
    root.style.fontSize = prefs.size === 100 ? '' : prefs.size + '%';
    root.classList.toggle('a11y-contrast', prefs.contrast);
    root.classList.toggle('a11y-links', prefs.links);
    root.classList.toggle('a11y-font', prefs.font);
    root.classList.toggle('a11y-no-motion', prefs.motion);
    root.classList.toggle('a11y-spacing', prefs.spacing);
    root.classList.toggle('a11y-focus', prefs.focus);
    document.dispatchEvent(new Event('site:accessibility-change'));
    try { localStorage.setItem(KEY, JSON.stringify({ ...prefs, sizePercent: prefs.size })); } catch (_) { }
  };

  const toggles = [['contrast', 'Kontras tinggi', 'fa-circle-half-stroke'], ['links', 'Garis bawah pautan', 'fa-underline'], ['font', 'Fon mudah baca', 'fa-font'], ['motion', 'Hentikan animasi', 'fa-pause'], ['spacing', 'Jarak teks', 'fa-text-width'], ['focus', 'Fokus papan kekunci', 'fa-keyboard']];

  const wrap = document.createElement('div');
  wrap.className = 'a11y notranslate';
  wrap.setAttribute('translate', 'no');
  wrap.innerHTML =
    '<button type="button" class="a11y-btn" aria-label="Aksesibiliti / Accessibility" aria-expanded="false" aria-controls="a11yPanel" aria-haspopup="dialog"><i class="fa-solid fa-universal-access" aria-hidden="true"></i></button>' +
    '<div class="a11y-panel" id="a11yPanel" role="dialog" aria-label="Aksesibiliti / Accessibility" hidden>' +
    '<div class="a11y-head"><strong>Aksesibiliti</strong><div class="a11y-actions"><button type="button" class="a11y-reset">Tetap semula</button><button type="button" class="a11y-close" aria-label="Tutup aksesibiliti"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></div></div>' +
    '<h3><i class="fa-solid fa-text-height" aria-hidden="true"></i> Saiz teks / Text size</h3>' +
    '<div class="a11y-size"><button type="button" data-size="-1" aria-label="Kecilkan teks">A−</button><span class="a11y-size-val" role="status" aria-live="polite" aria-atomic="true"></span><button type="button" data-size="1" aria-label="Besarkan teks">A+</button></div>' +
    '<h3><i class="fa-solid fa-eye" aria-hidden="true"></i> Paparan / Display</h3>' +
    '<div class="a11y-opts">' + toggles.map(([k, n, ic]) => '<button type="button" data-opt="' + k + '"><i class="fa-solid ' + ic + '" aria-hidden="true"></i><span>' + n + '</span><span class="a11y-option-state" aria-hidden="true">✓</span></button>').join('') + '</div>' +
    '</div>';
  document.body.appendChild(wrap);

  const btn = wrap.querySelector('.a11y-btn');
  const panel = wrap.querySelector('.a11y-panel');
  const sizeVal = wrap.querySelector('.a11y-size-val');

  const sync = () => {
    sizeVal.textContent = prefs.size + '%';
    wrap.querySelectorAll('[data-opt]').forEach((b) => b.setAttribute('aria-pressed', String(prefs[b.dataset.opt])));
    wrap.querySelector('[data-size="-1"]').disabled = prefs.size <= SIZES[0];
    wrap.querySelector('[data-size="1"]').disabled = prefs.size >= SIZES[SIZES.length - 1];
    apply();
  };

  const setOpen = (open) => {
    panel.hidden = !open;
    btn.setAttribute('aria-expanded', String(open));
    if (open) wrap.querySelector('.a11y-close').focus();
  };
  wrap.querySelector('.a11y-close').addEventListener('click', () => { setOpen(false); btn.focus(); });
  btn.addEventListener('click', (e) => { e.stopPropagation(); setOpen(panel.hidden); });
  document.addEventListener('click', (e) => { if (!wrap.contains(e.target)) setOpen(false); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !panel.hidden) { setOpen(false); btn.focus(); } });

  wrap.querySelectorAll('[data-size]').forEach((b) => b.addEventListener('click', () => {
    const index = Math.max(0, Math.min(SIZES.length - 1, SIZES.indexOf(prefs.size) + Number(b.dataset.size)));
    prefs.size = SIZES[index];
    sync();
  }));
  wrap.querySelectorAll('[data-opt]').forEach((b) => b.addEventListener('click', () => {
    prefs[b.dataset.opt] = !prefs[b.dataset.opt];
    sync();
  }));
  wrap.querySelector('.a11y-reset').addEventListener('click', () => { prefs = Object.assign({}, defaults); sync(); });

  sync();
}

// Shared motion only: each carousel keeps its existing card layout and styling.
function initDriftMotion(stage, { render, cycle, settle = (position) => position, focusScope = stage }) {
  const preference = window.matchMedia('(prefers-reduced-motion: reduce)');
  const reduced = () => preference.matches || document.documentElement.classList.contains('a11y-no-motion');
  let position = 0;
  let velocity = -60;
  let direction = -1;
  let boost = 0;
  let pointer = null;
  let cursor = null;
  let visible = false;
  let focused = false;
  let frameId = null;
  let previousTime = 0;
  let suppressClickUntil = 0;

  const wrap = () => {
    const width = cycle();
    if (width > 0) position = ((position % width) + width) % width;
  };
  const paint = () => { wrap(); render(position); };
  const canAnimate = () => visible && !document.hidden && !focused && !reduced();
  const frame = (now) => {
    frameId = null;
    const elapsed = Math.min(0.05, (now - previousTime) / 1000);
    previousTime = now;
    if (!pointer && canAnimate()) {
      boost *= Math.exp(-elapsed * 3.5);
      const target = direction * 60 + boost;
      velocity += (target - velocity) * (1 - Math.exp(-elapsed * 2.2));
      position += velocity * elapsed;
      paint();
    }
    if (canAnimate() && !pointer) frameId = requestAnimationFrame(frame);
  };
  const schedule = () => {
    if (frameId !== null) cancelAnimationFrame(frameId);
    frameId = null;
    if (!canAnimate() || pointer) return;
    previousTime = performance.now();
    frameId = requestAnimationFrame(frame);
  };
  const setPosition = (next) => { position = next; velocity = 0; boost = 0; paint(); };

  stage.classList.add('is-drifting');
  stage.addEventListener('pointerdown', (event) => {
    if (!event.isPrimary || (event.pointerType === 'mouse' && event.button !== 0)) return;
    pointer = { id: event.pointerId, startX: event.clientX, startY: event.clientY,
      x: event.clientX, time: performance.now(), velocity: 0, active: false };
    cursor = null;
    boost = 0;
    schedule();
  });
  stage.addEventListener('pointermove', (event) => {
    const now = performance.now();
    if (!pointer) {
      if (event.pointerType !== 'mouse' || reduced() || focused) return;
      if (cursor) {
        const delta = event.clientX - cursor.x;
        if (Math.abs(delta) > 1) {
          direction = delta > 0 ? 1 : -1;
          boost = Math.max(-700, Math.min(700, delta / Math.max(0.001, (now - cursor.time) / 1000) * 0.35));
        }
      }
      cursor = { x: event.clientX, time: now };
      return;
    }
    if (event.pointerId !== pointer.id) return;
    if (!pointer.active) {
      const horizontal = event.clientX - pointer.startX;
      const vertical = event.clientY - pointer.startY;
      if (Math.max(Math.abs(horizontal), Math.abs(vertical)) < 8) return;
      if (Math.abs(vertical) >= Math.abs(horizontal)) { pointer = null; schedule(); return; }
      pointer.active = true;
      stage.setPointerCapture(event.pointerId);
      stage.classList.add('is-dragging');
    }
    event.preventDefault();
    const delta = event.clientX - pointer.x;
    const elapsed = Math.max(0.001, (now - pointer.time) / 1000);
    position += delta;
    pointer.velocity = pointer.velocity * 0.6 + delta / elapsed * 0.4;
    pointer.x = event.clientX;
    pointer.time = now;
    paint();
  });
  const release = (event) => {
    if (!pointer || event.pointerId !== pointer.id) return;
    const moved = pointer.active;
    const cancelled = event.type === 'pointercancel' || event.type === 'lostpointercapture';
    velocity = cancelled || nowSince(pointer.time) > 120 ? 0 : Math.max(-1800, Math.min(1800, pointer.velocity));
    if (Math.abs(velocity) > 40) direction = velocity > 0 ? 1 : -1;
    pointer = null;
    stage.classList.remove('is-dragging');
    if (moved) {
      suppressClickUntil = Date.now() + 350;
      if (reduced()) setPosition(settle(position));
    }
    schedule();
  };
  const nowSince = (time) => performance.now() - time;
  stage.addEventListener('pointerup', release);
  stage.addEventListener('pointercancel', release);
  stage.addEventListener('lostpointercapture', release);
  stage.addEventListener('pointerleave', () => { cursor = null; });
  stage.addEventListener('dragstart', (event) => event.preventDefault());
  stage.addEventListener('click', (event) => {
    if (Date.now() >= suppressClickUntil) return;
    event.preventDefault();
    event.stopPropagation();
  }, true);
  stage.addEventListener('wheel', (event) => {
    if (Math.abs(event.deltaX) <= Math.abs(event.deltaY)) return;
    event.preventDefault();
    const delta = event.deltaX * (event.deltaMode === 1 ? 16 : event.deltaMode === 2 ? stage.clientWidth : 1);
    position -= delta;
    direction = delta < 0 ? 1 : -1;
    velocity = direction * 60;
    paint();
  }, { passive: false });
  focusScope.addEventListener('focusin', (event) => {
    focused = event.target.matches(':focus-visible');
    schedule();
  });
  focusScope.addEventListener('focusout', () => setTimeout(() => {
    focused = focusScope.contains(document.activeElement) && document.activeElement.matches(':focus-visible');
    schedule();
  }, 0));
  document.addEventListener('visibilitychange', schedule);
  preference.addEventListener('change', () => { velocity = 0; boost = 0; schedule(); });
  new MutationObserver(schedule).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
  new IntersectionObserver((entries) => { visible = entries[0].isIntersecting; schedule(); }).observe(stage);
  paint();
  return { setPosition, position: () => position };
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
  if (!n || !info || !title || !desc || !link) return;
  let active = 0;
  let textTimer;
  let suppressClickUntil = 0;

  const layout = () => {
    cards.forEach((card, i) => {
      const pos = (i - active + n) % n;
      card.dataset.pos = pos < 3 ? String(pos) : 'hidden';
      card.tabIndex = pos === 0 ? 0 : -1;
      card.setAttribute('aria-hidden', String(pos !== 0));
    });
  };

  const text = () => {
    const c = cards[active];
    title.textContent = c.dataset.title;
    desc.textContent = c.dataset.desc;
    link.href = c.getAttribute('href');
    if (credit) {
      credit.textContent = c.dataset.credit || '';
      credit.hidden = !c.dataset.credit;
    }
    if (count) count.textContent = String(active + 1).padStart(2, '0') + ' / ' + String(n).padStart(2, '0');
  };

  const go = (dir) => {
    active = (active + dir + n) % n;
    layout();
    clearTimeout(textTimer);
    text();
    info.classList.add('is-changing');
    textTimer = setTimeout(() => info.classList.remove('is-changing'), 180);
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
    if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return;
    e.preventDefault();
    go(e.key === 'ArrowLeft' ? -1 : 1);
    if (e.target.classList.contains('pd-card')) cards[active].focus({ preventScroll: true });
  });

  // Horizontal swipes belong to the cards; vertical gestures keep scrolling the page.
  const stage = root.querySelector('.pd-stage');
  let swipeStart = null;
  stage.addEventListener('pointerdown', (e) => {
    if (!e.isPrimary || (e.pointerType === 'mouse' && e.button !== 0) || e.target.closest('.pd-nav')) return;
    swipeStart = { pointerId: e.pointerId, x: e.clientX, y: e.clientY };
    stage.setPointerCapture(e.pointerId);
  });
  stage.addEventListener('pointercancel', () => { swipeStart = null; });
  stage.addEventListener('pointerup', (e) => {
    if (!swipeStart || e.pointerId !== swipeStart.pointerId) return;
    const dx = e.clientX - swipeStart.x;
    const dy = e.clientY - swipeStart.y;
    swipeStart = null;
    if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy) * 1.5) {
      suppressClickUntil = Date.now() + 500;
      go(dx < 0 ? 1 : -1);
    }
  });
  stage.addEventListener('click', (e) => {
    if (Date.now() < suppressClickUntil) {
      e.preventDefault();
      e.stopPropagation();
    }
  }, true);

  layout();
  text();
}

function initAffiliatedCarousel() {
  const root = document.querySelector('[data-affiliated-carousel]');
  const track = root?.querySelector('.affiliated-grid');
  if (!track) return;
  const items = Array.from(track.children);
  if (items.length < 2) return;
  const clone = (item) => {
    const copy = item.cloneNode(true);
    copy.dataset.carouselClone = '';
    copy.setAttribute('aria-hidden', 'true');
    copy.querySelectorAll('[id]').forEach((element) => element.removeAttribute('id'));
    copy.querySelectorAll('a').forEach((anchor) => { anchor.tabIndex = -1; });
    return copy;
  };
  track.prepend(...items.map(clone));
  track.append(...items.map(clone));
  let offset = 0;
  let width = 1;
  const measure = () => {
    offset = items[0].offsetLeft - track.firstElementChild.offsetLeft;
    width = items[items.length - 1].nextElementSibling.offsetLeft - items[0].offsetLeft;
  };
  measure();
  const motion = initDriftMotion(track, {
    cycle: () => width,
    render: (position) => { track.scrollLeft = offset + ((-position % width) + width) % width; },
  });
  track.addEventListener('focusout', () => motion.setPosition(-(track.scrollLeft - offset)));
  window.addEventListener('resize', () => {
    const progress = motion.position() / width;
    measure();
    motion.setPosition(progress * width);
  });
}


function initHomeCalendar() {
  const root = document.querySelector('[data-home-calendar]');
  if (!root) return;
  const monthLabel = root.querySelector('#home-calendar-month');
  const dates = root.querySelector('[data-calendar-dates]');
  const agenda = root.querySelector('[data-calendar-agenda]');
  const note = root.querySelector('[data-calendar-note]');
  const source = root.querySelector('[data-calendar-source]');
  const malaysiaDate = new Intl.DateTimeFormat('en-GB', {
    timeZone: 'Asia/Kuala_Lumpur', year: 'numeric', month: 'numeric', day: 'numeric',
  });
  const monthFormat = new Intl.DateTimeFormat('ms-MY', { month: 'long', year: 'numeric', timeZone: 'UTC' });
  const dateFormat = new Intl.DateTimeFormat('ms-MY', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC' });
  const shortDate = new Intl.DateTimeFormat('ms-MY', { day: 'numeric', month: 'short', timeZone: 'UTC' });
  const currentDate = () => {
    const parts = Object.fromEntries(malaysiaDate.formatToParts(new Date()).map((part) => [part.type, part.value]));
    return new Date(Date.UTC(Number(parts.year), Number(parts.month) - 1, Number(parts.day)));
  };
  const isoDate = (date) => date.toISOString().slice(0, 10);
  const parseDate = (date) => new Date(`${date}T12:00:00Z`);
  const isSameMonth = (first, second) => first.getUTCFullYear() === second.getUTCFullYear() && first.getUTCMonth() === second.getUTCMonth();
  const element = (tag, className, text) => {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
  };
  let today = currentDate();
  let displayedMonth = new Date(Date.UTC(today.getUTCFullYear(), today.getUTCMonth(), 1));
  let selectedDate = isoDate(today);
  let daySelected = false;
  let entries = [];
  let loading = false;
  let failed = false;
  let holidaySource = null;
  let requestId = 0;
  let pendingRequest;

  const entriesOn = (date) => entries.filter((entry) => entry.start_date <= date && entry.end_date >= date);
  const renderDates = () => {
    const restoreFocus = dates.contains(document.activeElement);
    monthLabel.textContent = monthFormat.format(displayedMonth);
    const firstWeekday = (displayedMonth.getUTCDay() + 6) % 7;
    const fragment = document.createDocumentFragment();
    for (let week = 0; week < 6; week++) {
      const row = document.createElement('tr');
      for (let weekday = 0; weekday < 7; weekday++) {
        const date = new Date(displayedMonth);
        date.setUTCDate(week * 7 + weekday - firstWeekday + 1);
        const key = isoDate(date);
        const items = entriesOn(key);
        const cell = document.createElement('td');
        const button = element('button', 'home-calendar-day');
        button.type = 'button';
        button.dataset.date = key;
        button.tabIndex = key === selectedDate ? 0 : -1;
        button.setAttribute('aria-pressed', String(daySelected && key === selectedDate));
        button.setAttribute('aria-label', dateFormat.format(date) + (items.length ? `. ${items.map((item) => `${item.kind === 'holiday' ? 'Cuti umum' : 'Program'}: ${item.title}`).join('. ')}` : ''));
        button.setAttribute('aria-controls', 'home-calendar-agenda');
        if (!isSameMonth(date, displayedMonth)) cell.classList.add('is-outside-month');
        if (key === isoDate(today)) {
          button.classList.add('is-today');
          button.setAttribute('aria-current', 'date');
        }
        if (daySelected && key === selectedDate) button.classList.add('is-selected');
        const time = element('time', '', String(date.getUTCDate()));
        time.dateTime = key;
        button.append(time);
        const dots = element('span', 'calendar-day-dots');
        dots.setAttribute('aria-hidden', 'true');
        for (const kind of ['program', 'holiday']) {
          if (items.some((item) => item.kind === kind)) dots.append(element('i', `calendar-dot ${kind}`));
        }
        button.append(dots);
        cell.append(button);
        row.append(cell);
      }
      fragment.append(row);
    }
    dates.replaceChildren(fragment);
    if (restoreFocus) dates.querySelector(`[data-date="${selectedDate}"]`)?.focus({ preventScroll: true });
  };

  const renderAgenda = () => {
    agenda.replaceChildren();
    agenda.setAttribute('aria-busy', String(loading));
    if (loading || failed) {
      agenda.append(element('p', 'home-calendar-empty', loading ? 'Memuatkan program & cuti umum…' : 'Maklumat kalendar tidak dapat dimuatkan.'));
      if (failed) {
        const retry = element('button', 'home-calendar-retry', 'Cuba lagi');
        retry.type = 'button';
        retry.addEventListener('click', loadEntries);
        agenda.append(retry);
      }
      return;
    }
    const heading = element('h4', 'home-calendar-agenda-title', daySelected ? dateFormat.format(parseDate(selectedDate)) : `Agenda ${monthFormat.format(displayedMonth)}`);
    agenda.append(heading);
    const monthStart = isoDate(displayedMonth);
    const monthEnd = new Date(displayedMonth);
    monthEnd.setUTCMonth(monthEnd.getUTCMonth() + 1);
    monthEnd.setUTCDate(0);
    const items = daySelected ? entriesOn(selectedDate) : entries.filter((item) => item.start_date <= isoDate(monthEnd) && item.end_date >= monthStart);
    if (!items.length) {
      agenda.append(element('p', 'home-calendar-empty', daySelected ? 'Tiada program atau cuti umum pada tarikh ini.' : 'Tiada program atau cuti umum pada bulan ini.'));
      return;
    }
    const list = element('ul', 'home-calendar-agenda-list');
    items.forEach((item) => {
      const row = element('li', `home-calendar-event ${item.kind === 'holiday' ? 'holiday' : 'program'}`);
      const label = shortDate.format(parseDate(item.start_date)) + (item.end_date !== item.start_date ? ` – ${shortDate.format(parseDate(item.end_date))}` : '');
      row.append(element('span', 'home-calendar-event-date', `${label} · ${item.kind === 'holiday' ? 'Cuti umum' : 'Program / Acara'}`));
      row.append(element('h5', 'home-calendar-event-title', item.title));
      const details = [];
      if (item.start_time) details.push(item.start_time + (item.end_time ? ` – ${item.end_time}` : '') + ' MYT');
      else if (item.kind !== 'holiday') details.push('Sepanjang hari');
      if (item.location) details.push(item.location);
      if (details.length) row.append(element('p', 'home-calendar-event-meta', details.join(' · ')));
      if (item.description) row.append(element('p', 'home-calendar-event-description', item.description));
      if (item.url) {
        let url;
        try { url = new URL(item.url, window.location.href); } catch {}
        if (url && ['http:', 'https:'].includes(url.protocol)) {
          const link = element('a', 'home-calendar-event-link', 'Lihat maklumat ↗');
          link.href = url.href;
          if (url.origin !== window.location.origin) { link.target = '_blank'; link.rel = 'noopener noreferrer'; }
          row.append(link);
        }
      }
      list.append(row);
    });
    agenda.append(list);
  };
  const render = () => {
    renderDates();
    renderAgenda();
    note.textContent = holidaySource ? `Cuti umum Sabah & Malaysia · ${displayedMonth.getUTCFullYear()}`
      : loading ? '' : `Jadual cuti umum ${displayedMonth.getUTCFullYear()} belum diimport.`;
    source.href = holidaySource || 'https://sabah.gov.my/public-holidays';
  };
  async function loadEntries() {
    const currentRequest = ++requestId;
    pendingRequest?.abort();
    pendingRequest = new AbortController();
    entries = [];
    loading = true;
    failed = false;
    holidaySource = null;
    render();
    try {
      const url = new URL(root.dataset.calendarUrl, window.location.href);
      url.searchParams.set('month', isoDate(displayedMonth).slice(0, 7));
      const response = await fetch(url, { headers: { Accept: 'application/json' }, cache: 'no-store', signal: pendingRequest.signal });
      if (!response.ok) throw new Error('Calendar request failed');
      const data = await response.json();
      if (currentRequest !== requestId) return;
      entries = data.entries;
      holidaySource = data.holiday_source;
    } catch (error) {
      if (error.name === 'AbortError' || currentRequest !== requestId) return;
      failed = true;
    }
    loading = false;
    render();
  }

  const changeMonth = (offset) => {
    const next = new Date(displayedMonth);
    next.setUTCMonth(next.getUTCMonth() + offset);
    if (next.getUTCFullYear() < 2000 || next.getUTCFullYear() > 2100) return;
    displayedMonth = next;
    selectedDate = isSameMonth(today, displayedMonth) ? isoDate(today) : isoDate(displayedMonth);
    daySelected = false;
    loadEntries();
  };
  const selectDate = (key) => {
    const date = parseDate(key);
    if (date.getUTCFullYear() < 2000 || date.getUTCFullYear() > 2100) return;
    const monthChanged = !isSameMonth(date, displayedMonth);
    displayedMonth = new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), 1));
    selectedDate = key;
    daySelected = true;
    if (monthChanged) loadEntries();
    else render();
    dates.querySelector(`[data-date="${key}"]`)?.focus({ preventScroll: true });
  };
  dates.addEventListener('click', (event) => {
    const button = event.target.closest('[data-date]');
    if (button) selectDate(button.dataset.date);
  });
  dates.addEventListener('keydown', (event) => {
    const button = event.target.closest('[data-date]');
    if (!button) return;
    const offsets = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7 };
    const date = parseDate(button.dataset.date);
    let offset = offsets[event.key];
    if (event.key === 'Home') offset = -((date.getUTCDay() + 6) % 7);
    if (event.key === 'End') offset = 6 - ((date.getUTCDay() + 6) % 7);
    if (offset === undefined) return;
    event.preventDefault();
    date.setUTCDate(date.getUTCDate() + offset);
    selectDate(isoDate(date));
  });
  root.querySelector('[data-calendar-previous]').addEventListener('click', () => changeMonth(-1));
  root.querySelector('[data-calendar-next]').addEventListener('click', () => changeMonth(1));
  const returnToToday = () => {
    today = currentDate();
    displayedMonth = new Date(Date.UTC(today.getUTCFullYear(), today.getUTCMonth(), 1));
    selectedDate = isoDate(today);
    daySelected = false;
    loadEntries();
  };
  root.querySelector('[data-calendar-today]').addEventListener('click', returnToToday);
  document.addEventListener('visibilitychange', () => { if (!document.hidden) { today = currentDate(); loadEntries(); } });
  setInterval(() => {
    if (document.hidden) return;
    const nextToday = currentDate();
    if (nextToday.getTime() !== today.getTime()) {
      const wasCurrentMonth = isSameMonth(today, displayedMonth);
      today = nextToday;
      if (wasCurrentMonth && !daySelected) returnToToday();
      else render();
    }
  }, 60000);
  loadEntries();
}


function initCardBlur() {
  // A card fades and blurs as it enters/leaves at the bottom of the screen.
  // No effect at the top, so cards stay sharp when they scroll under the navbar.
  const cards = Array.from(document.querySelectorAll('.quick-access-card, .hb-head, .hb-feature, .hb-card'));
  if (!cards.length) return;
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  const last = new WeakMap();
  let ticking = false;

  const update = () => {
    cards.forEach((card) => {
      const rect = card.getBoundingClientRect();
      // bottom edge only: card eases in as it rises into view
      const progress = document.documentElement.classList.contains('a11y-no-motion') ? 0
        : Math.min(Math.max((rect.bottom - window.innerHeight) / rect.height, 0), 1);
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
  document.addEventListener('site:accessibility-change', update);

  update();
}

// Dark mode: saved choice wins, otherwise follow the OS setting.
// The <head> snippet applies it before first paint; this wires up the button.
function initTheme() {
  const root = document.documentElement;
  const btn = document.getElementById('themeToggle');
  if (!btn) return;
  const KEY = 'theme';
  // Update older navbar content saved in the CMS as well as the default navbar.
  btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"></svg>';
  const icon = btn.querySelector('svg');
  const mq = window.matchMedia('(prefers-color-scheme: dark)');
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  let transitionLayer = null;
  let transitionAnimation = null;

  const saved = () => { try { return localStorage.getItem(KEY); } catch (_) { return null; } };
  const isDark = () => root.getAttribute('data-theme') === 'dark';

  const render = () => {
    const dark = isDark();
    const english = document.querySelector('.navbar')?.getAttribute('lang') === 'en';
    btn.setAttribute('aria-pressed', String(dark));
    btn.setAttribute('aria-label', english ? (dark ? 'Switch to light mode' : 'Switch to dark mode') : (dark ? 'Tukar ke mod cerah' : 'Tukar ke mod gelap'));
    btn.title = english ? (dark ? 'Light mode' : 'Dark mode') : (dark ? 'Mod cerah' : 'Mod gelap');
    icon.innerHTML = dark
      ? '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>'
      : '<path d="M20.9 13A9 9 0 0 1 11 3.1 9 9 0 1 0 20.9 13Z"/>';
  };

  const applyTheme = (theme, persist) => {
    root.setAttribute('data-theme', theme);
    if (persist) { try { localStorage.setItem(KEY, theme); } catch (_) { } }
    render();
  };

  const setTheme = async (theme, persist) => {
    if (transitionLayer) return;
    if (reducedMotion.matches || root.classList.contains('a11y-no-motion') || typeof root.animate !== 'function') {
      applyTheme(theme, persist);
      return;
    }

    transitionLayer = document.createElement('div');
    transitionLayer.className = 'site-theme-transition';
    transitionLayer.setAttribute('data-theme', theme);
    transitionLayer.setAttribute('aria-hidden', 'true');
    document.body.appendChild(transitionLayer);
    root.classList.add('theme-anim');

    try {
      transitionAnimation = transitionLayer.animate([{ opacity: 0 }, { opacity: 1 }], {
        duration: 220, easing: 'cubic-bezier(0.4, 0, 1, 1)', fill: 'forwards'
      });
      await transitionAnimation.finished;
      applyTheme(theme, persist);
      transitionAnimation = transitionLayer.animate([{ opacity: 1 }, { opacity: 0 }], {
        duration: 380, easing: 'cubic-bezier(0.16, 1, 0.3, 1)', fill: 'forwards'
      });
      await transitionAnimation.finished;
    } catch (_) {
      applyTheme(theme, persist);
    } finally {
      transitionAnimation?.cancel();
      transitionAnimation = null;
      transitionLayer.remove();
      transitionLayer = null;
      root.classList.remove('theme-anim');
    }
  };

  btn.addEventListener('click', () => setTheme(isDark() ? 'light' : 'dark', true));
  reducedMotion.addEventListener('change', () => {
    if (reducedMotion.matches) transitionAnimation?.cancel();
  });
  document.addEventListener('site:accessibility-change', () => {
    if (root.classList.contains('a11y-no-motion')) transitionAnimation?.cancel();
  });
  // Follow OS changes only while the visitor hasn't picked a theme themselves
  mq.addEventListener('change', (e) => { if (!saved()) setTheme(e.matches ? 'dark' : 'light', false); });
  render();
}

document.addEventListener('DOMContentLoaded', () => {

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
  initAffiliatedCarousel();
  initHomeCalendar();
  initCardBlur();
});
