@extends('admin.layout')
@section('title', 'Sunting ' . $part->label)

@push('head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
@endpush

@section('content')
    <h1>{{ $part->label }}</h1>
    <div class="card">
        <form method="POST" action="{{ route('admin.parts.update', $part) }}">
            @csrf @method('PUT')
            <div class="hint" style="margin-bottom:8px">Perubahan terpakai pada <b>semua</b> halaman. Berhati-hati dengan
                tag dan nama kelas.</div>
            <textarea id="html" name="html">{{ old('html', $part->html) }}</textarea>
            @error('html')
            <div class="err">{{ $message }}</div>@enderror
            <p class="row"><button class="btn">Simpan</button><a class="btn gray"
                    href="{{ route('admin.parts.index') }}">Batal</a></p>
        </form>
    </div>
@endsection

@push('foot')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js"></script>
    <script>
        if (window.CodeMirror) {
            CodeMirror.fromTextArea(document.getElementById('html'), { mode: 'htmlmixed', lineNumbers: true, lineWrapping: true });
        }
    </script>
@endpush