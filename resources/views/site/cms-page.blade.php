@extends('layouts.site')

@section('title', $page->title ?: 'Pejabat Daerah Ranau')
@section('description', $page->meta_description ?: 'Laman web rasmi Pejabat Daerah Ranau, Sabah.')
@section('body_class', $page->body_class ?? '')

@push('styles')
@foreach (preg_split('/\R/', (string) $page->styles, -1, PREG_SPLIT_NO_EMPTY) as $css)
@php($css = trim($css))
<link rel="stylesheet"
  href="{{ (str_starts_with($css, '/') || str_starts_with($css, 'http')) ? $css : asset('assets/css/' . $css) }}">
@endforeach
@if (in_array($page->path, ['profil-daerah/senarai-kampung', 'profil-daerah/kemudahan-awam'], true))
<link rel="stylesheet" href="{{ asset('assets/css/location-directory.css') }}?v={{ filemtime(public_path('assets/css/location-directory.css')) }}">
@endif
@if ($page->path === 'info-korporat/mengenai-kami')
<link rel="stylesheet" href="{{ asset('assets/css/terrain-compass.css') }}">
@endif
@endpush

@section('content')
  @if (!empty($preview))
    <div
      style="position:fixed;z-index:99999;bottom:12px;left:12px;background:#b45309;color:#fff;padding:8px 14px;border-radius:6px;font:14px system-ui">
      {{ __('Pratonton - belum disimpan') }}</div>
  @endif
  {!! \App\Support\Shortcodes::render($page->body) !!}
  @if ($page->path === 'info-korporat/mengenai-kami')
    @include('partials.terrain-compass')
  @endif
@endsection

@push('scripts')
  {!! $page->scripts !!}
  @if (in_array($page->path, ['profil-daerah/senarai-kampung', 'profil-daerah/kemudahan-awam'], true))
    <script src="{{ asset('assets/js/location-directory.js') }}?v={{ filemtime(public_path('assets/js/location-directory.js')) }}" defer></script>
  @endif
  @if ($page->path === 'info-korporat/mengenai-kami')
    <script src="{{ asset('assets/js/terrain-compass.js') }}" defer></script>
  @endif
@endpush
