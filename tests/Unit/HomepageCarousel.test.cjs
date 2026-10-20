const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const { test } = require('node:test');

const file = path.resolve(__dirname, '../../public/assets/js/kinomulok.js');
const source = fs.readFileSync(file, 'utf8').split('function initPdCarousel() {')[1]
  .split('\nfunction initAffiliatedCarousel()')[0];

function fixture() {
  const element = () => ({
    handlers: {}, dataset: {},
    classList: { add() {}, remove() {}, contains() { return false; } },
    setAttribute(key, value) { this[key] = value; },
    addEventListener(key, handler) { this.handlers[key] = handler; },
    setPointerCapture() {}, focus() {},
    getAttribute() { return '/example'; },
    closest() { return null; },
  });
  const cards = Array.from({ length: 6 }, (_, index) => ({
    ...element(), dataset: { title: `Slide ${index}`, desc: `Description ${index}` },
  }));
  const parts = Object.fromEntries([
    '.pd-info', '.pd-title', '.pd-desc', '.pd-link', '.pd-credit', '.pd-count',
    '.pd-prev', '.pd-next', '.pd-stage',
  ].map((selector) => [selector, element()]));
  const root = { ...element(), querySelectorAll: () => cards, querySelector: (selector) => parts[selector] };
  vm.runInNewContext(`function initPdCarousel() {${source}\ninitPdCarousel();`, {
    document: { getElementById: () => root }, setTimeout: () => 1, clearTimeout() {}, Date,
  });
  const pointer = (x, y) => ({ isPrimary: true, pointerId: 1, pointerType: 'touch', clientX: x, clientY: y, target: cards[0] });
  const swipe = (dx, dy) => {
    parts['.pd-stage'].handlers.pointerdown(pointer(200, 200));
    parts['.pd-stage'].handlers.pointerup(pointer(200 + dx, 200 + dy));
  };
  return { parts, cards, root, swipe, pointer };
}

test('next, previous and wrap-around keep text and link synchronized', () => {
  const { parts, cards } = fixture();
  parts['.pd-prev'].handlers.click();
  assert.equal(parts['.pd-title'].textContent, 'Slide 5');
  assert.equal(parts['.pd-desc'].textContent, 'Description 5');
  assert.equal(parts['.pd-link'].href, '/example');
  parts['.pd-next'].handlers.click();
  assert.equal(cards[0].dataset.pos, '0');
  assert.equal(parts['.pd-count'].textContent, '01 / 06');
});

test('vertical and diagonal scrolling do not change slides', () => {
  const { parts, swipe } = fixture();
  swipe(60, 150);
  assert.equal(parts['.pd-count'].textContent, '01 / 06');
  swipe(60, 60);
  assert.equal(parts['.pd-count'].textContent, '01 / 06');
});

test('horizontal swipe advances without following the card link', () => {
  const { parts, swipe } = fixture();
  swipe(-90, 8);
  assert.equal(parts['.pd-count'].textContent, '02 / 06');
  let blocked = false;
  parts['.pd-stage'].handlers.click({ preventDefault() { blocked = true; }, stopPropagation() {} });
  assert.equal(blocked, true);
});

test('cancelled pointer gestures do not change a slide', () => {
  const { parts, pointer } = fixture();
  const stage = parts['.pd-stage'];
  stage.handlers.pointerdown(pointer(200, 200));
  stage.handlers.pointercancel();
  stage.handlers.pointerup(pointer(20, 200));
  assert.equal(parts['.pd-count'].textContent, '01 / 06');
});
