const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const { test } = require('node:test');
const source = fs.readFileSync(path.resolve(__dirname, '../../public/assets/js/image-editor.js'), 'utf8');
const exported = { exports: {} };
vm.runInNewContext(source, { module: exported });
const { cropBounds, outputSize } = exported.exports;

test('crop coordinates stay within the source even with invalid or overflowing input', () => {
  assert.deepEqual(JSON.parse(JSON.stringify(cropBounds({ x: -30, y: 390, width: 9999, height: 40 }, 600, 400))), { x: 0, y: 390, width: 600, height: 10 });
  const crop = cropBounds({ x: NaN, y: Infinity, width: -1, height: 0 }, 600, 400);
  assert.ok(crop.x >= 0 && crop.x + crop.width <= 600);
  assert.ok(crop.y >= 0 && crop.y + crop.height <= 400);
  assert.ok(crop.width >= 1 && crop.height >= 1);
});

test('large portrait and landscape images keep their ratio within the output limit', () => {
  assert.equal(outputSize(8000, 4000).width, 4096);
  assert.equal(outputSize(8000, 4000).height, 2048);
  assert.equal(outputSize(3000, 9000).width, 1365);
  assert.equal(outputSize(3000, 9000).height, 4096);
  assert.equal(outputSize(600, 400).width, 600);
});

function fixture({ failure = false } = {}) {
  const nodes = [], draws = [], uploads = [], revoked = [];
  class Element {
    constructor(tag) { this.tag = tag; this.children = []; this.handlers = {}; this.dataset = {}; this.value = ''; this.disabled = false; nodes.push(this); }
    append(...children) { this.children.push(...children); }
    setAttribute(name, value) { this[name] = value; }
    addEventListener(name, handler) { this.handlers[name] = handler; }
    dispatchEvent(event) { return this.handlers[event.type]?.(event); }
    showModal() { this.open = true; }
    close() { this.open = false; this.handlers.close?.(); }
    focus() {}
    querySelectorAll() { return nodes.filter(n => ['button', 'input', 'select'].includes(n.tag)); }
    getContext() { return { clearRect() {}, drawImage(...args) { draws.push(args); }, fillRect() {}, strokeRect() {} }; }
    toBlob(callback, type) { callback({ type, size: 100 }); }
    getBoundingClientRect() { return { left: 0, top: 0, width: 600, height: 400 }; }
    setPointerCapture() {}
  }
  const script = { dataset: { imageEditorUrl: '/admin/media' } };
  const document = {
    createElement: tag => new Element(tag), body: new Element('body'), activeElement: { focus() {} },
    querySelector: selector => selector === '[data-image-editor-url]' ? script : selector === '#image-editor-translations' ? { textContent: '{}' } : { content: 'csrf-test' },
    querySelectorAll: () => []
  };
  class TestImage { constructor() { this.naturalWidth = 600; this.naturalHeight = 400; } async decode() {} }
  class TestFormData { append(...args) { uploads.push(args); } }
  const window = {};
  const URLClass = class extends URL { static createObjectURL() { return 'blob:test'; } static revokeObjectURL(value) { revoked.push(value); } };
  vm.runInNewContext(source, { document, window, Image: TestImage, File, URL: URLClass, location: { origin: 'https://example.test' }, FormData: TestFormData, Event,
    fetch: async (url, options) => { uploads.push({ url, options }); return { ok: !failure, json: async () => failure ? { errors: { file: ['Invalid image'] } } : { file: { url: '/uploads/copy.png', image: true } } }; }
  });
  return { nodes, draws, uploads, revoked, open: window.CmsImageEditor.open,
    byId: id => nodes.find(n => n.id === id), button: label => nodes.find(n => n.tag === 'button' && n.textContent === label),
    dialog: nodes.find(n => n.tag === 'dialog'), canvas: nodes.find(n => n.tag === 'canvas') };
}

test('editing exports the selected pixels at the requested size and saves a new file', async () => {
  const ui = fixture(); let saved;
  await ui.open({ url: '/uploads/original.png', name: 'original.png', onSaved: file => { saved = file; } });
  const crop = ui.byId('image-edit-Lebarpotongan'); crop.value = '300'; crop.dispatchEvent(new Event('change'));
  const width = ui.byId('image-edit-Lebarhasilpx'); width.value = '150'; width.dispatchEvent(new Event('change'));
  assert.equal(ui.byId('image-edit-Tinggihasilpx').value, 200);
  await ui.button('Simpan salinan').handlers.click();
  const lastDraw = ui.draws.at(-1);
  assert.deepEqual(lastDraw.slice(1), [0, 0, 300, 400, 0, 0, 150, 200]);
  assert.equal(ui.uploads[0][2], 'original-edited.png');
  const request = ui.uploads.find(item => item.url);
  assert.equal(request.url, '/admin/media');
  assert.equal(request.options.headers['X-CSRF-TOKEN'], 'csrf-test');
  assert.equal(saved.url, '/uploads/copy.png');
  assert.equal(ui.dialog.open, false);
});

test('square ratio, reset, and pointer selection update the crop', async () => {
  const ui = fixture(); await ui.open({ url: '/uploads/original.png' });
  const ratio = ui.byId('image-edit-Nisbahpotongan'); ratio.value = '1'; ratio.dispatchEvent(new Event('change'));
  assert.equal(ui.byId('image-edit-Lebarpotongan').value, 400);
  assert.equal(ui.byId('image-edit-Tinggipotongan').value, 400);
  ui.button('Tetap semula').handlers.click();
  assert.equal(ui.byId('image-edit-Lebarpotongan').value, 600);
  ui.canvas.handlers.pointerdown({ isPrimary: true, button: 0, pointerId: 1, clientX: 100, clientY: 50 });
  ui.canvas.handlers.pointermove({ clientX: 400, clientY: 250 }); ui.canvas.handlers.pointerup();
  assert.equal(ui.byId('image-edit-KedudukanX').value, 100);
  assert.equal(ui.byId('image-edit-KedudukanY').value, 50);
  assert.equal(ui.byId('image-edit-Lebarpotongan').value, 300);
  assert.equal(ui.byId('image-edit-Tinggipotongan').value, 200);
});

test('external images are rejected and upload failure keeps the edit available for retry', async () => {
  const external = fixture(); await external.open({ url: 'https://external.test/image.png' });
  assert.equal(external.button('Simpan salinan').disabled, true);
  assert.equal(external.uploads.length, 0);
  const ui = fixture({ failure: true }); await ui.open({ url: '/uploads/original.png' });
  await ui.button('Simpan salinan').handlers.click();
  assert.equal(ui.dialog.open, true);
  assert.equal(ui.button('Simpan salinan').disabled, false);
  assert.ok(ui.nodes.some(n => n.textContent === 'Invalid image'));
});

test('local upload object URLs are released when the editor closes', async () => {
  const ui = fixture(); await ui.open({ file: new File(['png'], 'new.png', { type: 'image/png' }) });
  ui.dialog.close(); assert.deepEqual(ui.revoked, ['blob:test']);
});
