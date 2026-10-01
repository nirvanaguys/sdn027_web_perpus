<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'publisher',
        'isbn',
        'category',
        'description',
        'file_path',
        'file_format',
        'file_size',
        'stock',
        'shelf_location',
    ];

    protected $hidden = [
        'file_path',
    ];

    protected $appends = [
        'has_ebook',
        'file_size_formatted',
    ];

    public function getHasEbookAttribute(): bool
    {
        return !empty($this->file_path);
    }

    public function getFileSizeFormattedAttribute(): ?string
    {
        if (!$this->file_size) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->file_size;
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
