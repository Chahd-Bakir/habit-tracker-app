<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@khotwa.app'],
            [
                'name' => 'Admin',
                'password' => bcrypt('Admin@123456'),
                'language' => 'en',
                'onboarding_completed' => true,
            ]
        );

        if (!$admin->hasRole('admin')) {
            $admin->assignRole($adminRole);
        }
    }
}
