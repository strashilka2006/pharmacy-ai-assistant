<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Заливает данные из старого дампа schema.sql.
 * Запуск: php artisan db:seed --class=LegacyDataSeeder
 *
 * Повторный запуск безопасен: записи обновляются по id, дублей не будет.
 */
class LegacyDataSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(
            file_get_contents(database_path('seeders/legacy-data.json')),
            true
        );

        foreach ($data['brands'] ?? [] as $row) {
            Brand::updateOrCreate(['id' => $row['id']], [
                'name' => $row['name'],
                'description' => $row['description'],
                'logo' => $row['logo'],
                'banner' => $row['banner'],
            ]);
        }
        $this->command->info('Брендов: ' . count($data['brands'] ?? []));

        foreach ($data['products'] ?? [] as $row) {
            Product::updateOrCreate(['id' => $row['id']], [
                'brand_id' => $row['brand_id'] ?: null,
                // категорий в дампе нет, ссылку не тянем
                'category_id' => null,
                'name' => $row['name'],
                'short_description' => $row['short_description'],
                'description' => $row['description'],
                'long_description' => $row['long_description'],
                'price' => $row['price'],
                'supplier' => $row['supplier'],
                'prescription' => (bool) $row['prescription'],
                'usage_info' => $row['usage_info'],
                'stock' => max(0, (int) $row['stock']),
                'image' => $row['image'],
                'label' => $row['label'] ?: null,
                'indications' => $row['indications'] ?? null,
                'composition' => $row['composition'] ?? null,
                'contraindications' => $row['contraindications'] ?? null,
                'drug_interactions' => $row['drug_interactions'] ?? null,
                'overdose' => $row['overdose'] ?? null,
            ]);
        }
        $this->command->info('Товаров: ' . count($data['products'] ?? []));

        // Пользователи: старые bcrypt-хеши переносятся как есть,
        // люди зайдут своими прежними паролями.
        foreach ($data['users'] ?? [] as $row) {
            User::updateOrCreate(['email' => $row['email']], [
                'password' => $row['password'],
                'name' => $row['name'],
                'phone' => $row['phone'],
                'role' => $row['role'],
                'address' => $row['address'] ?? null,
                'email_verified_at' => now(),
            ]);
        }
        $this->command->info('Пользователей: ' . count($data['users'] ?? []));
    }
}
