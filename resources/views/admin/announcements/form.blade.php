@extends('admin.layout')
@section('title', $item->exists ? __('Edit Hebahan') : __('Hebahan Baru'))
@section('content')
    <h1>{{ $item->exists ? __('Edit Hebahan') : __('Hebahan Baru') }}</h1>
    <p class="page-intro">{{ __('Sediakan kandungan, pilih kategori dan tentukan masa penerbitan.') }}</p>
    <div class="card">
        <form method="POST"
            action="{{ $item->exists ? route('admin.announcements.update', $item) : route('admin.announcements.store') }}"
            enctype="multipart/form-data">
            @csrf
            @if ($item->exists) @method('PUT') @endif

            <label for="title">{{ __('Tajuk') }}</label>
            <input type="text" id="title" name="title" value="{{ old('title', $item->title) }}" required>
            @error('title')
            <div class="err">{{ $message }}</div>@enderror

            <label for="author">{{ __('Penulis / Author (pilihan)') }}</label>
            <input type="text" id="author" name="author" maxlength="150" value="{{ old('author', $item->author) }}"
                placeholder="{{ __('Nama penulis atau jabatan') }}" @disabled(!$authorEnabled)>
            <div class="hint">{{ $authorEnabled ? __('Dipaparkan bersama berita di laman web. Kosongkan jika tidak digunakan.') : __('Fungsi penulis belum diaktifkan.') }}</div>
            @error('author')
            <div class="err">{{ $message }}</div>@enderror

            <label for="category">{{ __('Kategori') }}</label>
            <select id="category" name="category">
                @foreach (\App\Models\Announcement::CATEGORIES as $v => $l)
                    <option value="{{ $v }}" @selected(old('category', $item->category ?? 'hebahan') === $v)>{{ __($l) }}</option>
                @endforeach
            </select>
            @error('category')
            <div class="err">{{ $message }}</div>@enderror

            <label for="slug">{{ __('Nama ringkas URL (pilihan)') }}</label>
            <input type="text" id="slug" name="slug" value="{{ old('slug', $item->slug) }}">
            <div class="hint">{{ __('Kosongkan untuk dijana daripada tajuk.') }}</div>
            @error('slug')
            <div class="err">{{ $message }}</div>@enderror

            <label for="summary">{{ __('Ringkasan') }}</label>
            <textarea id="summary" name="summary"
                style="min-height:80px;font-family:inherit">{{ old('summary', $item->summary) }}</textarea>
            @error('summary')
            <div class="err">{{ $message }}</div>@enderror

            <label for="image">{{ __('Gambar utama') }}</label>
            <div data-announcement-cover data-current-image="{{ $item->image }}">
                <p><img data-cover-preview @if ($item->image) src="{{ $item->image }}" @else hidden @endif alt="{{ __('Pratonton gambar') }}" style="max-width:260px;max-height:180px;object-fit:contain;border-radius:8px"></p>
                <input type="hidden" id="image_media" name="image_media" value="{{ old('image_media') }}">
                <p class="row"><button type="button" class="btn gray" data-media-select="#image_media">{{ __('Pilih daripada Media') }}</button>
                    <button type="button" class="btn gray" data-edit-selected-image="#image_media" data-current-image="{{ $item->image }}" @if (!$item->image && !old('image_media')) hidden @endif>{{ __('Potong & ubah saiz') }}</button>
                </p>
                <p class="hint">{{ __('Gunakan semula gambar yang telah dimuat naik, atau muat naik gambar baharu di bawah.') }}</p>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                <label style="font-weight:400;margin:10px 0"><input type="checkbox" name="remove_image" value="1" @checked(old('remove_image'))> {{ __('Buang gambar ini') }}</label>
                <div class="hint">{{ __('JPG, PNG atau WebP, maksimum 5 MB. Dipaparkan pada kad di laman utama dan senarai hebahan. Tanpa gambar, ikon kategori digunakan.') }}</div>
                @error('image')<div class="err">{{ $message }}</div>@enderror
                @error('image_media')<div class="err">{{ $message }}</div>@enderror
            </div>

            @include('admin.content-editor')

            <h2 class="sec">{{ __('Penerbitan') }}</h2>
            @php
                $publicationMode = old('publication_mode', $item->status === 'published'
                    ? ($item->published_at?->isFuture() ? 'scheduled' : 'now') : 'draft');
            @endphp
            <label for="publication_mode">{{ __('Pilihan penerbitan') }}</label>
            <select id="publication_mode" name="publication_mode" aria-controls="publication-schedule">
                <option value="draft" @selected($publicationMode === 'draft')>{{ __('Simpan sebagai draf') }}</option>
                <option value="now" @selected($publicationMode === 'now')>{{ __('Terbitkan sekarang') }}</option>
                <option value="scheduled" @selected($publicationMode === 'scheduled')>{{ __('Jadualkan penerbitan') }}</option>
            </select>
            @error('publication_mode')
            <div class="err">{{ $message }}</div>@enderror
            <div class="hint" data-publication-hint="draft" @if ($publicationMode !== 'draft') hidden @endif>{{ __('Disimpan untuk disemak dan tidak dipaparkan di laman web.') }}</div>
            <div class="hint" data-publication-hint="now" @if ($publicationMode !== 'now') hidden @endif>{{ __('Terbit selepas disimpan tanpa menetapkan tarikh atau masa.') }}</div>

            <div id="publication-schedule" @if ($publicationMode !== 'scheduled') hidden @endif>
                <label for="published_at">{{ __('Tarikh dan masa penerbitan berjadual (MYT)') }}</label>
                <input type="datetime-local" id="published_at" name="published_at"
                    value="{{ old('published_at', $item->published_at?->isFuture() ? $item->published_at->format('Y-m-d\TH:i') : '') }}"
                    aria-describedby="publication-schedule-hint" @required($publicationMode === 'scheduled') @disabled($publicationMode !== 'scheduled')>
                <div class="hint" id="publication-schedule-hint">{{ __('Hebahan akan dipaparkan secara automatik pada tarikh dan masa yang dipilih, mengikut waktu Malaysia.') }}</div>
                @error('published_at')
                <div class="err">{{ $message }}</div>@enderror
            </div>

            <p class="row"><button class="btn">{{ __('Simpan hebahan') }}</button><a class="btn gray"
                    href="{{ route('admin.announcements.index') }}">{{ __('Batal') }}</a></p>
        </form>
    </div>
