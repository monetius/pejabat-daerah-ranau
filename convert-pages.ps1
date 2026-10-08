$utf8 = New-Object System.Text.UTF8Encoding $false
$skipCss = 'sabahtea','navbar','footer','dark'

function Fix-Links($t) {
  $t = $t -replace '(src|href)="\.\./assets/', '${1}="/assets/'
  $t = $t -replace 'href="\.\./index\.html"', 'href="/"'
  $t = $t -replace 'href="\.\./([a-z-]+/[a-z-]+)(?:\.html)?"', 'href="/${1}"'
  return $t
}

function Convert-Page($srcPath, $section, $name) {
  $html = [System.IO.File]::ReadAllText($srcPath)
  $mm = [regex]::Match($html, '(?s)<main id="main">(.*?)</main>')
  if (-not $mm.Success) { Write-Warning "No <main> in $srcPath"; return }
  $main  = Fix-Links $mm.Groups[1].Value
  $after = $html.Substring($mm.Index + $mm.Length)
  $title = [regex]::Match($html, '<title>(.*?)</title>').Groups[1].Value
  $desc  = [regex]::Match($html, '(?s)name="description"\s+content="(.*?)"').Groups[1].Value
  $body  = [regex]::Match($html, '<body class="(.*?)"').Groups[1].Value
  $css = [regex]::Matches($html, 'href="\.\./assets/css/([\w-]+)\.css') |
         ForEach-Object { $_.Groups[1].Value } |
         Where-Object { $_ -notin $skipCss } | Select-Object -Unique
  $links = ($css | ForEach-Object { "  <link rel=`"stylesheet`" href=`"{{ asset('assets/css/$_.css') }}`">" }) -join "`n"
  $extra = [regex]::Matches($after, '(?s)<script\b.*?</script>') |
           ForEach-Object { $_.Value } | Where-Object { $_ -notmatch 'kinomulok\.js' }
  $scripts = ""
  if ($extra) { $scripts = "`n@push('scripts')`n@verbatim`n" + (Fix-Links ($extra -join "`n")) + "`n@endverbatim`n@endpush`n" }

  $out = @"
@extends('layouts.site')

@section('title')$title@endsection
@section('description')$desc@endsection
@section('body_class', '$body')

@push('styles')
$links
@endpush

@section('content')
@verbatim
$main
@endverbatim
@endsection
$scripts
"@
  $dir = "resources\views\site\$section"
  New-Item -ItemType Directory -Force $dir | Out-Null
  [System.IO.File]::WriteAllText((Join-Path (Resolve-Path $dir).Path "$name.blade.php"), $out, $utf8)
  Write-Host "OK  $section/$name"
}

Get-ChildItem legacy -Directory | Where-Object { $_.Name -ne 'assets' } | ForEach-Object {
  $section = $_.Name
  Get-ChildItem $_.FullName -Filter *.html | ForEach-Object { Convert-Page $_.FullName $section $_.BaseName }
}

# Fix links on the home page
$p = (Resolve-Path "resources\views\site\home.blade.php").Path
$c = [System.IO.File]::ReadAllText($p)
$c = $c -replace 'href="([a-z-]+/[a-z-]+)\.html"', 'href="/${1}"'
[System.IO.File]::WriteAllText($p, $c, $utf8)
Write-Host "Home links fixed."