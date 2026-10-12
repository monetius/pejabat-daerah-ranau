@foreach ($items as $item)
    @php($kids = $item->children->filter(fn ($c) => $c->isShown()))
    @if ($kids->isNotEmpty())
        <li class="dropdown">
            <button class="dropdown-toggle" aria-expanded="false" aria-haspopup="true">
                {{ $item->label }}
                <span class="arrow" aria-hidden="true">&#9662;</span>
            </button>
            <ul class="dropdown-menu">
                @foreach ($kids as $kid)
                    <li><a href="{{ $kid->href() }}" @if ($kid->new_tab) target="_blank" rel="noopener" @endif>{{ $kid->label }}</a></li>
                @endforeach
            </ul>
        </li>
    @elseif ($item->isShown())
        <li><a href="{{ $item->href() }}" @if ($item->new_tab) target="_blank" rel="noopener" @endif>{{ $item->label }}</a></li>
    @endif
@endforeach
