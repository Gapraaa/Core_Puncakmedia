<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PmsDemoSeeder::class,
        ]);

        $roleMap = Role::query()->pluck('id', 'slug');

        $masterUser = User::query()->where('email', 'master@puncakmedia.local')->first();
        $superadminUser = User::query()->where('email', 'superadmin@puncakmedia.local')->first();
        $headOfficeUser = User::query()->where('email', 'headoffice@puncakmedia.local')->first();
        $financeUser = User::query()->where('email', 'finance@puncakmedia.local')->first();
        $salesUser = User::query()->where('email', 'sales@puncakmedia.local')->first();

        if ($masterUser !== null && isset($roleMap['master'])) {
            $masterUser->roles()->syncWithoutDetaching([$roleMap['master']]);
        }

        if ($superadminUser !== null && isset($roleMap['superadmin'])) {
            $superadminUser->roles()->syncWithoutDetaching([$roleMap['superadmin']]);
        }

        if ($headOfficeUser !== null && isset($roleMap['head-office'])) {
            $headOfficeUser->roles()->syncWithoutDetaching([$roleMap['head-office']]);
        }

        if ($financeUser !== null && isset($roleMap['finance'])) {
            $financeUser->roles()->syncWithoutDetaching([$roleMap['finance']]);
        }

        if ($salesUser !== null && isset($roleMap['admin-sales'])) {
            $salesUser->roles()->syncWithoutDetaching([$roleMap['admin-sales']]);
        }
    }
}
