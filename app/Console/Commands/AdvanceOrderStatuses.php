<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

/**
 * Демо-продвижение заказов по статусам.
 *
 * В оригинале это была функция updateUserOrderStatuses(), которая дёргалась
 * из bootstrap.php на КАЖДОМ запросе залогиненного пользователя: лишний
 * SELECT + пачка UPDATE на любой чих, включая загрузку картинки.
 * Здесь это одна фоновая задача раз в минуту.
 *
 * Регистрация (routes/console.php в Laravel 11+):
 *   Schedule::command('orders:advance')->everyMinute();
 */
class AdvanceOrderStatuses extends Command
{
    protected $signature = 'orders:advance';

    protected $description = 'Продвигает демо-заказы по цепочке статусов доставки';

    public function handle(): int
    {
        if (! config('pharmacy.order_simulation.enabled')) {
            $this->info('Симуляция выключена (ORDER_SIMULATION_ENABLED=false).');

            return self::SUCCESS;
        }

        $chain = config('pharmacy.order_simulation.chain');
        $step = config('pharmacy.order_simulation.step_seconds');
        $updated = 0;

        Order::query()
            ->whereNotIn('status', ['cancelled', 'paid', 'pending_payment', 'delivered'])
            ->chunkById(200, function ($orders) use ($chain, $step, &$updated) {
                foreach ($orders as $order) {
                    $elapsed = max(0, now()->getTimestamp() - $order->created_at->getTimestamp());
                    $index = min(intdiv($elapsed, $step), count($chain) - 1);
                    $target = $chain[$index];

                    if ($order->status !== $target) {
                        $order->update(['status' => $target]);
                        $updated++;
                    }
                }
            });

        $this->info("Обновлено заказов: {$updated}");

        return self::SUCCESS;
    }
}
