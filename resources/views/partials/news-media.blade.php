@if ($item->image)
    <img src="{{ $item->image }}" alt="" loading="lazy">
@else
    <div class="hb-ph hb-ph-{{ $item->category }}" aria-hidden="true"><i class="fa-solid {{ $item->categoryIcon() }}"></i></div>
@endif
