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

// Loads Google's page-translation widget in the background and drives it
// through the language buttons in the accessibility panel instead of its default UI.
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

function initQuickCarousel() {
  // Coverflow carousel for each .quick-access-grid. Position is one continuous
  // number (pos), so the cards can follow the cursor while dragging and then
  // glide to the nearest card with easing.
  document.querySelectorAll('.quick-access-grid').forEach((grid) => {
    const cards = Array.from(grid.querySelectorAll(':scope > .quick-access-card'));
    if (cards.length < 2) return;
    const n = cards.length;
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let pos = 0;
    let W = 460;
    let raf = 0;

    grid.classList.add('qa-carousel');
    const stage = document.createElement('div');
    stage.className = 'qa-stage';

    const slides = cards.map((card) => {
      const slide = document.createElement('div');
      slide.className = 'qa-slide';
      const title = card.querySelector('h3');
      const caption = document.createElement('div');
      caption.className = 'qa-caption';
      caption.textContent = title ? title.textContent : '';
      card.draggable = false;
      card.querySelectorAll('img').forEach((im) => { im.draggable = false; });
      slide.append(card, caption);
      stage.appendChild(slide);
      return slide;
    });
    grid.appendChild(stage);

    // Phones (<= 560px) show a plain stacked list (see home.css), so the
    // coverflow must not hide, fade or intercept taps on any card there.
    const phoneMQ = window.matchMedia('(max-width: 560px)');

    const render = () => {
      if (phoneMQ.matches) {
        slides.forEach((s) => {
          s.style.removeProperty('--x');
          s.style.removeProperty('--s');
          s.style.opacity = '';
          s.style.zIndex = '';
          s.style.visibility = '';
          s.style.pointerEvents = '';
          s.dataset.pos = 0;
        });
        return;
      }
      slides.forEach((s, i) => {
        let d = i - pos;
        d -= n * Math.round(d / n);
        const a = Math.abs(d);
        const sign = d < 0 ? -1 : 1;
        const scale = a <= 1 ? 1 - 0.26 * a : Math.max(0.5, 0.74 - (a - 1) * 0.24);
        const off = a <= 1 ? a * 0.75 : 0.75 + (a - 1) * 0.25;
        const op = a <= 1 ? 1 - 0.3 * a : Math.max(0, 0.7 - (a - 1) * 1.4);
        s.style.setProperty('--x', sign * off * W + 'px');
        s.style.setProperty('--s', scale);
        s.style.opacity = op;
        s.style.zIndex = Math.max(0, Math.round(10 - a * 4));
        s.style.visibility = op <= 0 ? 'hidden' : 'visible';
        s.style.pointerEvents = op < 0.05 ? 'none' : '';
        s.dataset.pos = Math.round(d);
      });
    };

    const measure = () => { W = slides[0].offsetWidth || W; render(); };

    const animateTo = (target) => {
      cancelAnimationFrame(raf);
      if (reduced || document.documentElement.classList.contains('a11y-no-motion')) { pos = target; render(); return; }
      const from = pos;
      const dist = target - from;
      const dur = Math.min(700, 380 + Math.abs(dist) * 160);
      const t0 = performance.now();
      const tick = (now) => {
        const t = Math.min(1, (now - t0) / dur);
        const e = 1 - Math.pow(1 - t, 3); // easeOutCubic
        pos = from + dist * e;
        render();
        if (t < 1) raf = requestAnimationFrame(tick);
      };
      raf = requestAnimationFrame(tick);
    };

    const go = (step) => animateTo(Math.round(pos) + step);

    const makeArrow = (dir) => {
      const b = document.createElement('button');
      b.type = 'button';
      b.className = 'qa-arrow qa-arrow--' + (dir < 0 ? 'prev' : 'next');
      b.setAttribute('aria-label', dir < 0 ? 'Sebelumnya' : 'Seterusnya');
      b.innerHTML = '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' +
        (dir < 0 ? 'M19 12H5M12 5l-7 7 7 7' : 'M5 12h14M12 5l7 7-7 7') + '"/></svg>';
      b.addEventListener('click', () => go(dir));
      grid.appendChild(b);
    };
    makeArrow(-1);
    makeArrow(1);

    // drag / swipe: cards follow the cursor, release glides to the nearest card
    let down = false, dragging = false, startX = 0, startPos = 0, lastX = 0, lastT = 0, vel = 0, suppress = false;
    stage.addEventListener('pointerdown', (e) => {
      if (phoneMQ.matches) return;
      if (e.pointerType === 'mouse' && e.button !== 0) return;
      cancelAnimationFrame(raf);
      down = true; dragging = false;
      startX = lastX = e.clientX; lastT = performance.now();
      startPos = pos; vel = 0;
    });
    stage.addEventListener('pointermove', (e) => {
      if (!down) return;
      const dx = e.clientX - startX;
      if (!dragging && Math.abs(dx) > 6) {
        dragging = true;
        grid.classList.add('is-dragging');
        try { stage.setPointerCapture(e.pointerId); } catch (_) { }
      }
      if (!dragging) return;
      const now = performance.now();
      const dt = Math.max(1, now - lastT);
      vel = 0.8 * vel + 0.2 * ((e.clientX - lastX) / dt); // px per ms, smoothed
      lastX = e.clientX; lastT = now;
      pos = startPos - dx / (W * 0.75);
      render();
    });
    const release = () => {
      if (!down) return;
      down = false;
      if (!dragging) return;
      dragging = false;
      suppress = true;
      setTimeout(() => { suppress = false; }, 0);
      grid.classList.remove('is-dragging');
      const projected = pos - (vel * 220) / (W * 0.75); // a flick carries a bit further
      const base = Math.round(startPos);
      animateTo(Math.max(base - 1, Math.min(base + 1, Math.round(projected))));
    };
    stage.addEventListener('pointerup', release);
    stage.addEventListener('pointercancel', release);

    // a drag must not open a link; clicking a side card centres it instead
    stage.addEventListener('click', (e) => {
      if (suppress) { e.preventDefault(); e.stopPropagation(); }
    }, true);
    cards.forEach((card, i) => {
      card.addEventListener('click', (e) => {
        if (phoneMQ.matches) return; // list layout: every card is a normal link
        const off = Math.round(slides[i].dataset.pos);
        if (off !== 0) {
          e.preventDefault();
          animateTo(Math.round(pos) + off);
        }
      });
    });

    grid.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft') go(-1);
      if (e.key === 'ArrowRight') go(1);
    });

    window.addEventListener('resize', measure);
    measure();
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
  initAccessibility();
  loadGoogleTranslate();
  initOfficeHours();
  initQuickCarousel();
  initSmoothScroll();
  initCardBlur();
});