<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SmpClassesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = [
            ['level' => 'SMP', 'name' => '7A', 'display_name' => 'Kelas 7A', 'is_active' => true],
            ['level' => 'SMP', 'name' => '7B', 'display_name' => 'Kelas 7B', 'is_active' => true],
            ['level' => 'SMP', 'name' => '8A', 'display_name' => 'Kelas 8A', 'is_active' => true],
            ['level' => 'SMP', 'name' => '8B', 'display_name' => 'Kelas 8B', 'is_active' => true],
            ['level' => 'SMP', 'name' => '9A', 'display_name' => 'Kelas 9A', 'is_active' => true],
            ['level' => 'SMP', 'name' => '9B', 'display_name' => 'Kelas 9B', 'is_active' => true],
        ];

        foreach ($classes as $c) {
            \App\Models\SchoolClass::updateOrCreate(
                ['level' => $c['level'], 'name' => $c['name']],
                $c
            );
        }

        // Nonaktifkan kelas lama
        \App\Models\SchoolClass::whereIn('name', ['Kelas 7', 'Kelas 8', 'Kelas 9'])
            ->update(['is_active' => false]);
    }
}
