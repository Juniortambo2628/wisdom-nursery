<?php

namespace Database\Seeders;

use App\Models\TeamMember;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 4) as $i) {
            TeamMember::firstOrCreate(['sort_order' => $i], [
                'name' => '[Team member name]',
                'role' => '[Role]',
            ]);
        }
    }
}
