<?php

use Illuminate\Support\Facades\Schedule;

// Демо-продвижение заказов по статусам доставки.
// Раньше это выполнялось на каждом запросе в bootstrap.php.
Schedule::command('orders:advance')->everyMinute()->withoutOverlapping();