@endsection

@push('foot')
    <script>
        (() => {
            const cover = document.querySelector('[data-announcement-cover]');
            const library = cover.querySelector('#image_media');
            const upload = cover.querySelector('#image');
            const remove = cover.querySelector('[name="remove_image"]');
            const preview = cover.querySelector('[data-cover-preview]');
            const cropButton = cover.querySelector('[data-edit-selected-image]');
            let uploadPreview = null;
            const releasePreview = () => {
                if (uploadPreview) URL.revokeObjectURL(uploadPreview);
                uploadPreview = null;
            };
            const showPreview = url => {
                const safe = /^\/(?:uploads|assets\/img)\//.test(url) && !/[\x00-\x20\\%?#]/.test(url);
                preview.hidden = !safe && url !== uploadPreview;
                cropButton.hidden = !safe;
                if (!preview.hidden && url) preview.src = url;
                else preview.removeAttribute('src');
            };
            library.addEventListener('input', () => {
                releasePreview(); upload.value = ''; remove.checked = false;
                showPreview(library.value);
            });
            upload.addEventListener('change', () => {
                releasePreview(); library.value = '';
                if (upload.files[0]) {
                    remove.checked = false;
                    uploadPreview = URL.createObjectURL(upload.files[0]);
                    showPreview(uploadPreview);
                } else showPreview(remove.checked ? '' : cover.dataset.currentImage);
            });
            remove.addEventListener('change', () => {
                if (remove.checked) {
                    releasePreview(); library.value = ''; upload.value = '';
                }
                showPreview(remove.checked ? '' : cover.dataset.currentImage);
            });
            preview.addEventListener('error', () => { preview.hidden = true; });
            showPreview(remove.checked ? '' : (library.value || cover.dataset.currentImage));

            const mode = document.getElementById('publication_mode');
            const schedule = document.getElementById('publication-schedule');
            const date = document.getElementById('published_at');
            const syncPublication = () => {
                const scheduled = mode.value === 'scheduled';
                schedule.hidden = !scheduled;
                date.disabled = !scheduled;
                date.required = scheduled;
                document.querySelectorAll('[data-publication-hint]').forEach((hint) => {
                    hint.hidden = hint.dataset.publicationHint !== mode.value;
                });
            };
            mode.addEventListener('change', syncPublication);
            syncPublication();
        })();
    </script>
@endpush
