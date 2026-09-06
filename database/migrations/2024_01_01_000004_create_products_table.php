<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->string('name');
            $table->text('short_description')->nullable();
            $table->text('description')->nullable();
            $table->longText('long_description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('supplier')->nullable();
            $table->boolean('prescription')->default(false);
            $table->text('usage_info')->nullable();
            $table->integer('stock')->default(0);
            $table->string('image')->nullable();
            $table->string('label', 50)->nullable();

            // Медицинские поля карточки
            $table->text('indications')->nullable();
            $table->text('composition')->nullable();
            $table->text('contraindications')->nullable();
            $table->text('drug_interactions')->nullable();
            $table->text('overdose')->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->index('name');
            $table->index('price');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
