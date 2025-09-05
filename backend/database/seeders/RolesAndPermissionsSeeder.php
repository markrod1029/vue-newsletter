<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view dashboard',
            'manage users',
            'manage posts',
            'manage events',
            'manage forums',
            'approve content',
            'create posts',
            'create events',
            'comment on content',
            'participate in forums'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo([
            'view dashboard',
            'manage users',
            'manage posts',
            'manage events',
            'manage forums',
            'approve content',
            'create posts',
            'create events',
            'comment on content',
            'participate in forums'
        ]);

        $studentRole = Role::create(['name' => 'student']);
        $studentRole->givePermissionTo([
            'view dashboard',
            'create posts',
            'create events',
            'comment on content',
            'participate in forums'
        ]);

        // Create admin user
        $admin = \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'status' => 'approved',
            'email_verified_at' => now()
        ]);
        $admin->assignRole('admin');

        // Create sample student user
        $student = \App\Models\User::create([
            'name' => 'Student User',
            'email' => 'student@example.com',
            'password' => bcrypt('password'),
            'grade_level' => 'Grade 12',
            'status' => 'approved',
            'email_verified_at' => now()
        ]);
        $student->assignRole('student');
    }
}
