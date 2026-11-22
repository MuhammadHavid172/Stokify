<?php

namespace Database\Factories;

use App\Models\Kios;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kios>
 */
class KiosFactory extends Factory
{
    protected $model = Kios::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_kios' => 'Kios ' . fake()->company(),
            'alamat' => fake()->address(),
            'no_telepon' => fake()->phoneNumber(),
            'status' => 'aktif',
        ];
    }
}
