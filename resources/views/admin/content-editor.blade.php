@once
    @push('head')
        <link rel="stylesheet" href="{{ asset('assets/css/content-editor.css') }}">
    @endpush
    @push('foot')
        <script src="{{ asset('assets/js/content-editor.js') }}" defer></script>
    @endpush
@endonce
@php
    $payload = ['version' => $editor['version'], 'fields' => array_map(fn ($field) => ['id' => $field['id'], 'value' => $field['value']], $editor['fields']), 'blocks' => $editor['blocks']];
@endphp
<div class="content-editor" data-content-editor data-media-url="{{ route('admin.media.index') }}">
    <input type="hidden" name="content" value="{{ old('content', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}" data-editor-value>
    <script type="application/json" data-editor-schema>{!! json_encode($editor, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>
    <div class="editor-intro">
        <div><h2>Kandungan</h2><p>Edit teks dan gambar melalui ruangan di bawah.</p></div>
        <span class="editor-badge">Editor mudah</span>
    </div>
    @if ($errors->any())
        <div class="errbox" role="alert">
            @foreach ($errors->all() as $message)<div>{{ $message }}</div>@endforeach
        </div>
    @endif
    <noscript><div class="errbox">Sila aktifkan JavaScript untuk menggunakan editor kandungan.</div></noscript>
    <div data-editor-existing></div>
    @if ($editor['allowBlocks'])
        <div class="editor-new-heading"><h3>{{ count($editor['fields']) ? 'Tambah kandungan' : 'Bina kandungan anda' }}</h3>
            <p>{{ count($editor['fields']) ? 'Blok baharu dipaparkan selepas kandungan sedia ada.' : 'Pilih jenis kandungan, kemudian isi teks atau pilih gambar.' }}</p>
        </div>
        <div class="editor-toolbar" role="group" aria-label="Tambah blok kandungan">
            @foreach (['heading' => 'Heading / Tajuk', 'paragraph' => 'Perenggan', 'list' => 'Senarai', 'image' => 'Gambar', 'link' => 'Pautan', 'download' => 'Muat turun'] as $type => $label)
                <button type="button" class="btn gray" data-add-block="{{ $type }}"><span aria-hidden="true">+</span> {{ $label }}</button>
            @endforeach
        </div>
        <div data-editor-blocks></div>
        <p class="editor-empty" data-editor-empty>Belum ada blok baharu. Pilih butang di atas untuk bermula.</p>
    @endif
    <p class="editor-feedback" data-editor-feedback role="status" aria-live="polite"></p>
</div>
