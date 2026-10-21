const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const { test } = require('node:test');

const source = fs.readFileSync(path.resolve(__dirname, '../../public/assets/js/kinomulok.js'), 'utf8')
  .split('function initAccessibility() {')[1].split('\nfunction initDriftMotion(')[0];

function fixture(saved = {}, storageBlocked = false) {
  const classes = new Set();
  const controls = {};
  const optionNames = ['contrast', 'links', 'font', 'motion', 'spacing', 'focus'];
  let focused;
  let persisted;
  const element = (name, dataset = {}) => ({
    dataset, handlers: {}, attributes: {}, hidden: true,
    addEventListener(event, handler) { this.handlers[event] = handler; },
    setAttribute(key, value) { this.attributes[key] = value; },
    focus() { focused = name; },
  });
  for (const selector of ['.a11y-btn', '.a11y-panel', '.a11y-size-val', '.a11y-close', '.a11y-reset']) {
    controls[selector] = element(selector);
  }
  for (const key of optionNames) controls[`[data-opt="${key}"]`] = element(key, { opt: key });
  for (const size of ['-1', '1']) controls[`[data-size="${size}"]`] = element(size, { size });
  const root = {
    style: {}, classList: { toggle(name, enabled) { if (enabled) classes.add(name); else classes.delete(name); } },
  };
  const handlers = {};
  const wrap = {
    setAttribute() {}, contains: () => false,
    querySelector: (selector) => controls[selector],
    querySelectorAll: (selector) => Object.values(controls).filter((control) => selector === '[data-opt]' ? control.dataset.opt : control.dataset.size),
  };
  vm.runInNewContext(`function initAccessibility() {${source}\ninitAccessibility();`, {
    Event,
    document: {
      documentElement: root, createElement: () => wrap, body: { appendChild() {} },
      addEventListener(event, handler) { handlers[event] = handler; }, dispatchEvent() {},
    },
    localStorage: {
      getItem() { if (storageBlocked) throw new Error('Blocked'); return JSON.stringify(saved); },
      setItem(key, value) { if (storageBlocked) throw new Error('Blocked'); persisted = JSON.parse(value); },
    },
  });

  return { root, classes, controls, handlers, saved: () => persisted, focused: () => focused };
}

test('each display option changes its class, pressed state and saved preference', () => {
  const { controls, classes, saved } = fixture();
  for (const [key, className] of Object.entries({ contrast: 'a11y-contrast', links: 'a11y-links', font: 'a11y-font', motion: 'a11y-no-motion', spacing: 'a11y-spacing', focus: 'a11y-focus' })) {
    controls[`[data-opt="${key}"]`].handlers.click();
    assert.equal(classes.has(className), true);
    assert.equal(controls[`[data-opt="${key}"]`].attributes['aria-pressed'], 'true');
    assert.equal(saved()[key], true);
  }
  assert.equal(fixture(saved()).classes.size, 6);
});

test('legacy text sizes are preserved and malformed display preferences are ignored', () => {
  assert.equal(fixture({ size: 2 }).root.style.fontSize, '115%');
  const current = fixture({ sizePercent: 25, contrast: 'false', links: {}, focus: true });
  assert.equal(current.root.style.fontSize, '25%');
  assert.deepEqual([...current.classes], ['a11y-focus']);
});

test('text sizes stay within 25 to 200 percent and reset clears all settings', () => {
  const { controls, root, classes, saved } = fixture({ sizePercent: 25, contrast: true, spacing: true });
  assert.equal(controls['[data-size="-1"]'].disabled, true);
  for (let index = 0; index < 20; index++) controls['[data-size="1"]'].handlers.click();
  assert.equal(root.style.fontSize, '200%');
  assert.equal(controls['[data-size="1"]'].disabled, true);
  controls['.a11y-reset'].handlers.click();
  assert.equal(root.style.fontSize, '');
  assert.equal(classes.size, 0);
  assert.equal(saved().sizePercent, 100);
});

test('opening focuses the close control and Escape restores focus to the launcher', () => {
  const { controls, handlers, focused } = fixture();
  controls['.a11y-btn'].handlers.click({ stopPropagation() {} });
  assert.equal(controls['.a11y-panel'].hidden, false);
  assert.equal(focused(), '.a11y-close');
  handlers.keydown({ key: 'Escape' });
  assert.equal(controls['.a11y-panel'].hidden, true);
  assert.equal(focused(), '.a11y-btn');
});

test('controls still work when browser storage is unavailable', () => {
  const { controls, classes } = fixture({}, true);
  controls['[data-opt="contrast"]'].handlers.click();
  assert.equal(classes.has('a11y-contrast'), true);
});
