<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $studentRole = Role::create(['name' => 'student']);

        // Create permissions
        $permissions = [
            'manage users',
            'manage content',
            'approve content',
            'create posts',
            'create events',
            'comment on content'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign permissions to roles
        $adminRole->givePermissionTo([
            'manage users',
            'manage content',
            'approve content',
            'create posts',
            'create events',
            'comment on content'
        ]);

        $studentRole->givePermissionTo([
            'create posts',
            'create events',
            'comment on content'
        ]);

        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'status' => 'approved'
        ]);

        $admin->assignRole('admin');

        // Create sample student user
        $student = User::create([
            'name' => 'Student User',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'grade_level' => 'Grade 12',
            'status' => 'approved'
        ]);

        $student->assignRole('student');
    }
}
