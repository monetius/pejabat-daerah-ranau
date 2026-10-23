<?php

namespace App\Support;

use Dom\Element;
use Dom\HTMLDocument;
use Dom\Node;
use Dom\Text;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use JsonException;

class ContentEditor
{
    private const LIST_STYLES = ['disc', 'circle', 'square', 'decimal', 'lower-alpha', 'upper-alpha', 'lower-roman', 'upper-roman'];

    private HTMLDocument $document;

    private Element $root;

    private ?Element $blockContainer = null;

    private ?Element $heroBlock = null;

    /** @var array<string, array{node: Node, attribute: ?string, type: string, label: string, group: string, value: string}> */
    private array $fields = [];

    /** @var list<array<string, string>> */
    private array $blocks = [];

    private string $group = 'Maklumat umum';

    public function __construct(private string $html, private bool $allowBlocks = true, private ?string $version = null)
    {
        $this->group = __('Maklumat umum');
        $this->version ??= hash('sha256', $html);
        $this->document = HTMLDocument::createFromString('<!doctype html><html><head><meta charset="UTF-8"></head><body><div id="cms-editor-root">'.$html.'</div></body></html>', LIBXML_NOERROR, 'UTF-8');
        $this->root = $this->document->getElementById('cms-editor-root');
        $this->walk($this->root);
        if ($this->heroBlock !== null) {
            array_unshift($this->blocks, $this->readBlock($this->heroBlock));
        }
    }

    /** @return array{version: string, fields: list<array<string, string>>, blocks: list<array<string, string>>, allowBlocks: bool} */
    public function schema(): array
    {
        $fields = [];
        foreach ($this->fields as $id => $field) {
            $fields[] = ['id' => $id, ...array_diff_key($field, ['node' => true, 'attribute' => true])];
        }

        return ['version' => $this->version, 'fields' => $fields, 'blocks' => $this->blocks, 'allowBlocks' => $this->allowBlocks];
    }

