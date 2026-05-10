<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class InitialAccessSeeder extends Seeder
{
    public function run(): void
    {
        $masterUser = User::query()->updateOrCreate(
            ['email' => 'master@puncakmedia.local'],
            [
                'name' => 'Master User',
                'username' => 'master',
                'password' => 'password',
                'is_active' => true,
            ],
        );

        $masterRoleId = Role::query()
            ->where('slug', 'master')
            ->value('id');

        if ($masterRoleId !== null) {
            $masterUser->roles()->syncWithoutDetaching([$masterRoleId]);
        }
    }
}
