@if (filled($item->author))
  <span class="hb-author" title="{{ $item->author }}">{{ \App\Support\HomepageTranslation::isEnglish() ? 'By' : 'Oleh' }} {{ $item->author }}</span>
@endif
