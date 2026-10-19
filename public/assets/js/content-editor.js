(() => {
    'use strict';
    const translations = JSON.parse(document.querySelector('[data-editor-translations]')?.textContent || '{}');
    const t = (text, replacements = {}) => {
        let translated = translations[text] || text;
        Object.entries(replacements).forEach(([key, value]) => { translated = translated.replaceAll(`:${key}`, String(value)); });
        return translated;
    };
    const types = { hero: t('Hero'), heading: t('Heading / Tajuk'), paragraph: t('Perenggan'), list: t('Senarai'), image: t('Gambar'), link: t('Pautan'), download: t('Muat turun') };
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
            image.alt = t('Pratonton gambar');
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
            const button = element('button', 'btn gray editor-media-button', kind === 'image' ? t('Pilih / muat naik gambar') : t('Pilih / muat naik fail'));
            button.type = 'button';
            button.addEventListener('click', () => openPicker(input, kind));
            parent.append(button);
        };
        const selectControl = (parent, label, value, choices, onChange) => {
            const wrapper = element('div', 'editor-field');
            const select = element('select'); select.id = `content-field-${++nextId}`;
            const caption = element('label', '', label); caption.htmlFor = select.id;
            choices.forEach(([choice, text]) => {
                const option = element('option', '', text); option.value = choice; select.append(option);
            });
            select.value = value;
            select.addEventListener('change', () => { onChange(select.value); changed(); });
            wrapper.append(caption, select); parent.append(wrapper);
            return select;
        };
        const heroControls = (card, block) => {
            const preview = element('div', 'editor-hero-preview');
            preview.setAttribute('aria-hidden', 'true');
            const image = element('img'); image.alt = '';
            const content = element('div', 'editor-hero-preview-content');
            const eyebrow = element('span', 'editor-hero-preview-eyebrow');
            const title = element('h2');
            const description = element('p');
            const button = element('span', 'editor-hero-preview-button');
            content.append(eyebrow, title, description, button);
            preview.append(image, content); card.append(preview);
            const updatePreview = () => {
                const safeImage = block.variant === 'image' && /^(https?:\/\/|\/(?!\/))/.test(block.url || '') && !/[\x00-\x20\\]/.test(block.url);
                image.hidden = !safeImage;
                if (safeImage) image.src = block.url; else image.removeAttribute('src');
                image.style.objectFit = block.image_fit || 'cover';
                preview.dataset.alignment = block.alignment || 'left';
                preview.dataset.size = block.size || 'large';
                preview.classList.toggle('has-image', safeImage);
                const hasText = [block.text, block.eyebrow, block.description, block.button_text].some(value => value?.trim());
                preview.classList.toggle('is-image-only', block.variant === 'image' && !hasText);
                eyebrow.textContent = block.eyebrow || ''; eyebrow.hidden = !block.eyebrow;
                title.textContent = block.text || (block.variant === 'plain' && !hasText ? t('Tajuk hero anda') : ''); title.hidden = !title.textContent;
                description.textContent = block.description || ''; description.hidden = !block.description;
                button.textContent = block.button_text || ''; button.hidden = !block.button_text;
                imageFields.hidden = block.variant !== 'image';
                imageInput.required = block.variant === 'image';
                buttonText.required = !!block.button_url?.trim();
                buttonUrl.required = !!block.button_text?.trim();
            };
            image.addEventListener('error', () => { image.hidden = true; });
            selectControl(card, t('Gaya hero'), block.variant || 'plain', [['plain', t('Tanpa gambar — latar biasa')], ['image', t('Gambar banner')]], value => { block.variant = value; updatePreview(); });
            const imageFields = element('div', 'editor-hero-image-fields'); card.append(imageFields);
            const imageInput = control(imageFields, t('Gambar banner'), block.url, value => { block.url = value; updatePreview(); }, { placeholder: t('Pilih gambar daripada Media') });
            imageInput.inputMode = 'url';
            mediaButton(imageInput.parentElement, imageInput, 'image');
            control(imageFields, t('Penerangan gambar (pilihan)'), block.alt, value => { block.alt = value; }, { hint: t('Biarkan kosong jika gambar hanya sebagai hiasan.') });
            selectControl(imageFields, t('Paparan gambar'), block.image_fit || 'cover', [['cover', t('Isi banner (gambar mungkin dipotong)')], ['contain', t('Paparkan gambar penuh')]], value => { block.image_fit = value; updatePreview(); });
            const options = element('div', 'editor-hero-grid'); card.append(options);
            selectControl(options, t('Saiz hero'), block.size || 'large', [['large', t('Besar')], ['compact', t('Ringkas')]], value => { block.size = value; updatePreview(); });
            selectControl(options, t('Kedudukan teks'), block.alignment || 'left', [['left', t('Kiri')], ['center', t('Tengah')]], value => { block.alignment = value; updatePreview(); });
            control(card, t('Label atas (pilihan)'), block.eyebrow, value => { block.eyebrow = value; updatePreview(); }, { placeholder: t('Contoh: Profil Daerah') }).maxLength = 120;
            control(card, t('Tajuk hero (pilihan)'), block.text, value => { block.text = value; updatePreview(); }, { placeholder: t('Contoh: Selamat datang ke Ranau') }).maxLength = 220;
            control(card, t('Teks penerangan (pilihan)'), block.description, value => { block.description = value; updatePreview(); }, { multiline: true, hint: t('Isi teks untuk hero tanpa gambar. Untuk banner gambar sahaja, kosongkan ruangan teks.') }).maxLength = 2000;
            const buttonOptions = element('details', 'editor-hero-button-options');
            buttonOptions.open = !!(block.button_text || block.button_url);
            buttonOptions.append(element('summary', '', t('Butang (pilihan)'))); card.append(buttonOptions);
            const buttonText = control(buttonOptions, t('Teks butang'), block.button_text, value => { block.button_text = value; updatePreview(); }, { placeholder: t('Contoh: Ketahui lebih lanjut') });
            buttonText.maxLength = 80;
            const buttonUrl = control(buttonOptions, t('Pautan butang'), block.button_url, value => { block.button_url = value; updatePreview(); }, { placeholder: '/profil-daerah/pelan-pembangunan', hint: t('Isi kedua-dua ruangan untuk memaparkan butang.') });
            buttonUrl.inputMode = 'url';
            updatePreview();
        };

        const existing = editor.querySelector('[data-editor-existing]');
        if (schema.fields.length) {
            const title = element('h3', '', t('Kandungan sedia ada'));
            const search = element('input', 'editor-search');
            search.type = 'search';
            search.placeholder = t('Cari teks, tajuk atau gambar…');
            search.setAttribute('aria-label', t('Cari kandungan sedia ada'));
            existing.append(title, search);
            const noResults = element('p', 'editor-empty', t('Tiada kandungan sepadan. Cuba kata carian lain.'));
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
        const heroList = editor.querySelector('[data-editor-hero]');
        const renderBlocks = (focusIndex = null) => {
            if (!blockList) return;
            blockList.replaceChildren();
            heroList?.replaceChildren();
            editor.querySelector('[data-editor-empty]').hidden = state.blocks.some(block => block.type !== 'hero');
            const heroButton = editor.querySelector('[data-add-block="hero"]');
            if (heroButton) {
                heroButton.disabled = state.blocks.some(block => block.type === 'hero');
                heroButton.title = heroButton.disabled ? t('Satu Hero bagi setiap halaman. Edit blok Hero di bawah.') : t('Tambah Hero di bahagian atas halaman');
            }
            state.blocks.forEach((block, index) => {
                const card = element('section', 'editor-block');
                card.dataset.blockIndex = String(index);
                const header = element('div', 'editor-block-header');
                header.append(element('h4', '', `${index + 1}. ${types[block.type] || t('Kandungan')}`));
                const actions = element('div', 'editor-block-actions');
                [['↑', -1, t('Naikkan')], ['↓', 1, t('Turunkan')]].forEach(([symbol, direction, label]) => {
                    if (block.type === 'hero') return;
                    const button = element('button', '', symbol);
                    button.type = 'button';
                    button.setAttribute('aria-label', t(':action blok :number', { action: label, number: index + 1 }));
                    button.disabled = index + direction < 0 || index + direction >= state.blocks.length || state.blocks[index + direction]?.type === 'hero';
                    button.addEventListener('click', () => {
                        [state.blocks[index], state.blocks[index + direction]] = [state.blocks[index + direction], state.blocks[index]];
                        changed(); renderBlocks(index + direction); announce(t('Susunan blok dikemas kini.'));
                    });
                    actions.append(button);
                });
                const remove = element('button', 'editor-remove', t('Buang'));
                remove.type = 'button';
                remove.setAttribute('aria-label', t('Buang blok :number', { number: index + 1 }));
                remove.addEventListener('click', () => {
                    const removed = state.blocks.splice(index, 1)[0];
                    changed(); renderBlocks(Math.min(index, state.blocks.length - 1));
                    feedback.replaceChildren(element('span', '', t('Blok dibuang. ')));
                    const undo = element('button', 'btn gray', t('Pulihkan'));
                    undo.type = 'button';
                    undo.addEventListener('click', () => { state.blocks.splice(index, 0, removed); changed(); renderBlocks(index); announce(t('Blok dipulihkan.')); });
                    feedback.append(undo);
                });
                actions.append(remove); header.append(actions); card.append(header);
                if (block.type === 'heading') {
                    const wrap = element('div', 'editor-field');
                    const select = element('select'); select.id = `content-field-${++nextId}`;
                    const label = element('label', '', t('Saiz tajuk')); label.htmlFor = select.id;
                    [['h1', t('Tajuk utama')], ['h2', t('Tajuk bahagian')], ['h3', t('Subtajuk')]].forEach(([value, text]) => {
                        const option = element('option', '', text); option.value = value; select.append(option);
                    });
                    select.value = block.level || 'h2';
                    select.addEventListener('change', () => { block.level = select.value; changed(); });
                    wrap.append(label, select); card.append(wrap);
                }
                if (block.type === 'hero') heroControls(card, block);
                if (!['image', 'hero'].includes(block.type)) {
                    control(card, block.type === 'list' ? t('Item senarai') : (block.type === 'heading' ? t('Teks tajuk') : (block.type === 'paragraph' ? t('Teks perenggan') : t('Teks pautan'))), block.text, value => { block.text = value; }, {
                        multiline: ['paragraph', 'list'].includes(block.type), required: true,
                        hint: block.type === 'list' ? t('Tulis satu item pada setiap baris.') : undefined,
                    });
                }
                if (['image', 'link', 'download'].includes(block.type)) {
                    const input = control(card, block.type === 'image' ? t('Gambar') : t('Alamat pautan'), block.url, value => { block.url = value; }, { required: true, placeholder: t('https://… atau pilih daripada Media') });
                    input.inputMode = 'url';
                    mediaButton(input.parentElement, input, block.type === 'image' ? 'image' : 'file');
                    if (block.type === 'image') {
                        previewImage(input.parentElement, input);
                        control(card, t('Penerangan gambar'), block.alt, value => { block.alt = value; }, { hint: t('Penerangan ringkas untuk pembaca skrin.') });
                        control(card, t('Kapsyen (pilihan)'), block.caption, value => { block.caption = value; });
                    }
                }
                (block.type === 'hero' && heroList ? heroList : blockList).append(card);
            });
            if (focusIndex !== null && focusIndex >= 0) editor.querySelector(`[data-block-index="${focusIndex}"] input, [data-block-index="${focusIndex}"] textarea, [data-block-index="${focusIndex}"] select`)?.focus();
        };
        editor.querySelectorAll('[data-add-block]').forEach(button => {
            button.addEventListener('click', () => {
                const type = button.dataset.addBlock;
                if (state.blocks.length >= 100) { announce(t('Maksimum 100 blok bagi satu halaman.')); return; }
                if (type === 'hero') {
                    if (state.blocks.some(block => block.type === 'hero')) return;
                    state.blocks.unshift({ type, variant: 'plain', text: '', eyebrow: '', description: '', url: '', alt: '', alignment: 'left', size: 'large', image_fit: 'cover', button_text: '', button_url: '' });
                } else {
                    state.blocks.push(type === 'image' ? { type, url: '', alt: '', caption: '' } : (type === 'heading' ? { type, text: '', level: state.blocks.length || schema.fields.length ? 'h2' : 'h1' } : (['link', 'download'].includes(type) ? { type, text: '', url: '' } : { type, text: '' })));
                }
                changed(); renderBlocks(type === 'hero' ? 0 : state.blocks.length - 1); announce(t(':type ditambah.', { type: types[type] }));
            });
        });

        const dialog = element('dialog', 'editor-media-dialog');
        const dialogHeading = element('div', 'editor-dialog-heading');
        const dialogTitle = element('h2', '', t('Pilih daripada Media')); dialogTitle.id = `media-title-${++nextId}`;
        dialog.setAttribute('aria-labelledby', dialogTitle.id);
        const close = element('button', 'btn gray editor-dialog-close'); close.type = 'button';
        close.setAttribute('aria-label', t('Tutup'));
        close.title = t('Tutup');
        close.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M6 18 18 6"/></svg>';
        close.addEventListener('click', () => dialog.close());
        dialogHeading.append(dialogTitle, close);
        const searchMedia = element('input'); searchMedia.type = 'search'; searchMedia.placeholder = t('Cari nama fail…'); searchMedia.setAttribute('aria-label', t('Cari fail media'));
        const uploadLabel = element('label', 'editor-upload', t('Muat naik fail baharu'));
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
                    dialog.close(); announce(t('Fail dipilih. Simpan untuk menerbitkan perubahan.'));
                });
                mediaGrid.append(button);
            });
            mediaStatus.textContent = files.length ? (files.length === 1 ? t('1 fail. Pilih satu untuk digunakan.') : t(':count fail. Pilih satu untuk digunakan.', { count: files.length })) : t('Tiada fail sepadan. Muat naik fail baharu.');
        };
        const openPicker = async (input, kind, trigger = null) => {
            pickerTarget = { input, kind, trigger }; searchMedia.value = '';
            upload.accept = kind === 'image' ? 'image/jpeg,image/png,image/webp,image/gif' : 'image/jpeg,image/png,image/webp,image/gif,application/pdf';
            dialog.showModal(); mediaStatus.textContent = t('Memuatkan Media…');
            if (!mediaLoaded) {
                try {
                    const response = await fetch(editor.dataset.mediaUrl, { headers: { Accept: 'application/json' } });
                    if (!response.ok) throw new Error();
                    mediaFiles = (await response.json()).files; mediaLoaded = true;
                } catch { mediaStatus.textContent = t('Media tidak dapat dimuatkan. Sila cuba lagi atau log masuk semula.'); return; }
            }
            showMedia();
        };
        searchMedia.addEventListener('input', showMedia);
        upload.addEventListener('change', async () => {
            if (!upload.files[0]) return;
            if (upload.files[0].size > 10 * 1024 * 1024) { mediaStatus.textContent = t('Saiz maksimum fail ialah 10 MB.'); upload.value = ''; return; }
            upload.disabled = true; mediaStatus.textContent = t('Memuat naik…');
            try {
                const data = new FormData(); data.append('file', upload.files[0]);
                const response = await fetch(editor.dataset.mediaUrl, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value }, body: data });
                const result = await response.json();
                if (!response.ok) throw new Error(Object.values(result.errors || {}).flat().join(' ') || t('Muat naik gagal. Sila cuba lagi.'));
                mediaFiles.unshift(result.file); showMedia();
                mediaStatus.textContent = t('Fail dimuat naik. Pilih fail untuk digunakan.');
            } catch (error) { mediaStatus.textContent = error instanceof SyntaxError || error instanceof TypeError ? t('Muat naik gagal. Sila cuba lagi atau log masuk semula.') : (error.message || 'Muat naik gagal.'); }
            finally { upload.disabled = false; upload.value = ''; }
        });
        form.querySelectorAll('[data-media-select]').forEach(button => {
            const input = form.querySelector(button.dataset.mediaSelect);
            if (input) button.addEventListener('click', () => openPicker(input, 'image', button));
        });
        dialog.addEventListener('close', () => (pickerTarget?.trigger || pickerTarget?.input)?.focus());
        renderBlocks(); sync();
        form.addEventListener('input', event => { if (!event.target.closest('[data-content-editor]')) dirty = true; });
        form.addEventListener('submit', event => { sync(); submitting = event.submitter?.getAttribute('formtarget') !== '_blank'; });
        window.addEventListener('beforeunload', event => {
            if (dirty && !submitting) { event.preventDefault(); event.returnValue = ''; }
        });
    });
})();
