<?php

namespace Database\Seeders;

use App\Models\TeamMember;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        // Roles as drawn in the concepts deck; names and photos come from the client.
        foreach (['Founder / Director', 'Nursery manager', 'Agronomist', 'Sales & orders'] as $i => $role) {
            TeamMember::firstOrCreate(['sort_order' => $i + 1], [
                'name' => '[Name]',
                'role' => $role,
            ]);
        }
    }
}
