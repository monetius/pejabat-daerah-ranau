<form class="nav-language-switch notranslate" data-site-language-form method="POST" action="{{ route('site.locale.update') }}" aria-label="{{ $locale === 'en' ? 'Language' : 'Bahasa' }}" translate="no">
    @csrf
    <span class="nav-language-caption">Bahasa / Language</span>
    <input type="hidden" name="translate_page" value="0">
    <button type="button" id="langToggle" aria-label="Bahasa / Language" aria-expanded="false" aria-controls="langMenu">
        <i class="fa-solid fa-language" aria-hidden="true"></i>
        <span data-language-label>{{ $locale === 'en' ? 'EN' : 'BM' }}</span>
        <span aria-hidden="true">▾</span>
    </button>
    <div class="nav-language-menu" id="langMenu" hidden>
        <button type="submit" name="locale" value="ms" data-lang="ms" lang="ms" aria-pressed="{{ $locale === 'ms' ? 'true' : 'false' }}">Bahasa Melayu</button>
        <button type="submit" name="locale" value="en" data-lang="en" lang="en" aria-pressed="{{ $locale === 'en' ? 'true' : 'false' }}">English</button>
        <button type="button" data-lang="zh-CN" lang="zh-CN" aria-pressed="false">中文</button>
        <button type="button" data-lang="ta" lang="ta" aria-pressed="false">தமிழ்</button>
        <button type="button" data-lang="ko" lang="ko" aria-pressed="false">한국어</button>
    </div>
</form>
