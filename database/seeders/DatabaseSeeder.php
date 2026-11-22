<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Hanya memanggil DemoSeeder pada lingkungan non-production (local/staging/testing).
     */
    public function run(): void
    {
        if (! app()->isProduction()) {
            $this->call(DemoSeeder::class);
        }
    }
}
