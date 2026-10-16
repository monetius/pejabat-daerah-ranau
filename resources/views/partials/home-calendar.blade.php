<aside class="home-calendar" data-home-calendar data-calendar-url="{{ route('calendar.data', absolute: false) }}" aria-labelledby="home-calendar-title">
    <div class="home-calendar-head">
        <div><span class="home-calendar-eyebrow">RANAU · SABAH</span><h3 id="home-calendar-title">Kalendar</h3><p>Program, acara &amp; cuti umum</p></div>
        <button type="button" class="home-calendar-today" data-calendar-today>Hari ini</button>
    </div>
    <div class="home-calendar-navigation">
        <button type="button" data-calendar-previous aria-label="Bulan sebelumnya" aria-controls="home-calendar-dates"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
        <p id="home-calendar-month" aria-live="polite" aria-atomic="true">Kalendar bulanan</p>
        <button type="button" data-calendar-next aria-label="Bulan seterusnya" aria-controls="home-calendar-dates"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
    </div>
    <table class="home-calendar-table" aria-labelledby="home-calendar-month">
        <thead><tr><th scope="col"><abbr title="Isnin">Isn</abbr></th><th scope="col"><abbr title="Selasa">Sel</abbr></th><th scope="col"><abbr title="Rabu">Rab</abbr></th><th scope="col"><abbr title="Khamis">Kha</abbr></th><th scope="col"><abbr title="Jumaat">Jum</abbr></th><th scope="col"><abbr title="Sabtu">Sab</abbr></th><th scope="col"><abbr title="Ahad">Aha</abbr></th></tr></thead>
        <tbody id="home-calendar-dates" data-calendar-dates></tbody>
    </table>
    <div class="home-calendar-legend"><span><i class="calendar-dot program" aria-hidden="true"></i> Program</span><span><i class="calendar-dot holiday" aria-hidden="true"></i> Cuti umum</span><span class="home-calendar-timezone">MYT</span></div>
    <div class="home-calendar-agenda" id="home-calendar-agenda" data-calendar-agenda aria-live="polite" aria-atomic="true"><p class="home-calendar-empty">Memuatkan kalendar...</p></div>
    <p class="home-calendar-note" data-calendar-note></p>
    <a class="home-calendar-source" data-calendar-source href="https://sabah.gov.my/public-holidays" target="_blank" rel="noopener noreferrer">Rujukan cuti umum Sabah &amp; Malaysia ↗</a>
    <noscript><p>Aktifkan JavaScript untuk melihat kalendar.</p></noscript>
</aside>
