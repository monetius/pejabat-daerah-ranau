<button type="button" class="theme-toggle {{ ($guest ?? false) ? 'theme-toggle-guest' : '' }}"
    data-cms-theme-toggle aria-label="Switch to dark mode" title="Switch to dark mode">
    <span class="theme-icon-moon">@include('admin.icon', ['name' => 'moon'])</span>
    <span class="theme-icon-sun">@include('admin.icon', ['name' => 'sun'])</span>
    <span class="theme-toggle-label" data-cms-theme-label>Dark mode</span>
</button>
