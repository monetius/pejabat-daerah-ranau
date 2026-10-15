<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    public const CATEGORIES = [
        'hebahan' => 'Hebahan',
        'berita' => 'Berita',
        'notis' => 'Notis',
    ];

    protected $fillable = ['title', 'slug', 'category', 'author', 'summary', 'image', 'body', 'status', 'published_at'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function publicationState(): string
    {
        if ($this->status !== 'published') {
            return 'draft';
        }

        return $this->published_at?->isFuture() ? 'scheduled' : 'published';
    }

    public function publicationLabel(): string
    {
        return match ($this->publicationState()) {
            'published' => 'Diterbitkan',
            'scheduled' => 'Dijadualkan',
            default => 'Draf',
        };
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst((string) $this->category);
    }

    public function categoryIcon(): string
    {
        return match ($this->category) {
            'berita' => 'fa-newspaper',
            'notis' => 'fa-bell',
            default => 'fa-bullhorn',
        };
    }
}
