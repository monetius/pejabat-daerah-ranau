<div class="cms-language" role="group" aria-label="{{ __('Bahasa CMS') }}">
    @foreach (['ms' => ['BM', 'Bahasa Melayu'], 'en' => ['EN', 'English']] as $locale => [$label, $name])
        <form method="POST" action="{{ route('admin.locale.update') }}">
            @csrf
            <input type="hidden" name="locale" value="{{ $locale }}">
            <button type="submit" lang="{{ $locale }}" aria-label="{{ $name }}"
                aria-pressed="{{ app()->getLocale() === $locale ? 'true' : 'false' }}"
                title="{{ $name }}">{{ $label }}</button>
        </form>
    @endforeach
</div>
