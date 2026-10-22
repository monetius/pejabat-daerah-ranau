const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const { test } = require('node:test');
const source = fs.readFileSync(path.resolve(__dirname, '../../public/assets/js/admin-verification.js'), 'utf8');
function fixture() {
  let focused = -1;
  const digits = Array.from({ length: 6 }, (_, index) => ({ value: '', handlers: {}, addEventListener(key, handler) { this.handlers[key] = handler; }, focus() { focused = index; }, select() {} }));
  const nodes = { '#verification-code': { value: '' }, '#code-expiry': {}, '[data-resend-code]': {}, '[data-verify-code]': {} };
  const panel = { dataset: { expiresAt: 1180, serverNow: 1000, resendAt: 1060, expiryLabel: 'Expires :time', expiredLabel: 'Expired', resendLabel: 'Request again', resendWaitLabel: 'Request again :time' }, querySelectorAll: () => digits, querySelector: (selector) => nodes[selector] };
  vm.runInNewContext(source, { document: { querySelector: () => panel, addEventListener() {} }, performance: { now: () => 0 }, setInterval() {}, clearInterval() {} });
  return { digits, nodes, focused: () => focused };
}
test('pasting a complete code fills six boxes and preserves a leading zero', () => {
  const { digits, nodes, focused } = fixture();
  digits[0].handlers.paste({ preventDefault() {}, clipboardData: { getData: () => '012 345' } });
  assert.equal(digits.map((digit) => digit.value).join(''), '012345');
  assert.equal(nodes['#verification-code'].value, '012345');
  assert.equal(focused(), 5);
});
test('typing advances and rejects nonnumeric input', () => {
  const { digits, nodes, focused } = fixture();
  digits[0].value = '4'; digits[0].handlers.input();
  assert.equal(focused(), 1);
  assert.equal(nodes['#verification-code'].value, '4');
  digits[1].value = 'x'; digits[1].handlers.input();
  assert.equal(digits[1].value, '');
  assert.equal(nodes['#verification-code'].value, '4');
});
test('Backspace in an empty box clears the previous digit and updates the submitted code', () => {
  const { digits, nodes, focused } = fixture();
  digits[0].value = '4'; digits[0].handlers.input();
  digits[1].handlers.keydown({ key: 'Backspace', preventDefault() {} });
  assert.equal(digits[0].value, ''); assert.equal(nodes['#verification-code'].value, ''); assert.equal(focused(), 0);
});
test('arrow keys navigate without changing the code', () => {
  const { digits, focused } = fixture();
  digits[2].handlers.keydown({ key: 'ArrowLeft', preventDefault() {} }); assert.equal(focused(), 1);
  digits[2].handlers.keydown({ key: 'ArrowRight', preventDefault() {} }); assert.equal(focused(), 3);
});

test('one-time-code autofill distributes a complete value from the first box', () => {
  const { digits, nodes } = fixture();
  digits[0].value = '654321'; digits[0].handlers.input();
  assert.equal(nodes['#verification-code'].value, '654321');
  assert.deepEqual(digits.map((digit) => digit.value), ['6', '5', '4', '3', '2', '1']);
});
