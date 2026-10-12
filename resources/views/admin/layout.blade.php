<!doctype html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - CMS PD Ranau</title>
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
    @stack('head')
</head>

<body class="{{ auth()->check() ? 'has-side' : 'login-page' }}">
    @auth
        <button class="side-toggle" type="button" aria-label="Buka menu" aria-controls="admin-sidebar" aria-expanded="false"
            onclick="toggleSidebar(!document.body.classList.contains('side-open'))">&#9776;</button>
        <button class="side-backdrop" type="button" aria-label="Tutup menu" onclick="toggleSidebar(false)"></button>
        <aside class="side" id="admin-sidebar">
            <div class="brand"><span class="brand-mark">R</span><div>PD Ranau<small>Pengurusan kandungan</small></div></div><p class="nav-caption">RUANG KERJA</p>
            <nav aria-label="Navigasi pentadbir">
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'on' : '' }}">@include('admin.icon', ['name' => 'dashboard'])<span>Papan Pemuka</span></a>
                <a href="{{ route('admin.announcements.index') }}" class="{{ request()->routeIs('admin.announcements.*') ? 'on' : '' }}">@include('admin.icon', ['name' => 'news'])<span>Hebahan</span></a>
                <a href="{{ route('admin.pages.index') }}" class="{{ request()->routeIs('admin.pages.*') ? 'on' : '' }}">@include('admin.icon', ['name' => 'pages'])<span>Halaman</span></a>
                <a href="{{ route('admin.menu.index') }}" class="{{ request()->routeIs('admin.menu.*') ? 'on' : '' }}">@include('admin.icon', ['name' => 'menu'])<span>Menu</span></a>
                <a href="{{ route('admin.parts.index') }}" class="{{ request()->routeIs('admin.parts.*') ? 'on' : '' }}">@include('admin.icon', ['name' => 'layout'])<span>Navbar &amp; Footer</span></a>
                <a href="{{ route('admin.media.index') }}" class="{{ request()->routeIs('admin.media.*') ? 'on' : '' }}">@include('admin.icon', ['name' => 'media'])<span>Media</span></a>
            </nav>
            <div class="foot">
                <a href="/" target="_blank">Lihat laman web</a>
                <form method="POST" id="logout-form" action="{{ route('admin.logout') }}">@csrf<button class="btn link" style="padding:9px 12px">Log keluar</button></form>
            </div>
        </aside>
    @endauth
    @auth
        <header class="topbar"><span>Pejabat Daerah Ranau <span class="topbar-divider">/</span> <strong>@yield('title', 'CMS')</strong></span><span class="user-chip"><span class="avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>{{ auth()->user()->name }}</span></header>
    @endauth
    <main class="wrap" id="admin-main">
        @if ($errors->has('delete'))
        <div class="errbox">{{ $errors->first('delete') }}</div>@endif
        @if (session('status'))
        <div class="flash">{{ session('status') }}</div>@endif
        @yield('content')
    </main>
    @auth
        <div id="idle-warn" hidden
            style="position:fixed;right:16px;bottom:16px;z-index:100;background:#fff7ed;border:1px solid #fdba74;color:#7c2d12;padding:12px 16px;border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,.18);max-width:320px">
            Anda akan dilog keluar dalam <b id="idle-sec">60</b> saat kerana tiada aktiviti.
            <div style="margin-top:8px"><button type="button" class="btn" id="idle-stay">Kekal log masuk</button></div>
        </div>
        <script>
            (function () {
                var LIMIT = {{ max(1, (int) config('cms.idle_minutes', 5)) }} * 60000;
                var WARN = Math.min(60000, LIMIT / 2);
                var PING_EVERY = 60000;
                var KEY = 'cms_last_activity';
                var last = Date.now(), lastPing = Date.now(), warned = false, done = false;
                var box = document.getElementById('idle-warn'), sec = document.getElementById('idle-sec');
                var token = document.querySelector('meta[name="csrf-token"]').content;

                function shared() { try { return +localStorage.getItem(KEY) || 0; } catch (e) { return 0; } }
                function ping() {
                    lastPing = Date.now();
                    fetch('{{ route('admin.keepalive') }}', {
                        method: 'POST', credentials: 'same-origin',
                        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    }).then(function (r) { if (r.status === 401 || r.status === 419 || r.redirected) location.reload(); })
                      .catch(function () {});
                }
                function active() {
                    if (done) return;
                    last = Date.now();
                    try { localStorage.setItem(KEY, String(last)); } catch (e) {}
                    if (Date.now() - lastPing > (warned ? 0 : PING_EVERY)) ping();
                }
                ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click'].forEach(function (ev) {
                    document.addEventListener(ev, active, { capture: true, passive: true });
                });
                document.getElementById('idle-stay').addEventListener('click', active);

                setInterval(function () {
                    if (done) return;
                    var idle = Date.now() - Math.max(last, shared());
                    if (idle >= LIMIT) {
                        done = true;
                        document.getElementById('logout-form').submit();
                    } else if (idle >= LIMIT - WARN) {
                        warned = true;
                        sec.textContent = Math.ceil((LIMIT - idle) / 1000);
                        box.hidden = false;
                    } else if (warned) {
                        warned = false;
                        box.hidden = true;
                    }
                }, 1000);
            })();
        </script>
    @endauth
    <script>
        function toggleSidebar(open) {
            document.body.classList.toggle('side-open', open);
            const button = document.querySelector('.side-toggle');
            if (button) button.setAttribute('aria-expanded', String(open));
        }
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') { toggleSidebar(false); document.querySelector('.side-toggle')?.focus(); }
        });
        document.querySelectorAll('.card > table').forEach(function (table) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-scroll'; wrapper.tabIndex = 0;
            wrapper.setAttribute('role', 'region'); wrapper.setAttribute('aria-label', 'Jadual kandungan');
            table.before(wrapper); wrapper.append(table);
        });
    </script>
    @stack('foot')
</body>

</html>