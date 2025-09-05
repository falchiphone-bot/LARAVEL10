<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OpenAIChatTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'Bolsa de valores',
            'Família',
            'Futebol',
            'Energia',
            'Trabalho',
            'Estudos',
        ];

        foreach ($types as $name) {
            $slug = Str::slug($name);
            $exists = DB::table('openai_chat_types')->where('slug', $slug)->exists();
            if (!$exists) {
                DB::table('openai_chat_types')->insert([
                    'name' => $name,
                    'slug' => $slug,
                ]);
            }
        }
    }
}
