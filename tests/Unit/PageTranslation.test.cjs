const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const { test } = require('node:test');

const file = path.resolve(__dirname, '../../public/assets/js/kinomulok.js');
const source = fs.readFileSync(file, 'utf8').split('function loadGoogleTranslate() {')[1]
  .split('\nfunction setSiteLanguage(')[0];

function load(cookie, twice = false) {
  const nodes = [];
  const context = {
    window: {},
    document: {
      cookie,
      getElementById: (id) => nodes.find((node) => node.id === id),
      createElement: (tag) => ({ tag, style: {} }),
      body: { appendChild: (node) => nodes.push(node) },
    },
  };
  vm.runInNewContext(`function loadGoogleTranslate() {${source}\nloadGoogleTranslate();${twice ? 'loadGoogleTranslate();' : ''}`, context);

  return nodes.filter((node) => node.tag === 'script');
}

test('untranslated visits do not load the third-party translation script', () => {
  for (const cookie of ['', 'theme=dark', 'googtrans=/ms/ms', 'googtrans=/ms/unknown', 'othergoogtrans=/ms/en']) {
    assert.equal(load(cookie).length, 0, cookie);
  }
});

test('all supported translated languages still load the existing translator', () => {
  for (const locale of ['en', 'zh-CN', 'ta', 'ko']) {
    const scripts = load(`theme=dark; googtrans=/ms/${locale}; preference=value`);
    assert.equal(scripts.length, 1, locale);
    assert.equal(scripts[0].src, 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit');
  }
});

test('repeated initialization does not load duplicate scripts', () => {
  assert.equal(load('googtrans=/ms/en', true).length, 1);
});
