<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Admin;
use App\Models\Discount;
use App\Models\FineSetting;
use App\Models\SchoolClass;
use App\Models\SppSetting;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Admins ────────────────────────────────────────────────────────
        $admins = [
            ['name' => 'Super Admin', 'email' => 'admin@spp.test', 'role' => 'super_admin', 'whatsapp' => '628123456789'],
            ['name' => 'Admin 1', 'email' => 'admin1@spp.test', 'role' => 'admin', 'whatsapp' => '628234567890'],
            ['name' => 'Admin 2', 'email' => 'admin2@spp.test', 'role' => 'admin', 'whatsapp' => '628345678901'],
            ['name' => 'Admin 3', 'email' => 'admin3@spp.test', 'role' => 'admin', 'whatsapp' => '628456789012'],
        ];

        foreach ($admins as $admin) {
            Admin::updateOrCreate(['email' => $admin['email']], [
                ...$admin,
                'password' => Hash::make('password'),
            ]);
        }

        // ─── Academic Year ─────────────────────────────────────────────────
        $year = AcademicYear::updateOrCreate(
            ['name' => '2024/2025'],
            ['start_year' => 2024, 'end_year' => 2025, 'is_active' => true]
        );

        // ─── School Classes ────────────────────────────────────────────────
        $classes = [
            // PG
            ['level' => 'PG', 'name' => 'PG', 'display_name' => 'PG (Play Group)', 'sort_order' => 1],
            // TK
            ['level' => 'TK', 'name' => 'TK A', 'display_name' => 'TK A', 'sort_order' => 2],
            ['level' => 'TK', 'name' => 'TK B', 'display_name' => 'TK B', 'sort_order' => 3],
            // SD
            ['level' => 'SD', 'name' => '1A', 'display_name' => 'Kelas 1A', 'sort_order' => 4],
            ['level' => 'SD', 'name' => '1B', 'display_name' => 'Kelas 1B', 'sort_order' => 5],
            ['level' => 'SD', 'name' => '1C', 'display_name' => 'Kelas 1C', 'sort_order' => 6],
            ['level' => 'SD', 'name' => '2A', 'display_name' => 'Kelas 2A', 'sort_order' => 7],
            ['level' => 'SD', 'name' => '2B', 'display_name' => 'Kelas 2B', 'sort_order' => 8],
            ['level' => 'SD', 'name' => '2C', 'display_name' => 'Kelas 2C', 'sort_order' => 9],
            ['level' => 'SD', 'name' => '3A', 'display_name' => 'Kelas 3A', 'sort_order' => 10],
            ['level' => 'SD', 'name' => '3B', 'display_name' => 'Kelas 3B', 'sort_order' => 11],
            ['level' => 'SD', 'name' => '3C', 'display_name' => 'Kelas 3C', 'sort_order' => 12],
            ['level' => 'SD', 'name' => '4A', 'display_name' => 'Kelas 4A', 'sort_order' => 13],
            ['level' => 'SD', 'name' => '4B', 'display_name' => 'Kelas 4B', 'sort_order' => 14],
            ['level' => 'SD', 'name' => '4C', 'display_name' => 'Kelas 4C', 'sort_order' => 15],
            ['level' => 'SD', 'name' => '5A', 'display_name' => 'Kelas 5A', 'sort_order' => 16],
            ['level' => 'SD', 'name' => '5B', 'display_name' => 'Kelas 5B', 'sort_order' => 17],
            ['level' => 'SD', 'name' => '5C', 'display_name' => 'Kelas 5C', 'sort_order' => 18],
            ['level' => 'SD', 'name' => '6A', 'display_name' => 'Kelas 6A', 'sort_order' => 19],
            ['level' => 'SD', 'name' => '6B', 'display_name' => 'Kelas 6B', 'sort_order' => 20],
            ['level' => 'SD', 'name' => '6C', 'display_name' => 'Kelas 6C', 'sort_order' => 21],
            // SMP
            ['level' => 'SMP', 'name' => '7', 'display_name' => 'Kelas 7', 'sort_order' => 22],
            ['level' => 'SMP', 'name' => '8', 'display_name' => 'Kelas 8', 'sort_order' => 23],
            ['level' => 'SMP', 'name' => '9', 'display_name' => 'Kelas 9', 'sort_order' => 24],
        ];

        foreach ($classes as $cls) {
            SchoolClass::updateOrCreate(['level' => $cls['level'], 'name' => $cls['name']], $cls);
        }

        // ─── Discounts ─────────────────────────────────────────────────────
        $discounts = [
            ['name' => 'Diskon Keluarga (2 Anak)', 'type' => 'family', 'value' => 10, 'is_percent' => true, 'description' => 'Diskon 10% untuk anak ke-2'],
            ['name' => 'Diskon Keluarga (3+ Anak)', 'type' => 'family', 'value' => 15, 'is_percent' => true, 'description' => 'Diskon 15% untuk anak ke-3 dan seterusnya'],
            ['name' => 'Diskon Berprestasi', 'type' => 'achievement', 'value' => 20, 'is_percent' => true, 'description' => 'Diskon 20% untuk siswa berprestasi'],
            ['name' => 'Diskon Kurang Mampu', 'type' => 'poor', 'value' => 50, 'is_percent' => true, 'description' => 'Diskon 50% untuk siswa kurang mampu'],
        ];

        foreach ($discounts as $disc) {
            Discount::updateOrCreate(['name' => $disc['name']], $disc);
        }

        // ─── SPP Settings ──────────────────────────────────────────────────
        $sppAmounts = [
            'PG' => 200000,
            'TK' => 250000,
            'SD' => 300000,
            'SMP' => 400000,
        ];

        foreach (SchoolClass::all() as $class) {
            SppSetting::updateOrCreate(
                ['class_id' => $class->id, 'academic_year_id' => $year->id],
                ['amount' => $sppAmounts[$class->level] ?? 300000]
            );
        }

        // ─── Fine Settings ─────────────────────────────────────────────────
        FineSetting::updateOrCreate(
            ['academic_year_id' => $year->id],
            ['due_date' => 10, 'fine_type' => 'flat', 'amount' => 10000,
             'description' => 'Denda keterlambatan Rp 10.000 per bulan (flat)']
        );

        // ─── Sample Students ───────────────────────────────────────────────
        $class1a = SchoolClass::where('name', '1A')->first();
        $class2b = SchoolClass::where('name', '2B')->first();
        $classTka = SchoolClass::where('name', 'TK A')->first();

        if ($class1a) {
            Student::updateOrCreate(['nisn' => '001'], [
                'full_name' => 'Ahmad Rizky Pratama',
                'birth_date' => '2017-05-15',
                'birth_place' => 'Jakarta',
                'gender' => 'L',
                'class_id' => $class1a->id,
                'academic_year_id' => $year->id,
                'status' => 'active',
                'parent_name' => 'Budi Pratama',
                'whatsapp' => '628111222333',
            ]);

            Student::updateOrCreate(['nisn' => '002'], [
                'full_name' => 'Siti Nurhaliza',
                'birth_date' => '2017-08-20',
                'birth_place' => 'Bandung',
                'gender' => 'P',
                'class_id' => $class1a->id,
                'academic_year_id' => $year->id,
                'status' => 'active',
                'parent_name' => 'Heri Santoso',
                'whatsapp' => '628222333444',
            ]);
        }
    }
}
