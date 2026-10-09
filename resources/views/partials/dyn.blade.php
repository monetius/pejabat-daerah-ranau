@php($__html = \App\Models\SitePart::htmlFor($key))
@if ($__html !== null){!! $__html !!}@else @include('partials.' . $key)@endif