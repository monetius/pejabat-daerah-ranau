(() => {
  const history = document.getElementById('sejarah-daerah');
  const template = document.getElementById('terrain-compass-template');
  const terrainCards = Array.from(document.querySelectorAll('#muka-bumi .md-dir'));
  if (!history || !template || terrainCards.length < 3 || history.querySelector('.terrain-compass')) return;

  const fragment = template.content.cloneNode(true);
  const compass = fragment.querySelector('.terrain-compass');
  const buttons = Array.from(compass.querySelectorAll('[data-compass-direction]'));
  const status = compass.querySelector('.terrain-compass-status');
  const directions = [
    { key: 'north', bearing: 0 },
    { key: 'east', bearing: 90 },
    { key: 'south', bearing: 180 },
  ];
  let currentBearing = 0;

  directions.forEach((direction, index) => {
    const card = terrainCards[index];
    direction.label = card.querySelector('strong')?.textContent.trim() || '';
    direction.terrain = card.querySelector(':scope > span:last-child')?.textContent.trim() || '';
    buttons[index].querySelector('[data-direction-label]').textContent = direction.label;
    buttons[index].querySelector('[data-direction-terrain]').textContent = direction.terrain;
    buttons[index].querySelector('.terrain-compass-letter').textContent = direction.label.charAt(0);
  });

  const river = terrainCards[3];
  if (river) {
    compass.querySelector('[data-river-label]').textContent = river.querySelector('strong')?.textContent.trim() || '';
    compass.querySelector('[data-river-name]').textContent = river.querySelector(':scope > span:last-child')?.textContent.trim() || '';
  } else {
    compass.querySelector('.terrain-compass-river').remove();
  }

  const selectDirection = (index) => {
    const direction = directions[index];
    const normalizedBearing = ((currentBearing % 360) + 360) % 360;
    const difference = ((direction.bearing - normalizedBearing + 540) % 360) - 180;
    currentBearing += difference;
    compass.style.setProperty('--compass-bearing', `${currentBearing}deg`);
    buttons.forEach((button, buttonIndex) => button.setAttribute('aria-pressed', String(buttonIndex === index)));
    compass.querySelectorAll('[data-compass-mark]').forEach((mark) => {
      mark.classList.toggle('is-active', mark.dataset.compassMark === direction.key);
    });
    status.textContent = `${direction.label} · ${String(direction.bearing).padStart(3, '0')}°`;
  };

  buttons.forEach((button, index) => {
    button.addEventListener('click', () => selectDirection(index));
    button.addEventListener('keydown', (event) => {
      let nextIndex;
      if (event.key === 'ArrowRight' || event.key === 'ArrowDown') nextIndex = (index + 1) % buttons.length;
      if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') nextIndex = (index - 1 + buttons.length) % buttons.length;
      if (event.key === 'Home') nextIndex = 0;
      if (event.key === 'End') nextIndex = buttons.length - 1;
      if (nextIndex === undefined) return;
      event.preventDefault();
      buttons[nextIndex].focus();
      selectDirection(nextIndex);
    });
  });

  selectDirection(0);
  history.appendChild(fragment);
  template.remove();
})();
