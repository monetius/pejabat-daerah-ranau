<button type="button" class="theme-toggle {{ ($guest ?? false) ? 'theme-toggle-guest' : '' }}"
    data-cms-theme-toggle data-label-dark="{{ __('Mod gelap') }}" data-label-light="{{ __('Mod cerah') }}"
    data-action-dark="{{ __('Tukar ke mod gelap') }}" data-action-light="{{ __('Tukar ke mod cerah') }}"
    aria-label="{{ __('Tukar ke mod gelap') }}" title="{{ __('Tukar ke mod gelap') }}">
    <span class="theme-icon-moon">@include('admin.icon', ['name' => 'moon'])</span>
    <span class="theme-icon-sun">@include('admin.icon', ['name' => 'sun'])</span>
    <span class="theme-toggle-label" data-cms-theme-label>{{ __('Mod gelap') }}</span>
</button>
