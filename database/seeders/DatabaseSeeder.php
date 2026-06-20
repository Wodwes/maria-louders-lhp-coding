<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Defaults to a lightweight local demo. Override with SEED_ROWS for
        // larger datasets, e.g. SEED_ROWS=50000 php artisan db:seed.
        $this->call(EventSeeder::class);
    }
}
