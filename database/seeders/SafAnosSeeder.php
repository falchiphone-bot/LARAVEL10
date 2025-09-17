<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SafAno;

class SafAnosSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([2023, 2024, 2025, 2026] as $ano) {
            SafAno::firstOrCreate(['ano' => $ano]);
        }
    }
}
