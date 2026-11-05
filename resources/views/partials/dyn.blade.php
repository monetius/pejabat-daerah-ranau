@php($__html = \App\Models\SitePart::htmlFor($key))
@if ($__html === null)
@php($__html = view('partials.' . $key)->render())
@endif
{!! match ($key) {
    'navbar' => \App\Support\SiteNavigation::render(\App\Models\MenuItem::inject($__html)),
    'footer' => \App\Support\Shortcodes::renderFooter($__html),
    default => $__html,
} !!}
