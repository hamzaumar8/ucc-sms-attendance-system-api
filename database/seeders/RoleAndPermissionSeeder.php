<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $roles = ['admin', 'lecturer', 'course-rep', 'student'];

        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }

        // Create permissions
        $permissions = [
            'manage users',
            'manage roles',
            'manage permissions',
            'view courses',
            'manage courses',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign permissions to Admin role
        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo($permissions);

        // Assign specific permissions to Lecturer role
        $lecturerRole = Role::findByName('lecturer');
        $lecturerRole->givePermissionTo(['view courses', 'manage courses']);

        // Assign specific permissions to Course rep role
        $studentRole = Role::findByName('course-rep');
        $studentRole->givePermissionTo(['view courses']);


        // Assign specific permissions to Student role
        $studentRole = Role::findByName('student');
        $studentRole->givePermissionTo(['view courses']);
    }
}
