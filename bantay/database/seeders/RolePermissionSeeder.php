<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // --- Roles (Table 7 in the thesis: Role-Permission Matrix) ---
        $admin    = Role::firstOrCreate(['name' => 'Administrator']);
        $officer  = Role::firstOrCreate(['name' => 'Surveillance Officer']);
        $health   = Role::firstOrCreate(['name' => 'Health Officer']);
        $viewer   = Role::firstOrCreate(['name' => 'Viewer']);

        // --- Permissions (expand this list as each module gets built) ---
        $permissions = [
            'manage users',
            'manage reference data',
            'upload datasets',
            'correct quarantined records',
            'view dashboards',
            'view geographic map',
            'manage surveillance rules',
            'acknowledge alerts',
            'escalate alerts',
            'generate reports',
            'view audit logs',
            'manage backups',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // --- Assign permissions per role ---
        $admin->givePermissionTo(Permission::all());

        $officer->givePermissionTo([
            'upload datasets',
            'correct quarantined records',
            'view dashboards',
            'view geographic map',
            'generate reports',
        ]);

        $health->givePermissionTo([
            'view dashboards',
            'view geographic map',
            'manage surveillance rules',
            'acknowledge alerts',
            'escalate alerts',
            'generate reports',
        ]);

        $viewer->givePermissionTo([
            'view dashboards',
            'view geographic map',
        ]);
    }
}
