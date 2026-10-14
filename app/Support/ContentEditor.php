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
    private HTMLDocument $document;

    private Element $root;

    private ?Element $blockContainer = null;

    /** @var array<string, array{node: Node, attribute: ?string, type: string, label: string, group: string, value: string}> */
    private array $fields = [];

    /** @var list<array<string, string>> */
    private array $blocks = [];

    private string $group = 'Maklumat umum';

    public function __construct(private string $html, private bool $allowBlocks = true, private ?string $version = null)
    {
        $this->version ??= hash('sha256', $html);
        $this->document = HTMLDocument::createFromString('<!doctype html><html><head><meta charset="UTF-8"></head><body><div id="cms-editor-root">'.$html.'</div></body></html>', LIBXML_NOERROR, 'UTF-8');
        $this->root = $this->document->getElementById('cms-editor-root');
        $this->walk($this->root);
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
            'blocks.*' => ['array:type,text,level,url,alt,caption'],
            'blocks.*.type' => ['required', Rule::in(['heading', 'paragraph', 'list', 'image', 'link', 'download'])],
            'blocks.*.text' => ['nullable', 'string', 'max:50000'],
            'blocks.*.level' => ['nullable', Rule::in(['h1', 'h2', 'h3'])],
            'blocks.*.url' => ['nullable', 'string', 'max:2000'],
            'blocks.*.alt' => ['nullable', 'string', 'max:500'],
            'blocks.*.caption' => ['nullable', 'string', 'max:1000'],
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
            if ($this->blockContainer === null && $blocks !== []) {
                $this->blockContainer = $this->document->createElement('section');
                $this->blockContainer->setAttribute('class', 'cms-blocks');
                $this->blockContainer->setAttribute('data-cms-blocks', '');
                $this->root->appendChild($this->blockContainer);
            }
            if ($this->blockContainer !== null) {
                $this->blockContainer->innerHTML = $this->renderBlocks($blocks);
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
            if (in_array($tag, ['h1', 'h2', 'h3'], true) && trim($node->textContent) !== '') {
                $this->group = mb_substr(trim($node->textContent), 0, 100);
            }
            if ($tag === 'img') {
                $this->addField($node, 'src', 'image', 'Gambar');
                $this->addField($node, 'alt', 'text', 'Penerangan gambar');
            } elseif ($tag === 'a' && $node->hasAttribute('href') && ! preg_match('/^\s*(javascript|data|vbscript):/i', (string) $node->getAttribute('href'))) {
                $this->addField($node, 'href', 'url', 'Destinasi pautan: '.mb_substr(trim($node->textContent), 0, 60));
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
            'label' => $label, 'group' => $this->group,
            'value' => $attribute !== null ? (string) $node->getAttribute($attribute) : trim($node->textContent),
        ];
    }

    /** @return array<string, string> */
    private function readBlock(Element $block): array
    {
        $type = $block->getAttribute('data-cms-type');
        $result = ['type' => $type];
        if ($type === 'image') {
            $image = $block->querySelector('img');
            $result += ['url' => $image?->getAttribute('src') ?? '', 'alt' => $image?->getAttribute('alt') ?? '', 'caption' => $block->querySelector('figcaption')?->textContent ?? ''];
        } elseif (in_array($type, ['link', 'download'], true)) {
            $link = $block->querySelector('a');
            $result += ['text' => $link?->textContent ?? '', 'url' => $link?->getAttribute('href') ?? ''];
        } elseif ($type === 'list') {
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
        foreach ($blocks as $index => $block) {
            $type = $block['type'];
            $text = str_replace("\r\n", "\n", trim((string) ($block['text'] ?? '')));
            if ($type !== 'image' && $text === '') {
                $this->invalid('Sila isi teks bagi blok '.($index + 1).'.');
            }
            $result[] = match ($type) {
                'heading' => ['type' => $type, 'text' => $text, 'level' => in_array($block['level'] ?? '', ['h1', 'h2', 'h3'], true) ? $block['level'] : 'h2'],
                'paragraph', 'list' => ['type' => $type, 'text' => $text],
                'image' => ['type' => $type, 'url' => $this->safeUrl((string) ($block['url'] ?? ''), true), 'alt' => trim((string) ($block['alt'] ?? '')), 'caption' => trim((string) ($block['caption'] ?? ''))],
                default => ['type' => $type, 'text' => $text, 'url' => $this->safeUrl((string) ($block['url'] ?? ''))],
            };
        }

        return $result;
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
                'list' => '<ul data-cms-type="list">'.implode('', array_map(fn (string $line): string => '<li>'.e(trim($line)).'</li>', array_filter(explode("\n", $block['text']), fn (string $line): bool => trim($line) !== ''))).'</ul>',
                'image' => '<figure data-cms-type="image"><img src="'.e($block['url']).'" alt="'.e($block['alt']).'" loading="lazy">'.($block['caption'] !== '' ? '<figcaption>'.e($block['caption']).'</figcaption>' : '').'</figure>',
                default => '<p data-cms-type="'.$block['type'].'"><a href="'.e($block['url']).'"'.($block['type'] === 'download' ? ' download' : '').'>'.$text.'</a></p>',
            };
        }

        return $html;
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
        throw ValidationException::withMessages(['content' => $message]);
    }
}
