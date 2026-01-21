<?php

namespace Database\Seeders;

use App\Models\AchievementCategory;
use Illuminate\Database\Seeder;

class AchievementCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Akademik',
                'description' => 'Prestasi yang berkaitan dengan kegiatan akademik seperti olimpiade sains, kompetisi matematika, penelitian, dll.',
                'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                'color' => '#3b82f6',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Olahraga',
                'description' => 'Prestasi di bidang olahraga seperti atletik, sepak bola, basket, bulu tangkis, dll.',
                'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
                'color' => '#10b981',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Seni & Budaya',
                'description' => 'Prestasi di bidang seni seperti musik, tari, teater, seni rupa, fotografi, dll.',
                'icon' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01',
                'color' => '#ec4899',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Teknologi & Inovasi',
                'description' => 'Prestasi di bidang teknologi seperti programming, robotika, IoT, AI, aplikasi, dll.',
                'icon' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                'color' => '#6366f1',
                'order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Kepemimpinan & Organisasi',
                'description' => 'Prestasi dalam kepemimpinan organisasi kemahasiswaan, BEM, HIMA, UKM, dll.',
                'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                'color' => '#8b5cf6',
                'order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Penelitian & Karya Ilmiah',
                'description' => 'Prestasi dalam penelitian, karya ilmiah, publikasi jurnal, poster ilmiah, dll.',
                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'color' => '#06b6d4',
                'order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Kewirausahaan',
                'description' => 'Prestasi dalam bidang kewirausahaan, bisnis, startup, kompetisi bisnis plan, dll.',
                'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                'color' => '#f97316',
                'order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Pengabdian Masyarakat',
                'description' => 'Prestasi dalam kegiatan pengabdian masyarakat, volunteer, sosial, kemanusiaan, dll.',
                'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
                'color' => '#14b8a6',
                'order' => 8,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            AchievementCategory::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }

        // Deactivate old "Non-Akademik" if it exists (from old system)
        AchievementCategory::where('name', 'Non-Akademik')
            ->where('order', 2)
            ->update(['is_active' => false, 'order' => 99]);
    }
}
