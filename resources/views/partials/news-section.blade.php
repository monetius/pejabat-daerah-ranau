@php
    $__news = \App\Models\Announcement::published()->orderByDesc('published_at')->orderByDesc('id')->take(4)->get();
    $__feature = $__news->first();
    $__side = $__news->slice(1);
@endphp
<link rel="stylesheet" href="{{ asset('assets/css/hebahan.css') }}">
<section class="hb-section" id="hebahan-berita">
    <div class="container">
        <div class="hb-head">
            <span aria-hidden="true"></span>
            <h2>Hebahan &amp; Berita</h2>
            <a class="hb-all" href="{{ route('announcements.index') }}">Lihat semua <span aria-hidden="true">→</span></a>
        </div>

        @if ($__feature)
            <div class="hb-grid">
                <a class="hb-feature" href="{{ route('announcements.show', $__feature->slug) }}">
                    <div class="hb-feature-media">@include('partials.news-media', ['item' => $__feature])</div>
                    <div class="hb-feature-body">
                        <h3>{{ $__feature->title }}</h3>
                        <div class="hb-meta">
                            <span class="hb-tag hb-tag-{{ $__feature->category }}">{{ $__feature->categoryLabel() }}</span>
                            <time>{{ $__feature->published_at?->format('d/m/Y') }}</time>
                        </div>
                    </div>
                </a>

                <div class="hb-side">
                    @foreach ($__side as $item)
                        <a class="hb-card" href="{{ route('announcements.show', $item->slug) }}">
                            <div class="hb-card-text">
                                <h4>{{ $item->title }}</h4>
                                <div class="hb-meta">
                                    <span class="hb-tag hb-tag-{{ $item->category }}">{{ $item->categoryLabel() }}</span>
                                    <time>{{ $item->published_at?->format('d/m/Y') }}</time>
                                </div>
                            </div>
                            <div class="hb-card-media">@include('partials.news-media', ['item' => $item])</div>
                        </a>
                    @endforeach
                </div>
            </div>
        @else
            <p class="hb-empty">Tiada hebahan atau berita buat masa ini.</p>
        @endif
    </div>
</section>
