<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'path',
        'section',
        'meta_description',
        'body_class',
        'styles',
        'body',
        'scripts',
        'status',
        'is_system',
    ];

    protected function casts(): array
    {
        return ['is_system' => 'boolean'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class);
    }

    /** Public URL of this page. */
    public function url(): string
    {
        return url('/' . ltrim((string) $this->path, '/'));
    }
}
