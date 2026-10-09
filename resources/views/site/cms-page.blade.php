@extends('layouts.site')

@section('title', $page->title ?: 'Pejabat Daerah Ranau')
@section('description', $page->meta_description ?: 'Laman web rasmi Pejabat Daerah Ranau, Sabah.')
@section('body_class', $page->body_class)

@push('styles')
@foreach (preg_split('/\R/', (string) $page->styles, -1, PREG_SPLIT_NO_EMPTY) as $css)
@php($css = trim($css))
<link rel="stylesheet"
  href="{{ (str_starts_with($css, '/') || str_starts_with($css, 'http')) ? $css : asset('assets/css/' . $css) }}">
@endforeach
@endpush

@section('content')
  @if (!empty($preview))
    <div
      style="position:fixed;z-index:99999;bottom:12px;left:12px;background:#b45309;color:#fff;padding:8px 14px;border-radius:6px;font:14px system-ui">
      Pratonton - belum disimpan</div>
  @endif
  {!! $page->body !!}
@endsection

@push('scripts')
  {!! $page->scripts !!}
@endpush