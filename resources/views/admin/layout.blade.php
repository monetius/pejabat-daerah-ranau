<!doctype html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') - CMS PD Ranau</title>
    <style>
        * {
            box-sizing: border-box
        }

        body {
            margin: 0;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            background: #f3f4f6;
            color: #1f2937
        }

        a {
            color: #0b5ed7;
            text-decoration: none
        }

        .top {
            background: #14304f;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 12px 24px;
            flex-wrap: wrap
        }

        .top strong {
            margin-right: 12px
        }

        .top a {
            color: #dbe7f5
        }

        .top a:hover {
            color: #fff
        }

        .top form {
            margin-left: auto
        }

        .wrap {
            max-width: 960px;
            margin: 24px auto;
            padding: 0 16px
        }

        .card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
            margin-bottom: 20px
        }

        h1 {
            margin: 0 0 16px;
            font-size: 1.5rem
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th,
        td {
            text-align: left;
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top
        }

        label {
            display: block;
            font-weight: 600;
            margin: 14px 0 6px
        }

        input[type=text],
        input[type=email],
        input[type=password],
        input[type=datetime-local],
        select,
        textarea {
            width: 100%;
            padding: 9px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font: inherit
        }

        textarea {
            min-height: 260px;
            font-family: ui-monospace, Consolas, monospace;
            font-size: .9rem
        }

        .btn {
            display: inline-block;
            background: #0b5ed7;
            color: #fff;
            border: 0;
            border-radius: 6px;
            padding: 9px 16px;
            font: inherit;
            cursor: pointer
        }

        .btn.gray {
            background: #64748b
        }

        .btn.red {
            background: #c62828
        }

        .btn.link {
            background: none;
            color: #fff;
            padding: 0
        }

        .flash {
            background: #dcfce7;
            border: 1px solid #86efac;
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 16px
        }

        .err {
            color: #b91c1c;
            font-size: .875rem;
            margin-top: 4px
        }

        .badge {
            padding: 2px 8px;
            border-radius: 99px;
            font-size: .78rem;
            background: #e5e7eb
        }

        .badge.published {
            background: #dcfce7;
            color: #166534
        }

        .row {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap
        }

        .hint {
            color: #64748b;
            font-size: .85rem;
            margin-top: 4px
        }

        .stats {
            display: flex;
            gap: 16px;
            flex-wrap: wrap
        }

        .stats .card {
            flex: 1;
            min-width: 160px;
            margin: 0
        }

        .stats b {
            font-size: 2rem;
            display: block
        }

        .errbox {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 16px
        }

        .grid2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0 16px
        }

        @media(max-width:700px) {
            .grid2 {
                grid-template-columns: 1fr
            }
        }

        .CodeMirror {
            height: 480px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 13px
        }

        .CodeMirror.short {
            height: 140px
        }

        h2.sec {
            font-size: 1.05rem;
            margin: 22px 0 8px;
            color: #14304f
        }

        code {
            background: #eef2f7;
            padding: 1px 5px;
            border-radius: 4px
        }
    </style>
    @stack('head')
</head>

<body>
    @auth
        <div class="top">
            <strong>CMS PD Ranau</strong>
            <a href="{{ route('admin.dashboard') }}">Papan Pemuka</a>
            <a href="{{ route('admin.announcements.index') }}">Hebahan</a>
            <a href="{{ route('admin.pages.index') }}">Halaman</a>
            <a href="{{ route('admin.parts.index') }}">Navbar &amp; Footer</a>
            <a href="{{ route('admin.media.index') }}">Media</a>
            <a href="/" target="_blank">Lihat laman web</a>
            <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="btn link">Log keluar</button>
            </form>
        </div>
    @endauth
    <div class="wrap">
        @if ($errors->has('delete'))
        <div class="errbox">{{ $errors->first('delete') }}</div>@endif
        @if (session('status'))
        <div class="flash">{{ session('status') }}</div>@endif
        @yield('content')
    </div>
    @stack('foot')
</body>

</html>