<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'brand_id', 'name', 'short_description', 'description',
        'long_description', 'price', 'supplier', 'prescription', 'usage_info',
        'stock', 'image', 'label', 'indications', 'composition',
        'contraindications', 'drug_interactions', 'overdose',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'prescription' => 'boolean',
            'stock' => 'integer',
        ];
    }

    /** Ярлыки товара — бывшая getProductLabels(). */
    public const LABELS = [
        'bad' => 'БАД',
        'imported' => 'Импортный товар',
        'strong' => 'Сильнодействующее',
        'kids' => 'Для детей',
    ];

    /** Расшифровка ярлыка — бывшая getLabelText(). */
    public const LABEL_TEXTS = [
        'bad' => 'Внимание: БАД — не является лекарственным средством.',
        'imported' => 'Товар произведён за рубежом. Сертификация может отличаться.',
        'strong' => 'Сильнодействующее средство. Перед применением требуется консультация врача.',
        'kids' => 'Подходит для детей. Использовать строго согласно инструкции.',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function getLabelTextAttribute(): ?string
    {
        return self::LABEL_TEXTS[$this->label] ?? null;
    }

    public function getInStockAttribute(): bool
    {
        return $this->stock > 0;
    }

    /** Заглушка вместо пустой картинки — бывшая imgUrl(). */
    public function getImageUrlAttribute(): string
    {
        $path = trim((string) $this->image);

        if ($path === '') {
            return asset('images/no-photo.jpg');
        }

        if (preg_match('~^https?://~i', $path)) {
            return $path;
        }

        return asset('storage/products/' . ltrim($path, '/'));
    }

    /** Фильтры каталога: ?q=&brand=&price_min=&price_max= */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where('name', 'like', '%' . $term . '%'))
            ->when($filters['brand'] ?? null, fn ($q, $brand) => $q->where('brand_id', (int) $brand))
            ->when($filters['price_min'] ?? null, fn ($q, $min) => $q->where('price', '>=', (float) $min))
            ->when($filters['price_max'] ?? null, fn ($q, $max) => $q->where('price', '<=', (float) $max));
    }

    public function scopeSorted(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name_asc' => $query->orderBy('name'),
            'brand_asc' => $query->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->orderBy('brands.name')
                ->orderBy('products.name')
                ->select('products.*'),
            default => $query->orderByDesc('id'),
        };
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    /** Безрецептурные — только их показывает ИИ-консультант. */
    public function scopeOverTheCounter(Builder $query): Builder
    {
        return $query->where('prescription', false);
    }
}
