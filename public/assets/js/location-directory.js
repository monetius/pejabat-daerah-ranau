(() => {
  const main = document.getElementById('main');
  const villages = main?.querySelector('.sk-dun');
  const facilities = main?.querySelector('.ka-jump-wrap');
  if (!main || (!villages && !facilities)) return;

  main.classList.add('location-directory');
  const isVillage = Boolean(villages);
  const sections = Array.from(main.querySelectorAll(isVillage ? '.sk-dun' : 'section[id]'));
  const entries = [];
  const normalize = (value) => value.toLocaleLowerCase('ms').normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
  const clean = (value) => value.replace(/\s+/g, ' ').trim();
  sections.forEach((section) => {
    const group = clean(section.querySelector('h2')?.textContent || 'Lain-lain');
    const rows = section.querySelectorAll(isVillage ? '.sk-card li' : '.ka-list li, .ka-table tbody tr, .ka-route, .ka-bank');
    rows.forEach((row) => {
      const school = row.matches('tr');d
      const name = clean((school ? row.cells[0] : row.querySelector(':scope > span') || row).textContent);
      const area = school ? clean(row.cells[1]?.textContent || '') : '';
      const mukim = row.closest('.sk-card')?.dataset.mukim || '';
      const query = [isVillage ? `Kampung ${name}` : name, area, 'Ranau, Sabah, Malaysia'].filter(Boolean).join(', ');
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'directory-map-button';
      button.innerHTML = '<i class="fa-solid fa-map-location-dot" aria-hidden="true"></i><span>Lihat peta</span>';
      button.setAttribute('aria-label', `Lihat peta ${name}`);
      button.setAttribute('aria-haspopup', 'dialog');
      if (school) {
        const cell = document.createElement('td');
        cell.appendChild(button);
        row.appendChild(cell);
      } else {
        const label = document.createElement('div');
        label.className = 'directory-location-name';
        while (row.firstChild) label.appendChild(row.firstChild);
        row.append(label, button);
      }
      row.classList.add('directory-location');
      const entry = { row, name, query, group, mukim, area, section, button, search: normalize(`${name} ${area} ${mukim} ${group}`) };
      entries.push(entry);
      button.addEventListener('click', () => openMap(entry));
    });
  });
  main.querySelectorAll('.ka-table thead tr').forEach((row) => {
    const heading = document.createElement('th');
    heading.scope = 'col';
    heading.textContent = 'Peta';
    row.appendChild(heading);
  });

  const intro = main.querySelector(isVillage ? '.sk-intro .container' : '.ka-jump-wrap .container');
  if (!intro || !entries.length) return;
  // The CMS's original village search remains detached with its old listener.
  main.querySelector('.sk-search')?.remove();
  main.querySelector('#sk-status')?.remove();
  const panel = document.createElement('div');
  panel.className = 'directory-toolbar';
  panel.innerHTML = `
    <div class="directory-summary"><span class="directory-eyebrow">DIREKTORI RANAU</span><h2>Cari ${isVillage ? 'kampung anda' : 'kemudahan berdekatan'}</h2><p>Pilih lokasi untuk melihat peta dan mendapatkan arah perjalanan.</p></div>
    <div class="directory-stats"><div><strong>${entries.length}</strong><span>${isVillage ? 'Kampung' : 'Entri kemudahan'}</span></div><div><strong>${sections.length}</strong><span>${isVillage ? 'DUN' : 'Kategori'}</span></div>${isVillage ? `<div><strong>${main.querySelectorAll('.sk-card').length}</strong><span>Mukim</span></div>` : ''}</div>
    <div class="directory-filters"><label class="directory-search"><span>Cari lokasi</span><input id="directory-query" type="search" placeholder="${isVillage ? 'Nama kampung atau mukim…' : 'Nama kemudahan atau lokasi…'}" autocomplete="off"></label><label><span>${isVillage ? 'DUN' : 'Kategori'}</span><select id="directory-group"><option value="">Semua ${isVillage ? 'DUN' : 'kategori'}</option></select></label><button type="button" class="directory-reset">Tetapkan semula</button></div>
    <p class="directory-results" role="status" aria-live="polite"></p>`;
  intro.prepend(panel);
  const input = panel.querySelector('input');
  const select = panel.querySelector('select');
  const status = panel.querySelector('.directory-results');
  sections.forEach((section) => {
    const option = document.createElement('option');
    option.value = section.id;
    option.textContent = clean(section.querySelector('h2').textContent);
    select.appendChild(option);
  });
  const details = Array.from(main.querySelectorAll('.ka-details'));
  const originalDetails = new Map(details.map((element) => [element, element.open]));
  const empty = document.createElement('div');
  empty.className = 'directory-empty container';
  empty.innerHTML = '<i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><h2>Tiada lokasi dijumpai</h2><p>Cuba nama lain atau tetapkan semula penapis.</p>';
  empty.hidden = true;
  intro.parentElement.after(empty);
  const filter = () => {
    const query = normalize(input.value);
    let count = 0;
    entries.forEach((entry) => {
      entry.row.hidden = !((!select.value || entry.section.id === select.value) && (!query || entry.search.includes(query)));
      if (!entry.row.hidden) count++;
    });
    main.querySelectorAll('.sk-card, .ka-card, .ka-details').forEach((card) => {
      card.hidden = !card.querySelector('.directory-location:not([hidden])');
    });
    details.forEach((element) => { element.open = query ? !element.hidden : originalDetails.get(element); });
    sections.forEach((section) => { section.hidden = !section.querySelector('.directory-location:not([hidden])'); });
    status.textContent = `${count} daripada ${entries.length} ${isVillage ? 'kampung' : 'entri'} dipaparkan`;
    empty.hidden = count !== 0;
  };
  input.addEventListener('input', filter);
  select.addEventListener('change', filter);
  panel.querySelector('.directory-reset').addEventListener('click', () => {
    input.value = '';
    select.value = '';
    filter();
    input.focus();
  });
  main.querySelectorAll('.sk-jump a, .ka-jump a').forEach((link) => {
    link.addEventListener('click', () => {
      input.value = '';
      select.value = '';
      filter();
    });
  });
  filter();

  const dialog = document.createElement('dialog');
  dialog.className = 'directory-map-dialog';
  dialog.setAttribute('aria-labelledby', 'directory-map-title');
  dialog.innerHTML = `
    <div class="directory-map-header"><div><p class="directory-eyebrow">PETA LOKASI</p><h2 id="directory-map-title"></h2><p class="directory-map-area"></p></div><button type="button" class="directory-map-close" aria-label="Tutup peta" autofocus><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></div>
    <div class="directory-map-frame"></div>
    <div class="directory-map-footer"><p>Peta berdasarkan carian nama lokasi di Google Maps. Sila semak pin sebelum memulakan perjalanan.</p><div><a class="directory-map-external" target="_blank" rel="noopener noreferrer">Buka Google Maps <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a><a class="directory-directions" target="_blank" rel="noopener noreferrer">Dapatkan arah <i class="fa-solid fa-diamond-turn-right" aria-hidden="true"></i></a></div></div>`;
  document.body.appendChild(dialog);
  let trigger = null;
  function openMap(entry) {
    trigger = entry.button;
    dialog.querySelector('h2').textContent = entry.name;
    dialog.querySelector('.directory-map-area').textContent = [entry.mukim ? `Mukim ${entry.mukim}` : entry.area, entry.group, 'Ranau, Sabah'].filter(Boolean).join(' · ');
    const frame = document.createElement('iframe');
    frame.title = `Google Maps — ${entry.name}`;
    frame.referrerPolicy = 'no-referrer-when-downgrade';
    frame.allowFullscreen = true;
    frame.src = `https://www.google.com/maps?q=${encodeURIComponent(entry.query)}&output=embed&hl=ms`;
    dialog.querySelector('.directory-map-frame').replaceChildren(frame);
    dialog.querySelector('.directory-map-external').href = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(entry.query)}`;
    dialog.querySelector('.directory-directions').href = `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(entry.query)}`;
    dialog.showModal();
    document.body.classList.add('directory-map-open');
  }
  dialog.querySelector('.directory-map-close').addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', (event) => {
    if (event.target !== dialog) return;
    const bounds = dialog.getBoundingClientRect();
    if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) dialog.close();
  });
  dialog.addEventListener('close', () => {
    dialog.querySelector('.directory-map-frame').replaceChildren();
    document.body.classList.remove('directory-map-open');
    trigger?.focus();
  });
})();
