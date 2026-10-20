<form class="nav-language-switch" data-site-language-form method="POST" action="{{ route('site.locale.update') }}" aria-label="{{ $locale === 'en' ? 'Navigation language' : 'Bahasa navigasi' }}">
    @csrf
    <input type="hidden" name="translate_page" value="0">
    <button type="submit" name="locale" value="ms" lang="ms" aria-label="Bahasa Melayu" aria-pressed="{{ $locale === 'ms' ? 'true' : 'false' }}">BM</button>
    <button type="submit" name="locale" value="en" lang="en" aria-label="English" aria-pressed="{{ $locale === 'en' ? 'true' : 'false' }}">EN</button>
</form>
