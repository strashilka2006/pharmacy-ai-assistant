<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'total', 'status', 'name', 'phone', 'address', 'payment_id', 'pay_url',
    ];

    protected function casts(): array
    {
        return ['total' => 'decimal:2'];
    }

    /** Человеческие названия статусов — бывшая getOrderStatusText(). */
    public const STATUS_LABELS = [
        'pending_payment' => 'Ожидает оплаты',
        'new' => 'Заказ сформирован',
        'paid' => 'Оплачен',
        'processing' => 'Собран на складе',
        'shipped' => 'Отправлен в сортировочный центр',
        'at_hub' => 'Прибыл в сортировочный центр',
        'sent_to_pickup' => 'Отправлен в пункт выдачи',
        'ready_for_pickup' => 'Готов к выдаче',
        'delivered' => 'Готов к выдаче',
        'cancelled' => 'Отменён',
    ];

    /** Шаг прогресс-бара на странице заказа. */
    public const STATUS_STEPS = [
        'pending_payment' => 0,
        'new' => 0,
        'paid' => 1,
        'processing' => 1,
        'shipped' => 2,
        'at_hub' => 3,
        'sent_to_pickup' => 4,
        'ready_for_pickup' => 5,
        'delivered' => 5,
        'cancelled' => -1,
    ];

    public const CANCELLABLE = ['new', 'processing', 'pending_payment'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? 'В обработке';
    }

    public function getStatusStepAttribute(): int
    {
        return self::STATUS_STEPS[$this->status] ?? 0;
    }

    public function getProgressPercentAttribute(): float
    {
        return $this->status_step > 0 ? $this->status_step / 5 * 100 : 0;
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, self::CANCELLABLE, true);
    }
}
