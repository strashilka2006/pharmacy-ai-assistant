<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'logo', 'banner'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeWithBanner(Builder $query): Builder
    {
        return $query->whereNotNull('banner')->where('banner', '!=', '');
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->assetUrl($this->logo);
    }

    public function getBannerUrlAttribute(): ?string
    {
        return $this->assetUrl($this->banner);
    }

    private function assetUrl(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        return preg_match('~^https?://~i', $path)
            ? $path
            : asset('storage/brands/' . ltrim($path, '/'));
    }
}
