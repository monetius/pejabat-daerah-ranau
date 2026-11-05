@php($__latest = \App\Models\Announcement::published()->orderByDesc('published_at')->orderByDesc('id')->first())
@if ($__latest)
    <a class="hm-live-item" href="{{ route('announcements.show', $__latest->slug) }}">
        <svg class="hm-live-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 11v2a1 1 0 0 0 1 1h2l5 4V6L6 10H4a1 1 0 0 0-1 1z"/><path d="M15 9a4 4 0 0 1 0 6M18 6a8 8 0 0 1 0 12"/></svg>
        <span class="hm-live-text"><strong>{{ $__latest->title }}</strong><span class="hm-live-sub">{{ $__latest->categoryLabel() }} terkini · {{ $__latest->published_at?->locale('ms')->diffForHumans() }}</span></span>
    </a>
@else
    <a class="hm-live-item" href="{{ route('announcements.index') }}">
        <svg class="hm-live-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 11v2a1 1 0 0 0 1 1h2l5 4V6L6 10H4a1 1 0 0 0-1 1z"/><path d="M15 9a4 4 0 0 1 0 6M18 6a8 8 0 0 1 0 12"/></svg>
        <span class="hm-live-text"><strong>Hebahan &amp; Berita</strong><span class="hm-live-sub">Semua pengumuman Pejabat Daerah Ranau</span></span>
    </a>
@endif
