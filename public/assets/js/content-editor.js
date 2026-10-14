(() => {
    'use strict';
    const types = { heading: 'Heading / Tajuk', paragraph: 'Perenggan', list: 'Senarai', image: 'Gambar', link: 'Pautan', download: 'Muat turun' };
    const element = (tag, className, text) => {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    };
    let nextId = 0;
    document.querySelectorAll('[data-content-editor]').forEach(editor => {
        const schema = JSON.parse(editor.querySelector('[data-editor-schema]').textContent);
        const hidden = editor.querySelector('[data-editor-value]');
        let state;
        try { state = JSON.parse(hidden.value); } catch { state = null; }
        if (!state || !Array.isArray(state.fields) || !Array.isArray(state.blocks)) {
            state = { version: schema.version, fields: schema.fields.map(({ id, value }) => ({ id, value })), blocks: schema.blocks };
        }
        const form = editor.closest('form');
        const feedback = editor.querySelector('[data-editor-feedback]');
        let dirty = false, submitting = false, pickerTarget = null, mediaFiles = [], mediaLoaded = false;
        const sync = () => { hidden.value = JSON.stringify(state); };
        const changed = () => { dirty = true; sync(); };
        const announce = text => { feedback.textContent = text; };
        const control = (parent, label, value, onInput, options = {}) => {
            const wrapper = element('div', 'editor-field');
            const input = element(options.multiline ? 'textarea' : 'input');
            input.id = `content-field-${++nextId}`;
            if (!options.multiline) input.type = 'text';
            input.value = value || '';
            input.required = !!options.required;
            if (options.placeholder) input.placeholder = options.placeholder;
            const caption = element('label', '', label);
            caption.htmlFor = input.id;
            wrapper.append(caption, input);
            if (options.hint) wrapper.append(element('p', 'hint', options.hint));
            input.addEventListener('input', () => { onInput(input.value); changed(); });
            parent.append(wrapper);
            return input;
        };
        const previewImage = (parent, input) => {
            const image = element('img', 'editor-image-preview');
            image.alt = 'Pratonton gambar';
            image.loading = 'lazy';
            const update = () => {
                const url = input.value.trim();
                const safe = /^(https?:\/\/|\/(?!\/))/.test(url) && !/[\x00-\x20\\]/.test(url);
                image.hidden = !safe;
                if (safe) image.src = url; else image.removeAttribute('src');
            };
            image.addEventListener('error', () => { image.hidden = true; });
            input.addEventListener('input', update);
            parent.append(image);
            update();
        };
        const mediaButton = (parent, input, kind) => {
            const button = element('button', 'btn gray editor-media-button', kind === 'image' ? 'Pilih / muat naik gambar' : 'Pilih / muat naik fail');
            button.type = 'button';
            button.addEventListener('click', () => openPicker(input, kind));
            parent.append(button);
        };

        const existing = editor.querySelector('[data-editor-existing]');
        if (schema.fields.length) {
            const title = element('h3', '', 'Kandungan sedia ada');
            const search = element('input', 'editor-search');
            search.type = 'search';
            search.placeholder = 'Cari teks, tajuk atau gambar…';
            search.setAttribute('aria-label', 'Cari kandungan sedia ada');
            existing.append(title, search);
            const noResults = element('p', 'editor-empty', 'Tiada kandungan sepadan. Cuba kata carian lain.');
            noResults.hidden = true;
            existing.append(noResults);
            const groups = new Map();
            schema.fields.forEach(field => {
                let group = groups.get(field.group);
                if (!group) {
                    const details = element('details', 'editor-group');
                    details.open = groups.size === 0;
                    details.append(element('summary', '', field.group));
                    const content = element('div', 'editor-group-content');
                    details.append(content);
                    existing.append(details);
                    group = { details, content, fields: [] };
                    groups.set(field.group, group);
                }
                const current = state.fields.find(item => item.id === field.id);
                const input = control(group.content, field.label, current?.value ?? field.value, value => {
                    if (current) current.value = value;
                }, { multiline: field.type === 'paragraph' || field.value.length > 140 });
                const wrapper = input.parentElement;
                if (field.type === 'image') {
                    mediaButton(wrapper, input, 'image');
                    previewImage(wrapper, input);
                } else if (field.type === 'url') {
                    input.inputMode = 'url';
                    mediaButton(wrapper, input, 'file');
                }
                group.fields.push({ wrapper, input, label: field.label });
            });
            search.addEventListener('input', () => {
                const query = search.value.toLocaleLowerCase().trim();
                groups.forEach(({ details, fields }) => {
                    fields.forEach(({ wrapper, input, label }) => {
                        wrapper.hidden = !!query && !`${label} ${input.value}`.toLocaleLowerCase().includes(query);
                    });
                    details.hidden = fields.every(({ wrapper }) => wrapper.hidden);
                    if (query && !details.hidden) details.open = true;
                });
                noResults.hidden = [...groups.values()].some(({details}) => !details.hidden);
            });
        }

        const blockList = editor.querySelector('[data-editor-blocks]');
        const renderBlocks = (focusIndex = null) => {
            if (!blockList) return;
            blockList.replaceChildren();
            editor.querySelector('[data-editor-empty]').hidden = state.blocks.length > 0;
            state.blocks.forEach((block, index) => {
                const card = element('section', 'editor-block');
                const header = element('div', 'editor-block-header');
                header.append(element('h4', '', `${index + 1}. ${types[block.type] || 'Kandungan'}`));
                const actions = element('div', 'editor-block-actions');
                [['↑', -1, 'Naikkan'], ['↓', 1, 'Turunkan']].forEach(([symbol, direction, label]) => {
                    const button = element('button', '', symbol);
                    button.type = 'button';
                    button.setAttribute('aria-label', `${label} blok ${index + 1}`);
                    button.disabled = index + direction < 0 || index + direction >= state.blocks.length;
                    button.addEventListener('click', () => {
                        [state.blocks[index], state.blocks[index + direction]] = [state.blocks[index + direction], state.blocks[index]];
                        changed(); renderBlocks(index + direction); announce('Susunan blok dikemas kini.');
                    });
                    actions.append(button);
                });
                const remove = element('button', 'editor-remove', 'Buang');
                remove.type = 'button';
                remove.setAttribute('aria-label', `Buang blok ${index + 1}`);
                remove.addEventListener('click', () => {
                    const removed = state.blocks.splice(index, 1)[0];
                    changed(); renderBlocks(Math.min(index, state.blocks.length - 1));
                    feedback.replaceChildren(element('span', '', 'Blok dibuang. '));
                    const undo = element('button', 'btn gray', 'Pulihkan');
                    undo.type = 'button';
                    undo.addEventListener('click', () => { state.blocks.splice(index, 0, removed); changed(); renderBlocks(index); announce('Blok dipulihkan.'); });
                    feedback.append(undo);
                });
                actions.append(remove); header.append(actions); card.append(header);
                if (block.type === 'heading') {
                    const wrap = element('div', 'editor-field');
                    const select = element('select'); select.id = `content-field-${++nextId}`;
                    const label = element('label', '', 'Saiz tajuk'); label.htmlFor = select.id;
                    [['h1', 'Tajuk utama'], ['h2', 'Tajuk bahagian'], ['h3', 'Subtajuk']].forEach(([value, text]) => {
                        const option = element('option', '', text); option.value = value; select.append(option);
                    });
                    select.value = block.level || 'h2';
                    select.addEventListener('change', () => { block.level = select.value; changed(); });
                    wrap.append(label, select); card.append(wrap);
                }
                if (block.type !== 'image') {
                    control(card, block.type === 'list' ? 'Item senarai' : (block.type === 'heading' ? 'Teks tajuk' : (block.type === 'paragraph' ? 'Teks perenggan' : 'Teks pautan')), block.text, value => { block.text = value; }, {
                        multiline: ['paragraph', 'list'].includes(block.type), required: true,
                        hint: block.type === 'list' ? 'Tulis satu item pada setiap baris.' : undefined,
                    });
                }
                if (['image', 'link', 'download'].includes(block.type)) {
                    const input = control(card, block.type === 'image' ? 'Gambar' : 'Alamat pautan', block.url, value => { block.url = value; }, { required: true, placeholder: 'https://… atau pilih daripada Media' });
                    input.inputMode = 'url';
                    mediaButton(input.parentElement, input, block.type === 'image' ? 'image' : 'file');
                    if (block.type === 'image') {
                        previewImage(input.parentElement, input);
                        control(card, 'Penerangan gambar', block.alt, value => { block.alt = value; }, { hint: 'Penerangan ringkas untuk pembaca skrin.' });
                        control(card, 'Kapsyen (pilihan)', block.caption, value => { block.caption = value; });
                    }
                }
                blockList.append(card);
            });
            if (focusIndex !== null && focusIndex >= 0) blockList.children[focusIndex]?.querySelector('input, textarea, select')?.focus();
        };
        editor.querySelectorAll('[data-add-block]').forEach(button => {
            button.addEventListener('click', () => {
                const type = button.dataset.addBlock;
                if (state.blocks.length >= 100) { announce('Maksimum 100 blok bagi satu halaman.'); return; }
                state.blocks.push(type === 'image' ? { type, url: '', alt: '', caption: '' } : (type === 'heading' ? { type, text: '', level: state.blocks.length || schema.fields.length ? 'h2' : 'h1' } : (['link', 'download'].includes(type) ? { type, text: '', url: '' } : { type, text: '' })));
                changed(); renderBlocks(state.blocks.length - 1); announce(`${types[type]} ditambah.`);
            });
        });

        const dialog = element('dialog', 'editor-media-dialog');
        const dialogHeading = element('div', 'editor-dialog-heading');
        const dialogTitle = element('h2', '', 'Pilih daripada Media'); dialogTitle.id = `media-title-${++nextId}`;
        dialog.setAttribute('aria-labelledby', dialogTitle.id);
        const close = element('button', 'btn gray editor-dialog-close'); close.type = 'button';
        close.setAttribute('aria-label', 'Tutup');
        close.title = 'Tutup';
        close.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M6 18 18 6"/></svg>';
        close.addEventListener('click', () => dialog.close());
        dialogHeading.append(dialogTitle, close);
        const searchMedia = element('input'); searchMedia.type = 'search'; searchMedia.placeholder = 'Cari nama fail…'; searchMedia.setAttribute('aria-label', 'Cari fail media');
        const uploadLabel = element('label', 'editor-upload', 'Muat naik fail baharu');
        const upload = element('input'); upload.type = 'file'; uploadLabel.append(upload);
        const mediaStatus = element('p', 'hint'); mediaStatus.setAttribute('role', 'status');
        const mediaGrid = element('div', 'editor-media-grid');
        dialog.append(dialogHeading, searchMedia, uploadLabel, mediaStatus, mediaGrid);
        document.body.append(dialog);
        const showMedia = () => {
            mediaGrid.replaceChildren();
            const query = searchMedia.value.toLocaleLowerCase();
            const files = mediaFiles.filter(file => (pickerTarget.kind !== 'image' || file.image) && file.name.toLocaleLowerCase().includes(query));
            files.forEach(file => {
                const button = element('button', 'editor-media-item'); button.type = 'button';
                if (file.image) { const img = element('img'); img.src = file.url; img.alt = ''; img.loading = 'lazy'; button.append(img); }
                else button.append(element('span', 'editor-file-icon', 'PDF'));
                button.append(element('span', '', file.name));
                button.addEventListener('click', () => {
                    pickerTarget.input.value = file.url;
                    pickerTarget.input.dispatchEvent(new Event('input', { bubbles: true }));
                    dialog.close(); announce('Fail dipilih. Simpan untuk menerbitkan perubahan.');
                });
                mediaGrid.append(button);
            });
            mediaStatus.textContent = files.length ? `${files.length} fail. Pilih satu untuk digunakan.` : 'Tiada fail sepadan. Muat naik fail baharu.';
        };
        const openPicker = async (input, kind) => {
            pickerTarget = { input, kind }; searchMedia.value = '';
            upload.accept = kind === 'image' ? 'image/jpeg,image/png,image/webp,image/gif' : 'image/jpeg,image/png,image/webp,image/gif,application/pdf';
            dialog.showModal(); mediaStatus.textContent = 'Memuatkan Media…';
            if (!mediaLoaded) {
                try {
                    const response = await fetch(editor.dataset.mediaUrl, { headers: { Accept: 'application/json' } });
                    if (!response.ok) throw new Error();
                    mediaFiles = (await response.json()).files; mediaLoaded = true;
                } catch { mediaStatus.textContent = 'Media tidak dapat dimuatkan. Sila cuba lagi atau log masuk semula.'; return; }
            }
            showMedia();
        };
        searchMedia.addEventListener('input', showMedia);
        upload.addEventListener('change', async () => {
            if (!upload.files[0]) return;
            if (upload.files[0].size > 10 * 1024 * 1024) { mediaStatus.textContent = 'Saiz maksimum fail ialah 10 MB.'; upload.value = ''; return; }
            upload.disabled = true; mediaStatus.textContent = 'Memuat naik…';
            try {
                const data = new FormData(); data.append('file', upload.files[0]);
                const response = await fetch(editor.dataset.mediaUrl, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value }, body: data });
                const result = await response.json();
                if (!response.ok) throw new Error(Object.values(result.errors || {}).flat().join(' ') || 'Muat naik gagal. Sila cuba lagi.');
                mediaFiles.unshift(result.file); showMedia();
                mediaStatus.textContent = 'Fail dimuat naik. Pilih fail untuk digunakan.';
            } catch (error) { mediaStatus.textContent = error instanceof SyntaxError || error instanceof TypeError ? 'Muat naik gagal. Sila cuba lagi atau log masuk semula.' : (error.message || 'Muat naik gagal.'); }
            finally { upload.disabled = false; upload.value = ''; }
        });
        dialog.addEventListener('close', () => pickerTarget?.input.focus());
        renderBlocks(); sync();
        form.addEventListener('input', event => { if (!event.target.closest('[data-content-editor]')) dirty = true; });
        form.addEventListener('submit', event => { sync(); submitting = event.submitter?.getAttribute('formtarget') !== '_blank'; });
        window.addEventListener('beforeunload', event => {
            if (dirty && !submitting) { event.preventDefault(); event.returnValue = ''; }
        });
    });
})();
