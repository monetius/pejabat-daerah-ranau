@php($__html = \App\Models\SitePart::htmlFor($key))
@if ($__html === null)
@php($__html = view('partials.' . $key)->render())
@endif
{!! $key === 'navbar' ? \App\Models\MenuItem::inject($__html) : $__html !!}
