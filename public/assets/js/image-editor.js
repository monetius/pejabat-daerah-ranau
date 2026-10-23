(() => {
    'use strict';
    const clamp = (value, min, max) => Math.min(max, Math.max(min, Number(value) || min));
    const cropBounds = (crop, width, height) => {
        const x = Math.round(clamp(crop.x, 0, width - 1));
        const y = Math.round(clamp(crop.y, 0, height - 1));
        return { x, y, width: Math.round(clamp(crop.width, 1, width - x)), height: Math.round(clamp(crop.height, 1, height - y)) };
    };
    const outputSize = (width, height, limit = 4096) => {
        const scale = Math.min(1, limit / width, limit / height);
        return { width: Math.max(1, Math.round(width * scale)), height: Math.max(1, Math.round(height * scale)) };
    };
    if (typeof module !== 'undefined' && module.exports) { module.exports = { cropBounds, outputSize }; return; }
    const script = document.querySelector('[data-image-editor-url]');
    if (!script) return;
    const translations = JSON.parse(document.querySelector('#image-editor-translations').textContent);
    const t = key => translations[key] || key;
    const node = (tag, className = '', text = '') => {
        const item = document.createElement(tag); item.className = className; item.textContent = text; return item;
    };
    const button = (label, className = 'btn gray') => { const item = node('button', className, t(label)); item.type = 'button'; return item; };
    const dialog = node('dialog', 'image-editor-dialog');
    dialog.setAttribute('aria-labelledby', 'image-editor-title');
    const heading = node('div', 'image-editor-heading');
    const title = node('h2', '', t('Potong & ubah saiz')); title.id = 'image-editor-title';
    const close = button('', 'btn gray'); close.textContent = '\u00d7'; close.setAttribute('aria-label', t('Tutup'));
    heading.append(title, close);
    const note = node('p', 'hint', t('Salinan baharu disimpan. Gambar asal tidak berubah. GIF disimpan sebagai gambar pegun.'));
    const layout = node('div', 'image-editor-layout');
    const stage = node('div', 'image-editor-stage');
    const canvas = node('canvas'); canvas.width = 600; canvas.height = 400;
    canvas.setAttribute('role', 'img'); canvas.setAttribute('aria-label', t('Pratonton kawasan potongan'));
    const help = node('p', 'hint', t('Seret pada gambar untuk memilih kawasan. Anda juga boleh memasukkan ukuran di sebelah.'));
    stage.append(canvas, help);
    const controls = node('div', 'image-editor-controls');
    const field = (label, type, parent = controls) => {
        const wrap = node('div'); const caption = node('label', '', t(label)); const input = node(type === 'select' ? 'select' : 'input');
        input.id = `image-edit-${label.replace(/\W/g, '')}`; caption.htmlFor = input.id;
        if (type !== 'select') { input.type = type; input.min = '1'; input.step = '1'; }
        wrap.append(caption, input); parent.append(wrap); return input;
    };
    const ratio = field('Nisbah potongan', 'select');
    [['', 'Bebas'], ['original', 'Nisbah asal'], ['1', '1:1'], ['1.3333333333333333', '4:3'], ['1.7777777777777777', '16:9'], ['0.75', '3:4']].forEach(([value, label]) => { const option = node('option', '', t(label)); option.value = value; ratio.append(option); });
    const cropPair = node('div', 'image-editor-pair'); controls.append(cropPair);
    const cropWidth = field('Lebar potongan', 'number', cropPair); const cropHeight = field('Tinggi potongan', 'number', cropPair);
    const positionPair = node('div', 'image-editor-pair'); controls.append(positionPair);
    const xInput = field('Kedudukan X', 'number', positionPair); const yInput = field('Kedudukan Y', 'number', positionPair); xInput.min = yInput.min = '0';
    const sizePair = node('div', 'image-editor-pair'); controls.append(sizePair);
    const width = field('Lebar hasil (px)', 'number', sizePair); const height = field('Tinggi hasil (px)', 'number', sizePair); width.max = height.max = '4096';
    controls.append(node('p', 'hint', t('Nisbah hasil dikekalkan. Maksimum 4096 piksel setiap sisi.')));
    const format = field('Format gambar', 'select');
    [['image/png', 'PNG'], ['image/jpeg', 'JPG'], ['image/webp', 'WebP']].forEach(([value, label]) => { const option = node('option', '', label); option.value = value; format.append(option); });
    layout.append(stage, controls);
    const status = node('p', 'hint'); status.setAttribute('role', 'status'); status.setAttribute('aria-live', 'polite');
    const actions = node('div', 'image-editor-actions'); const reset = button('Tetap semula'); const save = button('Simpan salinan', 'btn'); actions.append(reset, save);
    dialog.append(heading, note, layout, status, actions); document.body.append(dialog);
    const context = canvas.getContext('2d');
    let image = null, crop = null, options = null, sourceUrl = null, trigger = null, drag = null, busy = false, generation = 0;
    const selectedRatio = () => ratio.value === 'original' ? image.naturalWidth / image.naturalHeight : Number(ratio.value);
    const fitRatio = value => {
        const aspect = selectedRatio();
        if (aspect) {
            const cropScale = Math.min(value.width / aspect, value.height);
            value = { ...value, width: cropScale * aspect, height: cropScale };
        }
        return cropBounds(value, image.naturalWidth, image.naturalHeight);
    };
    const draw = () => {
        context.clearRect(0, 0, canvas.width, canvas.height);
        context.drawImage(image, 0, 0, canvas.width, canvas.height);
        const sx = canvas.width / image.naturalWidth, sy = canvas.height / image.naturalHeight;
        context.fillStyle = 'rgba(0,0,0,.55)'; context.fillRect(0, 0, canvas.width, canvas.height);
        context.drawImage(image, crop.x, crop.y, crop.width, crop.height, crop.x * sx, crop.y * sy, crop.width * sx, crop.height * sy);
        context.strokeStyle = '#ffffff'; context.lineWidth = 2;
        context.strokeRect(crop.x * sx + 1, crop.y * sy + 1, Math.max(1, crop.width * sx - 2), Math.max(1, crop.height * sy - 2));
    };
    const updateCrop = () => {
        cropWidth.value = crop.width; cropHeight.value = crop.height; xInput.value = crop.x; yInput.value = crop.y;
        const size = outputSize(crop.width, crop.height); width.value = size.width; height.value = size.height; draw();
    };
    const setBusy = value => {
        busy = value;
        dialog.querySelectorAll('button,input,select').forEach(item => { item.disabled = value; });
    };
    const resetCrop = () => {
        if (!image || busy) return;
        ratio.value = ''; crop = { x: 0, y: 0, width: image.naturalWidth, height: image.naturalHeight }; updateCrop();
    };
    reset.addEventListener('click', resetCrop);
    close.addEventListener('click', () => { if (!busy) dialog.close(); });
    dialog.addEventListener('cancel', event => { if (busy) event.preventDefault(); });
    dialog.addEventListener('close', () => {
        generation++;
        if (sourceUrl) { URL.revokeObjectURL(sourceUrl); sourceUrl = null; }
        image = null; drag = null; trigger?.focus();
    });
    [cropWidth, cropHeight, xInput, yInput].forEach(input => input.addEventListener('change', () => {
        if (!image || busy) return;
        let value = { x: xInput.value, y: yInput.value, width: cropWidth.value, height: cropHeight.value };
        const aspect = selectedRatio();
        if (aspect && input === cropWidth) value.height = Number(value.width) / aspect;
        if (aspect && input === cropHeight) value.width = Number(value.height) * aspect;
        crop = fitRatio(cropBounds(value, image.naturalWidth, image.naturalHeight)); updateCrop();
    }));
    ratio.addEventListener('change', () => { if (image) { crop = fitRatio(crop); updateCrop(); } });
    width.addEventListener('change', () => { if (image) { const size = outputSize(clamp(width.value, 1, 4096), clamp(width.value, 1, 4096) * crop.height / crop.width); width.value = size.width; height.value = size.height; } });
    height.addEventListener('change', () => { if (image) { const size = outputSize(clamp(height.value, 1, 4096) * crop.width / crop.height, clamp(height.value, 1, 4096)); width.value = size.width; height.value = size.height; } });
    const point = event => {
        const bounds = canvas.getBoundingClientRect();
        return { x: clamp((event.clientX - bounds.left) / bounds.width * image.naturalWidth, 0, image.naturalWidth), y: clamp((event.clientY - bounds.top) / bounds.height * image.naturalHeight, 0, image.naturalHeight) };
    };
    canvas.addEventListener('pointerdown', event => {
        if (!image || busy || !event.isPrimary || event.button !== 0) return;
        drag = { ...point(event), before: { ...crop } }; canvas.setPointerCapture(event.pointerId);
    });
    canvas.addEventListener('pointermove', event => {
        if (!drag || !image || busy) return;
        const end = point(event); crop = fitRatio({ x: Math.min(drag.x, end.x), y: Math.min(drag.y, end.y), width: Math.abs(end.x - drag.x), height: Math.abs(end.y - drag.y) }); updateCrop();
    });
    canvas.addEventListener('pointerup', () => { if (drag && crop.width < 3 && crop.height < 3) { crop = drag.before; updateCrop(); } drag = null; });
    canvas.addEventListener('pointercancel', () => { if (drag) { crop = drag.before; updateCrop(); } drag = null; });
    const open = async value => {
        if (dialog.open) return;
        options = value; trigger = document.activeElement; image = null;
        context.clearRect(0, 0, canvas.width, canvas.height);
        dialog.showModal(); status.textContent = t('Memuatkan gambar...'); setBusy(true);
        const currentGeneration = ++generation;
        try {
            let url;
            if (value.file instanceof File) {
                if (!['image/jpeg', 'image/png', 'image/webp', 'image/gif'].includes(value.file.type) || value.file.size > 10 * 1024 * 1024) throw new Error(t('Pilih gambar JPG, PNG, WebP atau GIF di bawah 10 MB.'));
                sourceUrl = URL.createObjectURL(value.file); url = sourceUrl;
            } else {
                const target = new URL(value.url, location.origin);
                if (target.origin !== location.origin || !/^\/(uploads|assets\/img)\//.test(target.pathname)) throw new Error(t('Gambar tidak dapat dibuka.'));
                url = target.href;
            }
            const loaded = new Image(); loaded.src = url; await loaded.decode();
            if (currentGeneration !== generation) return;
            if (loaded.naturalWidth * loaded.naturalHeight > 40000000) throw new Error(t('Gambar terlalu besar. Pilih gambar di bawah 40 megapiksel.'));
            image = loaded; const preview = outputSize(image.naturalWidth, image.naturalHeight, 900); canvas.width = preview.width; canvas.height = preview.height;
            setBusy(false); format.value = 'image/png'; resetCrop(); status.textContent = `${image.naturalWidth} \u00d7 ${image.naturalHeight} px`; save.focus();
        } catch (error) {
            if (currentGeneration !== generation) return;
            setBusy(false); save.disabled = true; reset.disabled = true; status.textContent = error.message || t('Gambar tidak dapat dibuka.');
        }
    };
    save.addEventListener('click', async () => {
        if (!image || busy) return;
        width.dispatchEvent(new Event('change'));
        setBusy(true); status.textContent = t('Menyimpan gambar...');
        try {
            const output = document.createElement('canvas'); output.width = Number(width.value); output.height = Number(height.value);
            const drawing = output.getContext('2d');
            if (format.value === 'image/jpeg') { drawing.fillStyle = '#ffffff'; drawing.fillRect(0, 0, output.width, output.height); }
            drawing.imageSmoothingEnabled = true; drawing.imageSmoothingQuality = 'high';
            drawing.drawImage(image, crop.x, crop.y, crop.width, crop.height, 0, 0, output.width, output.height);
            const blob = await new Promise(resolve => output.toBlob(resolve, format.value, .9));
            if (!blob || blob.size > 10 * 1024 * 1024) throw new Error(t('Fail hasil terlalu besar. Kurangkan saiz atau pilih JPG / WebP.'));
            const extension = { 'image/png': 'png', 'image/jpeg': 'jpg', 'image/webp': 'webp' }[blob.type];
            const base = (options.name || options.file?.name || 'image').replace(/\.[^.]+$/, '');
            const data = new FormData(); data.append('file', blob, `${base}-edited.${extension}`);
            const response = await fetch(script.dataset.imageEditorUrl, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: data });
            const result = await response.json();
            if (!response.ok) throw new Error(result.errors?.file?.[0] || t('Gambar tidak dapat disimpan. Sila cuba lagi.'));
            const onSaved = options.onSaved; setBusy(false); dialog.close(); onSaved?.(result.file);
        } catch (error) { setBusy(false); status.textContent = error instanceof SyntaxError || error instanceof TypeError ? t('Gambar tidak dapat disimpan. Sila cuba lagi.') : error.message; }
    });
    window.CmsImageEditor = { open };
    document.querySelectorAll('[data-edit-image]').forEach(item => item.addEventListener('click', () => open({ url: item.dataset.editImage, onSaved: () => location.reload() })));
    document.querySelectorAll('[data-media-upload-form]').forEach(form => {
        const input = form.querySelector('input[type="file"]'); const edit = form.querySelector('[data-edit-upload]');
        input.addEventListener('change', () => { edit.disabled = !input.files[0]?.type.startsWith('image/'); });
        edit.addEventListener('click', () => { if (input.files[0]) open({ file: input.files[0], onSaved: () => location.reload() }); });
    });
})();
