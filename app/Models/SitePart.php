<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SitePart extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'label', 'html'];

    /** Returns the stored HTML, or null when it isn't in the database (falls back to the Blade partial). */
    public static function htmlFor(string $key): ?string
    {
        try {
            return static::query()->whereKey($key)->value('html');
        } catch (\Throwable) {
            return null;
        }
    }
}
