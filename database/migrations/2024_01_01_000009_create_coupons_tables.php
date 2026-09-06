<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Таблицы из старой схемы, на которые в коде не было ни одной ссылки.
 * Оставлены, чтобы дамп импортировался без потерь. Если купоны не нужны —
 * удали этот файл целиком, ничего не отвалится.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->unsignedInteger('discount_percent');
            $table->dateTime('expires_at')->nullable();
            $table->boolean('active')->default(true);
        });

        Schema::create('used_coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->timestamp('used_at')->useCurrent();
        });

        Schema::create('admin_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->text('action');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_logs');
        Schema::dropIfExists('used_coupons');
        Schema::dropIfExists('coupons');
    }
};
