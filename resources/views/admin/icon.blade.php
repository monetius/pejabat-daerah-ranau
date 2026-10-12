<svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($name)
@case('dashboard')<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>@break
@case('news')<path d="m3 10 14-5v14L3 14zM7 16l1 5h3l-1-4M21 9v6"/>@break
@case('pages')<path d="M14 3H5v18h14V8zM14 3v6h5M8 13h8M8 17h6"/>@break
@case('menu')<path d="M8 5h13M8 12h13M8 19h13M3 5h.01M3 12h.01M3 19h.01"/>@break
@case('layout')<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 8h18M3 16h18"/>@break
@default<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8" cy="8" r="1.5"/><path d="m3 17 6-6 5 5 3-3 4 4"/>
@endswitch
</svg>
