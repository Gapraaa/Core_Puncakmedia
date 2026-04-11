<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Master', 'slug' => 'master'],
            ['name' => 'Superadmin', 'slug' => 'superadmin'],
            ['name' => 'Head Office', 'slug' => 'head-office'],
            ['name' => 'Finance', 'slug' => 'finance'],
            ['name' => 'Admin Sales', 'slug' => 'admin-sales'],
        ];

        foreach ($roles as $role) {
            Role::query()->updateOrCreate(
                ['slug' => $role['slug']],
                ['name' => $role['name']]
            );
        }
    }
}
