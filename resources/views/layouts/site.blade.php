<!doctype html>
<html lang="ms">

<head>
    <script>
        (function () {
            try {
                var t = localStorage.getItem("theme");
                if (!t && window.matchMedia("(prefers-color-scheme: dark)").matches) t = "dark";
                if (t === "dark") document.documentElement.setAttribute("data-theme", "dark");
            } catch (e) { }
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pejabat Daerah Ranau')</title>
    <meta name="description" content="@yield('description', 'Laman web rasmi Pejabat Daerah Ranau, Sabah.')">
    <link rel="stylesheet" href="{{ asset('assets/css/sabahtea.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/navbar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/footer.css') }}">
    @stack('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="icon" href="{{ asset('assets/img/favicon.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('assets/css/dark.css') }}">
</head>

<body class="@yield('body_class')">
    @include('partials.navbar')
    <main id="main">
        @yield('content')
    </main>
    @include('partials.footer')
    <script src="{{ asset('assets/js/kinomulok.js') }}"></script>
    @stack('scripts')
</body>

</html>