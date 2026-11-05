@php
    $__news = \App\Models\Announcement::published()->orderByDesc('published_at')->orderByDesc('id')->take(5)->get();
    $__feature = $__news->first();
    $__side = $__news->slice(1);
@endphp
<link rel="stylesheet" href="{{ asset('assets/css/hebahan.css') }}?v={{ filemtime(public_path('assets/css/hebahan.css')) }}">
<section class="hb-news" id="hebahan-berita" aria-labelledby="hb-news-title">
    <div class="hb-news-wrap">
        <div class="hb-news-head">
            <div>
                <p class="hb-news-eyebrow">Terkini</p>
                <h2 id="hb-news-title">Hebahan &amp; Berita</h2>
            </div>
            <a class="hb-news-all" href="{{ route('announcements.index') }}">Lihat semua
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>

        @if ($__feature)
            <div class="hb-news-grid">
                <a class="hb-news-feature" href="{{ route('announcements.show', $__feature->slug) }}" data-reveal>
                    <div class="hb-news-feature-media">@include('partials.news-media', ['item' => $__feature])</div>
                    <div class="hb-news-feature-body">
                        <div class="hb-news-meta">
                            <span class="hb-news-tag hb-news-tag--{{ $__feature->category }}">{{ $__feature->categoryLabel() }}</span>
                            <time datetime="{{ $__feature->published_at?->toDateString() }}">{{ $__feature->published_at?->translatedFormat('j F Y') }}</time>
                            @include('partials.news-author', ['item' => $__feature])
                        </div>
                        <h3>{{ $__feature->title }}</h3>
                        @if (filled($__feature->summary))
                            <p>{{ \Illuminate\Support\Str::limit($__feature->summary, 220) }}</p>
                        @endif
                    </div>
                </a>

                @if ($__side->isNotEmpty())
                    <div class="hb-news-list">
                        @foreach ($__side as $item)
                            <a class="hb-news-item" href="{{ route('announcements.show', $item->slug) }}" data-reveal>
                                <div class="hb-news-item-media">@include('partials.news-media', ['item' => $item])</div>
                                <div>
                                    <div class="hb-news-meta">
                                        <span class="hb-news-tag hb-news-tag--{{ $item->category }}">{{ $item->categoryLabel() }}</span>
                                        <time datetime="{{ $item->published_at?->toDateString() }}">{{ $item->published_at?->translatedFormat('j M Y') }}</time>
                                    </div>
                                    <h4>{{ $item->title }}</h4>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @else
            <p class="hb-news-empty">Tiada hebahan atau berita buat masa ini.</p>
        @endif
    </div>
</section>