    public function apply(string $payload): string
    {
        try {
            $input = json_decode($payload, true, 64, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->invalid('Kandungan tidak dapat dibaca. Muat semula halaman dan cuba lagi.');
        }

        if (! is_array($input) || ! is_string($input['version'] ?? null) || ! hash_equals($this->version, $input['version'])) {
            $this->invalid('Kandungan telah berubah sejak halaman ini dibuka. Salin perubahan anda, kemudian muat semula halaman sebelum menyimpan.');
        }

        $data = Validator::make($input, [
            'fields' => ['present', 'array', 'max:10000'],
            'fields.*' => ['array:id,value'],
            'fields.*.id' => ['required', 'string', 'distinct'],
            'fields.*.value' => ['present', 'nullable', 'string', 'max:50000'],
            'blocks' => ['present', 'array', $this->allowBlocks ? 'max:100' : 'max:0'],
            'blocks.*' => ['array:type,text,level,url,alt,caption,variant,eyebrow,description,alignment,size,image_fit,button_text,button_url,list_style'],
            'blocks.*.type' => ['required', Rule::in(['hero', 'heading', 'paragraph', 'list', 'image', 'link', 'download'])],
            'blocks.*.text' => ['nullable', 'string', 'max:50000'],
            'blocks.*.list_style' => ['nullable', Rule::in(self::LIST_STYLES)],
            'blocks.*.level' => ['nullable', Rule::in(['h1', 'h2', 'h3'])],
            'blocks.*.url' => ['nullable', 'string', 'max:2000'],
            'blocks.*.alt' => ['nullable', 'string', 'max:500'],
            'blocks.*.caption' => ['nullable', 'string', 'max:1000'],
            'blocks.*.variant' => ['nullable', Rule::in(['plain', 'image'])],
            'blocks.*.eyebrow' => ['nullable', 'string', 'max:120'],
            'blocks.*.description' => ['nullable', 'string', 'max:2000'],
            'blocks.*.alignment' => ['nullable', Rule::in(['left', 'center'])],
            'blocks.*.size' => ['nullable', Rule::in(['compact', 'large'])],
            'blocks.*.image_fit' => ['nullable', Rule::in(['cover', 'contain'])],
            'blocks.*.button_text' => ['nullable', 'string', 'max:80'],
            'blocks.*.button_url' => ['nullable', 'string', 'max:2000'],
        ])->validate();

        if (count($data['fields']) !== count($this->fields)) {
            $this->invalid('Sebahagian kandungan tidak lengkap. Muat semula halaman dan cuba lagi.');
        }

        $changed = false;
        foreach ($data['fields'] as $submitted) {
            $field = $this->fields[$submitted['id']] ?? null;
            if ($field === null) {
                $this->invalid('Ruangan kandungan tidak dikenali. Muat semula halaman dan cuba lagi.');
            }
            $value = (string) ($submitted['value'] ?? '');
            if ($value === $field['value']) {
                continue;
            }
            $value = str_replace("\r\n", "\n", $value);
            if (in_array($field['type'], ['url', 'image'], true)) {
                $value = $this->safeUrl($value, $field['type'] === 'image');
            }
            if ($field['attribute'] !== null) {
                $field['node']->setAttribute($field['attribute'], $value);
            } else {
                $original = $field['node']->textContent;
                preg_match('/^(\s*)/u', $original, $leading);
                preg_match('/(\s*)$/u', $original, $trailing);
                $field['node']->textContent = $leading[1].$value.$trailing[1];
            }
            $changed = true;
        }

        $blocks = $this->normalizeBlocks($data['blocks']);
        if ($this->fields === [] && $blocks === [] && trim($this->html) === '') {
            $this->invalid('Tambah sekurang-kurangnya satu tajuk, perenggan atau gambar sebelum menyimpan.');
        }
        if ($blocks !== $this->blocks) {
            $changed = true;
            $hero = array_find($blocks, fn (array $block): bool => $block['type'] === 'hero');
            $contentBlocks = array_values(array_filter($blocks, fn (array $block): bool => $block['type'] !== 'hero'));
            if ($hero === null && $this->heroBlock !== null) {
                $this->heroBlock->remove();
                $this->heroBlock = null;
            } elseif ($hero !== null) {
                $wrapper = $this->document->createElement('div');
                $wrapper->innerHTML = $this->renderHero($hero);
                $newHero = $wrapper->firstElementChild;
                if ($this->heroBlock !== null) {
                    $this->root->replaceChild($newHero, $this->heroBlock);
                } else {
                    $this->root->insertBefore($newHero, $this->root->firstChild);
                }
                $this->heroBlock = $newHero;
            }
            if ($this->blockContainer === null && $contentBlocks !== []) {
                $this->blockContainer = $this->document->createElement('section');
                $this->blockContainer->setAttribute('class', 'cms-blocks');
                $this->blockContainer->setAttribute('data-cms-blocks', '');
                $this->root->appendChild($this->blockContainer);
            }
            if ($this->blockContainer !== null) {
                $this->blockContainer->innerHTML = $this->renderBlocks($contentBlocks);
            }
        }

        return $changed ? $this->root->innerHTML : $this->html;
    }

    private function walk(Node $node): void
    {
        if ($node instanceof Element) {
            $tag = strtolower($node->localName);
            if (in_array($tag, ['script', 'style', 'svg', 'math', 'template', 'noscript', 'textarea', 'select'], true)
                || $node->hasAttribute('hidden') || $node->getAttribute('aria-hidden') === 'true'
                || (! $this->allowBlocks && $node->getAttribute('id') === 'navLinks')) {
                return;
            }
            if ($node->hasAttribute('data-cms-blocks')) {
                $this->blockContainer = $node;
                foreach ($node->children as $block) {
                    $this->blocks[] = $this->readBlock($block);
                }

                return;
            }
            if ($this->allowBlocks && $node->getAttribute('data-cms-type') === 'hero') {
                $this->heroBlock = $node;

                return;
            }
            if (in_array($tag, ['h1', 'h2', 'h3'], true) && trim($node->textContent) !== '') {
                $this->group = mb_substr(trim($node->textContent), 0, 100);
            }
            if ($tag === 'img') {
                $this->addField($node, 'src', 'image', 'Gambar');
                $this->addField($node, 'alt', 'text', 'Penerangan gambar');
            } elseif ($tag === 'a' && $node->hasAttribute('href') && ! preg_match('/^\s*(javascript|data|vbscript):/i', (string) $node->getAttribute('href'))) {
                $this->addField($node, 'href', 'url', __('Destinasi pautan: :text', ['text' => mb_substr(trim($node->textContent), 0, 60)]));
            }
        } elseif ($node instanceof Text && trim($node->textContent) !== '') {
            if (preg_match('/^\[\[[^\]]+\]\]$/', trim($node->textContent))) {
                return;
            }
            $tag = strtolower($node->parentElement?->localName ?? '');
            $label = match (true) {
                in_array($tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true) => 'Tajuk / heading',
                $tag === 'p' => 'Perenggan',
                $tag === 'a' => 'Teks pautan',
                $tag === 'li' => 'Item senarai',
                in_array($tag, ['td', 'th'], true) => 'Teks jadual',
                $tag === 'button' => 'Teks butang',
                default => 'Teks',
            };
            $this->addField($node, null, $tag === 'p' ? 'paragraph' : 'text', $label);
        }
        foreach ($node->childNodes as $child) {
            $this->walk($child);
        }
    }

    private function addField(Node $node, ?string $attribute, string $type, string $label): void
    {
        $id = 'f'.count($this->fields);
        $this->fields[$id] = [
            'node' => $node, 'attribute' => $attribute, 'type' => $type,
            'label' => __($label), 'group' => $this->group,
            'value' => $attribute !== null ? (string) $node->getAttribute($attribute) : trim($node->textContent),
        ];
    }

    /** @return array<string, string> */
    private function readBlock(Element $block): array
    {
        $type = $block->getAttribute('data-cms-type');
        $result = ['type' => $type];
        if ($type === 'hero') {
            $description = $block->querySelector('.cms-hero-description');
            $button = $block->querySelector('.cms-hero-button');
            $result += [
                'variant' => $block->getAttribute('data-hero-variant') ?: 'plain',
                'text' => $block->querySelector('.cms-hero-title')?->textContent ?? '',
                'eyebrow' => $block->querySelector('.cms-hero-eyebrow')?->textContent ?? '',
                'description' => $description !== null ? $this->plainText($description) : '',
                'url' => $block->getAttribute('data-hero-image') ?? '',
                'alt' => $block->getAttribute('data-hero-alt') ?? '',
                'alignment' => $block->getAttribute('data-hero-alignment') ?: 'left',
                'size' => $block->getAttribute('data-hero-size') ?: 'large',
                'image_fit' => $block->getAttribute('data-hero-fit') ?: 'cover',
                'button_text' => $button?->textContent ?? '',
                'button_url' => $button?->getAttribute('href') ?? '',
            ];
        } elseif ($type === 'image') {
            $image = $block->querySelector('img');
            $result += ['url' => $image?->getAttribute('src') ?? '', 'alt' => $image?->getAttribute('alt') ?? '', 'caption' => $block->querySelector('figcaption')?->textContent ?? ''];
        } elseif (in_array($type, ['link', 'download'], true)) {
            $link = $block->querySelector('a');
            $result += ['text' => $link?->textContent ?? '', 'url' => $link?->getAttribute('href') ?? ''];
        } elseif ($type === 'list') {
            $style = $block->getAttribute('data-list-style');
            if (! in_array($style, self::LIST_STYLES, true)) {
                $style = $block->localName === 'ol' ? match ($block->getAttribute('type')) {
                    'a' => 'lower-alpha', 'A' => 'upper-alpha', 'i' => 'lower-roman', 'I' => 'upper-roman', default => 'decimal',
                } : 'disc';
            }
            $result['list_style'] = $style;
            $result['text'] = implode("\n", array_map(fn (Element $item): string => $item->textContent, iterator_to_array($block->children)));
        } else {
            $result['text'] = $this->plainText($block);
            if ($type === 'heading') {
                $result['level'] = $block->localName;
            }
        }

        return $result;
    }

    private function plainText(Node $node): string
    {
        $text = '';
        foreach ($node->childNodes as $child) {
            $text .= $child instanceof Element && $child->localName === 'br' ? "\n" : ($child instanceof Text ? $child->textContent : $this->plainText($child));
        }

        return $text;
    }

    /** @param list<array<string, mixed>> $blocks
     * @return list<array<string, string>>
     */
    private function normalizeBlocks(array $blocks): array
    {
        $result = [];
        $hero = null;
        foreach ($blocks as $index => $block) {
            $type = $block['type'];
            if ($type === 'hero') {
                if ($hero !== null) {
                    $this->invalid('Gunakan satu Hero sahaja bagi setiap halaman.');
                }
                $hero = $this->normalizeHero($block);

                continue;
            }
            $text = str_replace("\r\n", "\n", trim((string) ($block['text'] ?? '')));
            if ($type !== 'image' && $text === '') {
                $this->invalid(__('Sila isi teks bagi blok :number.', ['number' => $index + 1]));
            }
            $result[] = match ($type) {
                'heading' => ['type' => $type, 'text' => $text, 'level' => in_array($block['level'] ?? '', ['h1', 'h2', 'h3'], true) ? $block['level'] : 'h2'],
                'paragraph' => ['type' => $type, 'text' => $text],
                'list' => ['type' => $type, 'list_style' => $block['list_style'] ?? 'disc', 'text' => $text],
                'image' => ['type' => $type, 'url' => $this->safeUrl((string) ($block['url'] ?? ''), true), 'alt' => trim((string) ($block['alt'] ?? '')), 'caption' => trim((string) ($block['caption'] ?? ''))],
                default => ['type' => $type, 'text' => $text, 'url' => $this->safeUrl((string) ($block['url'] ?? ''))],
            };
        }
        if ($hero !== null) {
            array_unshift($result, $hero);
        }

        return $result;
    }

    /** @param array<string, mixed> $block
     * @return array<string, string>
     */
    private function normalizeHero(array $block): array
    {
        $hero = [
            'type' => 'hero',
            'variant' => in_array($block['variant'] ?? '', ['plain', 'image'], true) ? $block['variant'] : 'plain',
            'text' => trim((string) ($block['text'] ?? '')),
            'eyebrow' => trim((string) ($block['eyebrow'] ?? '')),
            'description' => str_replace("\r\n", "\n", trim((string) ($block['description'] ?? ''))),
            'url' => trim((string) ($block['url'] ?? '')),
            'alt' => trim((string) ($block['alt'] ?? '')),
            'alignment' => in_array($block['alignment'] ?? '', ['left', 'center'], true) ? $block['alignment'] : 'left',
            'size' => in_array($block['size'] ?? '', ['compact', 'large'], true) ? $block['size'] : 'large',
            'image_fit' => in_array($block['image_fit'] ?? '', ['cover', 'contain'], true) ? $block['image_fit'] : 'cover',
            'button_text' => trim((string) ($block['button_text'] ?? '')),
            'button_url' => trim((string) ($block['button_url'] ?? '')),
        ];
        if (mb_strlen($hero['text']) > 220) {
            $this->invalid('Tajuk Hero mestilah tidak melebihi 220 aksara.');
        }
        if ($hero['url'] !== '') {
            $hero['url'] = $this->safeUrl($hero['url'], true);
        }
        if ($hero['variant'] === 'image' && $hero['url'] === '') {
            $this->invalid('Pilih gambar untuk Hero atau gunakan gaya tanpa gambar.');
        }
        if (($hero['button_text'] === '') !== ($hero['button_url'] === '')) {
            $this->invalid('Isi teks dan alamat pautan butang Hero, atau kosongkan kedua-duanya.');
        }
        if ($hero['button_url'] !== '') {
            $hero['button_url'] = $this->safeUrl($hero['button_url']);
        }
        if ($hero['variant'] === 'plain' && ($hero['text'].$hero['eyebrow'].$hero['description'].$hero['button_text']) === '') {
            $this->invalid('Isi tajuk atau teks untuk Hero tanpa gambar.');
        }

        return $hero;
    }

    /** @param array<string, string> $hero */
    private function renderHero(array $hero): string
    {
        $hasText = ($hero['text'].$hero['eyebrow'].$hero['description'].$hero['button_text']) !== '';
        $classes = 'cms-hero cms-hero--'.$hero['variant'].' cms-hero--'.$hero['alignment'].' cms-hero--'.$hero['size'].' cms-hero--fit-'.$hero['image_fit'];
        if (! $hasText) {
            $classes .= ' cms-hero--image-only';
        }
        $html = '<section class="'.$classes.'" data-cms-type="hero" data-hero-variant="'.$hero['variant'].'" data-hero-alignment="'.$hero['alignment'].'" data-hero-size="'.$hero['size'].'" data-hero-fit="'.$hero['image_fit'].'" data-hero-image="'.e($hero['url']).'" data-hero-alt="'.e($hero['alt']).'">';
        if ($hero['variant'] === 'image') {
            $html .= '<img class="cms-hero-media" src="'.e($hero['url']).'" alt="'.e($hero['alt']).'" fetchpriority="high">';
        }
        if ($hasText) {
            $html .= '<div class="container cms-hero-inner">';
            if ($hero['eyebrow'] !== '') {
                $html .= '<p class="cms-hero-eyebrow">'.e($hero['eyebrow']).'</p>';
            }
            if ($hero['text'] !== '') {
                $html .= '<h1 class="cms-hero-title">'.e($hero['text']).'</h1>';
            }
            if ($hero['description'] !== '') {
                $html .= '<p class="cms-hero-description">'.str_replace("\n", '<br>', e($hero['description'])).'</p>';
            }
            if ($hero['button_text'] !== '') {
                $html .= '<a class="cms-hero-button" href="'.e($hero['button_url']).'">'.e($hero['button_text']).'</a>';
            }
            $html .= '</div>';
        }

        return $html.'</section>';
    }

    /** @param list<array<string, string>> $blocks */
    private function renderBlocks(array $blocks): string
    {
        $html = '';
        foreach ($blocks as $block) {
            $text = e($block['text'] ?? '');
            $html .= match ($block['type']) {
                'heading' => '<'.$block['level'].' data-cms-type="heading">'.$text.'</'.$block['level'].'>',
                'paragraph' => '<p data-cms-type="paragraph">'.str_replace("\n", '<br>', $text).'</p>',
                'list' => $this->renderList($block),
                'image' => '<figure data-cms-type="image"><img src="'.e($block['url']).'" alt="'.e($block['alt']).'" loading="lazy">'.($block['caption'] !== '' ? '<figcaption>'.e($block['caption']).'</figcaption>' : '').'</figure>',
                default => '<p data-cms-type="'.$block['type'].'"><a href="'.e($block['url']).'"'.($block['type'] === 'download' ? ' download' : '').'>'.$text.'</a></p>',
            };
        }

        return $html;
    }

    /** @param array<string, string> $block */
    private function renderList(array $block): string
    {
        $style = $block['list_style'];
        $tag = in_array($style, ['disc', 'circle', 'square'], true) ? 'ul' : 'ol';
        $type = match ($style) {
            'decimal' => '1', 'lower-alpha' => 'a', 'upper-alpha' => 'A', 'lower-roman' => 'i', 'upper-roman' => 'I', default => null,
        };
        $items = array_filter(explode("\n", $block['text']), fn (string $line): bool => trim($line) !== '');

        return '<'.$tag.' data-cms-type="list" data-list-style="'.$style.'"'.($type !== null ? ' type="'.$type.'"' : '')
            .' style="list-style-type:'.$style.';padding-inline-start:1.5rem">'
            .implode('', array_map(fn (string $line): string => '<li>'.e(trim($line)).'</li>', $items)).'</'.$tag.'>';
    }

    private function safeUrl(string $value, bool $image = false): string
    {
        $value = trim($value);
        $safe = ! preg_match('/[\x00-\x20\x7f\\\\]/', $value) && (
            (str_starts_with($value, '/') && ! str_starts_with($value, '//'))
            || (preg_match('/^https?:\/\//i', $value) && filter_var($value, FILTER_VALIDATE_URL))
            || (! $image && preg_match('/^(#|mailto:|tel:)/i', $value))
        );
        if (! $safe || ($image && $value === '')) {
            $this->invalid('Gunakan pautan yang sah (https:// atau /). Pautan e-mel, telefon dan bahagian halaman juga dibenarkan untuk teks.');
        }

        return $value;
    }

    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['content' => __($message)]);
    }
}
