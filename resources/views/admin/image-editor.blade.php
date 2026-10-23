@once
    @push('head')
        <link rel="stylesheet" href="{{ asset('assets/css/image-editor.css') }}?v={{ filemtime(public_path('assets/css/image-editor.css')) }}">
    @endpush
    @push('foot')
        <script type="application/json" id="image-editor-translations">@json(app('translation.loader')->load(app()->getLocale(), '*', '*'))</script>
        <script src="{{ asset('assets/js/image-editor.js') }}?v={{ filemtime(public_path('assets/js/image-editor.js')) }}" data-image-editor-url="{{ route('admin.media.store') }}" defer></script>
    @endpush
@endonce
