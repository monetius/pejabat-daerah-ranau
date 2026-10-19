<svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($name)
@case('settings')<polygon points="18.93,9.13 21.78,9.92 21.78,14.08 18.93,14.87 20.39,17.45 17.45,20.39 14.87,18.93 14.08,21.78 9.92,21.78 9.13,18.93 6.55,20.39 3.61,17.45 5.07,14.87 2.22,14.08 2.22,9.92 5.07,9.13 3.61,6.55 6.55,3.61 9.13,5.07 9.92,2.22 14.08,2.22 14.87,5.07 17.45,3.61 20.39,6.55 18.93,9.13"/><circle cx="12" cy="12" r="3.2"/>@break
@case('security')<path d="m12 3 8 3v6c0 5-8 9-8 9s-8-4-8-9V6zM8 12l3 3 5-6"/>@break
@case('dashboard')<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>@break
@case('news')<path d="m3 10 14-5v14L3 14zM7 16l1 5h3l-1-4M21 9v6"/>@break
@case('calendar')<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4M17 3v4M3 11h18M7 15h2M13 15h2M7 18h2"/>@break
@case('pages')<path d="M14 3H5v18h14V8zM14 3v6h5M8 13h8M8 17h6"/>@break
@case('menu')<path d="M8 5h13M8 12h13M8 19h13M3 5h.01M3 12h.01M3 19h.01"/>@break
@case('layout')<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 8h18M3 16h18"/>@break
@case('moon')<path d="M20.9 13A9 9 0 0 1 11 3.1 9 9 0 1 0 20.9 13Z"/>@break
@case('sun')<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>@break
@default<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8" cy="8" r="1.5"/><path d="m3 17 6-6 5 5 3-3 4 4"/>
@endswitch
</svg>
