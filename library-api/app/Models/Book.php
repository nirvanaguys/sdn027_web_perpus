<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

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
        'cover_image',
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
        'cover_url',
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

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover_image ? '/storage/' . $this->cover_image : null;
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function readingHistories(): HasMany
    {
        return $this->hasMany(ReadingHistory::class);
    }

    public function readingLists(): HasMany
    {
        return $this->hasMany(ReadingList::class);
    }
}
