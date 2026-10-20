<!doctype html>
<html lang="{{ \App\Support\HomepageTranslation::isEnglish() ? 'en' : 'ms' }}">

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
    <title>{{ \App\Support\HomepageTranslation::isEnglish() ? 'Ranau District Office' : $__env->yieldContent('title', 'Pejabat Daerah Ranau') }}</title>
    <meta name="description" content="{{ \App\Support\HomepageTranslation::isEnglish() ? 'Official website of the Ranau District Office, Sabah.' : $__env->yieldContent('description', 'Laman web rasmi Pejabat Daerah Ranau, Sabah.') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/sabahtea.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/navbar.css') }}?v={{ filemtime(public_path('assets/css/navbar.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/footer.css') }}">
    @stack('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="icon" href="{{ asset('assets/img/favicon.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('assets/css/dark.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/professional.css') }}">
</head>

<body class="@yield('body_class')">
    @include('partials.dyn', ['key' => 'navbar'])
    <main id="main">
        {!! \App\Support\HomepageTranslation::render($__env->yieldContent('content')) !!}
    </main>
    @include('partials.dyn', ['key' => 'footer'])
    @if (\App\Support\HomepageTranslation::isEnglish())
        <script type="application/json" id="homepage-labels">@json(\Illuminate\Support\Facades\Lang::get('homepage', [], 'en'))</script>
    @endif
    <script src="{{ asset('assets/js/kinomulok.js') }}?v={{ filemtime(public_path('assets/js/kinomulok.js')) }}"></script>
    @stack('scripts')
</body>

</html>
