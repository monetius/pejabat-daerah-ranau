// Carousel "Perutusan / Sejarah / Senarai Pegawai"
// Kad ditengahkan, kad jiran kelihatan separuh; boleh diseret (jari atau tetikus).
(function () {
    var root = document.getElementById('profil');
    if (!root) return;
    document.documentElement.classList.add('js');

    var vp = root.querySelector('.ab-viewport'),
        track = root.querySelector('.ab-track'),
        slides = [].slice.call(root.querySelectorAll('.ab-slide')),
        prevs = [].slice.call(root.querySelectorAll('.ab-prev')),
        nexts = [].slice.call(root.querySelectorAll('.ab-next')),
        dots = [].slice.call(root.querySelectorAll('.ab-dots button')),
        count = root.querySelector('.ab-count b'),
        last = slides.length - 1,
        i = 0,
        cur = 0;

    function pad(n) { return (n < 10 ? '0' : '') + n; }

    function place() {
        var s = slides[i];
        // letak kad aktif di tengah paparan
        cur = (vp.clientWidth - s.offsetWidth) / 2 - s.offsetLeft;
        track.style.transform = 'translate3d(' + cur + 'px,0,0)';
        var cs = getComputedStyle(vp);
        vp.style.height = (s.offsetHeight + parseFloat(cs.paddingTop) + parseFloat(cs.paddingBottom)) + 'px';
    }

    function go(n) {
        i = Math.max(0, Math.min(last, n));
        place();
        prevs.forEach(function (b) { b.disabled = i === 0; });
        nexts.forEach(function (b) { b.disabled = i === last; });
        if (count) count.textContent = pad(i + 1);
        slides.forEach(function (s, k) {
            s.classList.toggle('is-active', k === i);
            s.classList.toggle('is-before', k < i);
            s.classList.toggle('is-after', k > i);
            s.setAttribute('aria-hidden', k !== i);
        });
        dots.forEach(function (d, k) { d.setAttribute('aria-selected', k === i); });
    }

    prevs.forEach(function (b) { b.addEventListener('click', function () { go(i - 1); }); });
    nexts.forEach(function (b) { b.addEventListener('click', function () { go(i + 1); }); });
    dots.forEach(function (d, k) { d.addEventListener('click', function () { go(k); }); });
    root.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowLeft') go(i - 1);
        if (e.key === 'ArrowRight') go(i + 1);
    });

    // seret: ikut jari/tetikus, lepas = snap ke kad terdekat
    var down = false, moved = false, sx = 0, sy = 0, dx = 0, pid = null, blockClick = false;

    [].forEach.call(track.querySelectorAll('img'), function (im) { im.draggable = false; });

    vp.addEventListener('pointerdown', function (e) {
        if (e.pointerType === 'mouse' && e.button !== 0) return;
        down = true; moved = false; dx = 0; sx = e.clientX; sy = e.clientY; pid = e.pointerId;
    });
    vp.addEventListener('pointermove', function (e) {
        if (!down || e.pointerId !== pid) return;
        dx = e.clientX - sx;
        if (!moved) {
            // tunggu sehingga jelas ini gerakan mengufuk (bukan skrol menegak)
            if (Math.abs(dx) < 8 || Math.abs(dx) < Math.abs(e.clientY - sy)) return;
            moved = true;
            track.classList.add('is-dragging');
            try { vp.setPointerCapture(pid); } catch (err) { }
        }
        var d = ((i === 0 && dx > 0) || (i === last && dx < 0)) ? dx * 0.3 : dx;
        track.style.transform = 'translate3d(' + (cur + d) + 'px,0,0)';
    });
    function release(e) {
        if (!down || (e && e.pointerId !== pid)) return;
        down = false;
        track.classList.remove('is-dragging');
        if (!moved) return;
        blockClick = true;
        setTimeout(function () { blockClick = false; }, 0);
        var limit = Math.min(80, vp.clientWidth * 0.15);
        go(Math.abs(dx) > limit ? i + (dx < 0 ? 1 : -1) : i);
    }
    vp.addEventListener('pointerup', release);
    vp.addEventListener('pointercancel', release);

    // klik kad jiran = pergi ke kad itu
    track.addEventListener('click', function (e) {
        if (blockClick) { e.preventDefault(); e.stopPropagation(); return; }
        var s = e.target.closest('.ab-slide');
        if (s && !s.classList.contains('is-active')) go(slides.indexOf(s));
    }, true);

    // pautan "Perutusan / Sejarah / Senarai Pegawai" di hero
    [].forEach.call(document.querySelectorAll('[data-slide]'), function (a) {
        a.addEventListener('click', function () { go(+a.getAttribute('data-slide')); });
    });

    // kekalkan kedudukan bila saiz skrin / kandungan berubah
    function relayout() {
        track.classList.add('no-anim');
        place();
        void track.offsetWidth;
        track.classList.remove('no-anim');
    }
    window.addEventListener('resize', relayout);
    window.addEventListener('load', relayout);
    if ('ResizeObserver' in window) {
        var ro = new ResizeObserver(function () { place(); });
        slides.forEach(function (s) { ro.observe(s); });
    }
    relayout();
    go(0);
})();

// Visi & Misi: animasi mengikut skrol. Skrol ke bawah = masuk; skrol ke atas
// (atau lepasi bahagian ini) = undur semula, dan main lagi bila dilihat semula.
(function () {
    var els = [].slice.call(document.querySelectorAll('.vm-from-left,.vm-from-right,.vm-fade'));
    if (!els.length) return;
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce) { els.forEach(function (e) { e.style.opacity = 1; e.style.transform = 'none'; }); return; }

    function clamp(v) { return v < 0 ? 0 : v > 1 ? 1 : v; }
    function ease(t) { return t * t * (3 - 2 * t); }

    var ticking = false;
    function update() {
        ticking = false;
        var vh = window.innerHeight, small = window.innerWidth <= 760, dist = small ? 48 : 140,
            ramp = small ? 0.14 : 0.22;
        var nav = document.querySelector('.navbar');
        var line = nav ? nav.getBoundingClientRect().bottom : 100;
        els.forEach(function (el) {
            var r = el.getBoundingClientRect(), c = r.top + r.height / 2;
            var shift = el.classList.contains('vm-fade') ? 0.03 : 0;
            // masuk dan keluar dicerminkan pada garis tengah skrin. Ramp masuk 22% (14% pada telefon) supaya ada
            // jalur tengah yang panjang di mana Visi DAN Misi kelihatan penuh serentak.
            var pin = clamp((vh * (0.98 - shift) - c) / (vh * ramp));
            // keluar: hanya bermula bila bahagian atas elemen sampai ke navbar, dan siap bila ia
            // habis melepasinya (sama seperti kesan blur kad di tempat lain)
            var d = line + r.height;   // pudar sepanjang laluan sehingga keluar skrin
            var pout = clamp(1 - (line - r.top) / d);
            var p = ease(Math.min(pin, pout)), q = 1 - p, tx = 0, ty = 0;
            if (el.classList.contains('vm-from-left')) tx = -dist * q;
            else if (el.classList.contains('vm-from-right')) tx = dist * q;
            else ty = 24 * q;
            el.style.opacity = p;
            el.style.transform = 'translate3d(' + tx + 'px,' + ty + 'px,0)';
        });
    }
    function queue() { if (!ticking) { ticking = true; requestAnimationFrame(update); } }
    window.addEventListener('scroll', queue, { passive: true });
    window.addEventListener('resize', queue);
    update();
})();