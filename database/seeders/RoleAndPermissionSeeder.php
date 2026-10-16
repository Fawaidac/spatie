<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define All Permissions
        $permissions = [
            // User Management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            // Role Management
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            'roles.give-permission',
            'roles.remove-permission',

            // Permission Management
            'permissions.view',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',

            // Profile / Setting Management
            'profile.view',
            'profile.update',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // 2. Define Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $userRole  = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // 3. Assign Permissions to Roles
        // Admin gets all permissions
        $adminRole->syncPermissions(Permission::all());

        // User gets profile permissions
        $userRole->syncPermissions([
            'profile.view',
            'profile.update',
        ]);

        // 4. Assign Admin role to existing admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'              => 'Admin',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
                'status'            => 'active',
                'phone'             => '+62 812 3456 7890',
                'bio'               => 'System Administrator with full access rights.',
            ]
        );
        $admin->syncRoles(['admin']);

        // 5. Create a standard demo user with 'user' role
        $demoUser = User::firstOrCreate(
            ['email' => 'user@gmail.com'],
            [
                'name'              => 'Standard User',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
                'status'            => 'active',
                'phone'             => '+62 857 1234 5678',
                'bio'               => 'Registered platform member.',
            ]
        );
        $demoUser->syncRoles(['user']);

        // 6. Create a couple more sample users for realism
        $sampleUsers = [
            [
                'name'   => 'Jane Smith',
                'email'  => 'jane.smith@email.com',
                'status' => 'active',
                'role'   => 'user',
            ],
            [
                'name'   => 'Bob Wilson',
                'email'  => 'bob.wilson@email.com',
                'status' => 'inactive',
                'role'   => 'user',
            ],
            [
                'name'   => 'Sarah Johnson',
                'email'  => 'sarah.j@email.com',
                'status' => 'active',
                'role'   => 'admin',
            ],
            [
                'name'   => 'Mike Brown',
                'email'  => 'mike.b@email.com',
                'status' => 'banned',
                'role'   => 'user',
            ],
        ];

        foreach ($sampleUsers as $sample) {
            $user = User::firstOrCreate(
                ['email' => $sample['email']],
                [
                    'name'              => $sample['name'],
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                    'status'            => $sample['status'],
                ]
            );
            $user->syncRoles([$sample['role']]);
        }
    }
}
