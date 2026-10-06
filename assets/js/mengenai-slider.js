(function () {
    var root = document.getElementById('profil');
    if (!root) return;
    document.documentElement.classList.add('js');
    var vp = root.querySelector('.ab-viewport'),
        track = root.querySelector('.ab-track'),
        slides = [].slice.call(root.querySelectorAll('.ab-slide')),
        prev = root.querySelector('.ab-prev'),
        next = root.querySelector('.ab-next'),
        dots = [].slice.call(root.querySelectorAll('.ab-dots button')),
        i = 0;

    function go(n) {
        i = Math.max(0, Math.min(slides.length - 1, n));
        track.style.transform = 'translateX(-' + i * 100 + '%)';
        vp.style.height = slides[i].offsetHeight + 'px';
        prev.hidden = i === 0;
        next.hidden = i === slides.length - 1;
        slides.forEach(function (s, k) {
            s.setAttribute('aria-hidden', k !== i);
            if ('inert' in s) s.inert = k !== i;
        });
        dots.forEach(function (d, k) { d.setAttribute('aria-selected', k === i); });
    }

    prev.addEventListener('click', function () { go(i - 1); });
    next.addEventListener('click', function () { go(i + 1); });
    dots.forEach(function (d, k) { d.addEventListener('click', function () { go(k); }); });
    root.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowLeft') go(i - 1);
        if (e.key === 'ArrowRight') go(i + 1);
    });

    // swipe pada telefon
    var x0 = null;
    vp.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
    vp.addEventListener('touchend', function (e) {
        if (x0 === null) return;
        var dx = e.changedTouches[0].clientX - x0;
        if (Math.abs(dx) > 50) go(i + (dx < 0 ? 1 : -1));
        x0 = null;
    });

    // pautan "Perutusan / Sejarah / Senarai Pegawai" di hero
    [].forEach.call(document.querySelectorAll('[data-slide]'), function (a) {
        a.addEventListener('click', function () { go(+a.getAttribute('data-slide')); });
    });

    window.addEventListener('resize', function () { vp.style.height = slides[i].offsetHeight + 'px'; });
    window.addEventListener('load', function () { go(i); });
    go(0);
})();

// Visi & Misi: animasi bila masuk skrin
(function () {
    var els = document.querySelectorAll('.vm-from-left,.vm-from-right,.vm-fade');
    if (!els.length) return;
    if (!('IntersectionObserver' in window)) { [].forEach.call(els, function (e) { e.classList.add('vm-in'); }); return; }
    var io = new IntersectionObserver(function (list) {
        list.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('vm-in'); io.unobserve(en.target); } });
    }, { threshold: 0.2 });
    [].forEach.call(els, function (e) { io.observe(e); });
})();