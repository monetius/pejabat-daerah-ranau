<?php

namespace App\Models;

use App\Support\NavMenu;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One entry in the top navigation. Two levels only:
 * a top-level item with sub-items becomes a dropdown, otherwise it is a plain link.
 */
class MenuItem extends Model
{
    protected $fillable = ['parent_id', 'label', 'page_id', 'url', 'new_tab', 'is_visible', 'sort_order'];

    protected function casts(): array
    {
        return ['new_tab' => 'boolean', 'is_visible' => 'boolean'];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /** Where this item points. A CMS page always wins, so renaming a page path never breaks the menu. */
    public function href(): string
    {
        if ($this->page) {
            return '/' . ltrim((string) $this->page->path, '/');
        }

        return filled($this->url) ? $this->url : '#';
    }

    /** Should visitors see this link? Hidden items and links to draft pages are left out. */
    public function isShown(): bool
    {
        if (!$this->is_visible) {
            return false;
        }
        if ($this->page_id !== null) {
            return $this->page !== null && $this->page->status === 'published';
        }

        return filled($this->url);
    }

    /** Visible top-level items with their sub-items, in order. */
    public static function tree(): Collection
    {
        try {
            return static::query()
                ->whereNull('parent_id')
                ->where('is_visible', true)
                ->orderBy('sort_order')->orderBy('id')
                ->with(['page', 'children.page'])
                ->get();
        } catch (\Throwable) {
            return new Collection();
        }
    }

    /**
     * Swaps the menu list inside the navbar HTML for the one managed in the admin.
     * If no menu has been set up yet, the navbar is returned untouched.
     */
    public static function inject(string $navbarHtml): string
    {
        try {
            if (!static::query()->exists()) {
                return $navbarHtml;
            }
        } catch (\Throwable) {
            return $navbarHtml;
        }

        $items = view('partials.nav-items', ['items' => static::tree()])->render();

        return NavMenu::replaceList($navbarHtml, $items) ?? $navbarHtml;
    }
}
