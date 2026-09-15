<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Corrects two mismatches between the original RolePermissionSeeder (from
 * the Phase 0 setup script) and what the BANTAY wireframes actually specify:
 *
 * 1. Health Officer had been given 'manage surveillance rules' — but the
 *    Health Officer wireframe explicitly lists "Change rule thresholds,
 *    priority, or versions" under Restricted Functions. Removed.
 *
 * 2. Surveillance Officer never had 'acknowledge alerts' — but their
 *    wireframe explicitly grants "Acknowledge and review assigned alerts".
 *    Added. They do NOT get 'escalate alerts' (that stays Health
 *    Officer/Administrator only, per both wireframes' restrictions).
 *
 * Safe to re-run — uses firstOrCreate/sync-style calls, no duplicates.
 */
class FixAlertRulePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Make sure the two alert-related permissions exist (they should
        // already, from Phase 0 — this is just a safety net).
        Permission::firstOrCreate(['name' => 'acknowledge alerts']);
        Permission::firstOrCreate(['name' => 'escalate alerts']);

        $health = Role::where('name', 'Health Officer')->first();
        $officer = Role::where('name', 'Surveillance Officer')->first();

        if ($health) {
            $health->revokePermissionTo('manage surveillance rules');
            $health->givePermissionTo(['acknowledge alerts', 'escalate alerts']);
        }

        if ($officer) {
            $officer->givePermissionTo(['acknowledge alerts']);
            // Explicitly does NOT get 'escalate alerts' or 'manage surveillance rules'.
        }
    }
}
