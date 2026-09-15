#!/usr/bin/env bash
# ==============================================================================
# BANTAY setup script
# Run this from Git Bash (bundled with Laragon) inside C:\laragon\www
#   bash setup_bantay.sh
#
# What it does:
#   1. Creates the Laravel project (skips if it already exists)
#   2. Configures .env for PostgreSQL (bantay_dev)
#   3. Installs Laravel Breeze for the auth UI (login/register/dashboard)
#   4. Installs Spatie Laravel-Permission for RBAC
#   5. Runs migrations
#   6. Publishes a role/permission seeder stub matching the 4 BANTAY roles
# ==============================================================================

set -e  # stop on first error

PROJECT_NAME="bantay"
DB_NAME="bantay_dev"
DB_USER="postgres"

# --- 1. Prompt for the Postgres password once, used to fill .env -----------
read -sp "Enter your PostgreSQL password (for user '${DB_USER}'): " DB_PASSWORD
echo ""

# --- 2. Create the Laravel project (skip if folder already exists) ---------
if [ -d "$PROJECT_NAME" ]; then
  echo ">> '$PROJECT_NAME' folder already exists, skipping composer create-project."
else
  echo ">> Creating Laravel project..."
  composer create-project laravel/laravel "$PROJECT_NAME"
fi

cd "$PROJECT_NAME"

# --- 3. Configure .env for PostgreSQL ---------------------------------------
echo ">> Configuring .env for PostgreSQL..."
sed -i "s/^DB_CONNECTION=.*/DB_CONNECTION=pgsql/" .env
sed -i "s/^DB_HOST=.*/DB_HOST=127.0.0.1/" .env
sed -i "s/^DB_PORT=.*/DB_PORT=5432/" .env
sed -i "s/^DB_DATABASE=.*/DB_DATABASE=${DB_NAME}/" .env
sed -i "s/^DB_USERNAME=.*/DB_USERNAME=${DB_USER}/" .env
sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD}/" .env

# --- 4. Install Laravel Breeze for the auth UI (login/register/dashboard) --
echo ">> Installing Laravel Breeze (auth UI: login, register, dashboard)..."
composer require laravel/breeze --dev
php artisan breeze:install blade --no-interaction

# --- 5. Install Spatie Laravel-Permission for RBAC --------------------------
echo ">> Installing Spatie Laravel-Permission..."
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# --- 6. Add a 'role' string column to users (simple approach) --------------
echo ">> Creating migration to add role tracking..."
php artisan make:migration add_role_to_users_table --table=users

MIGRATION_FILE=$(ls database/migrations/*_add_role_to_users_table.php | tail -n 1)
cat > "$MIGRATION_FILE" <<'EOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
EOF

# --- 7. Create the RBAC seeder for the 4 BANTAY roles -----------------------
echo ">> Creating RolePermissionSeeder..."
php artisan make:seeder RolePermissionSeeder

cat > database/seeders/RolePermissionSeeder.php <<'EOF'
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
EOF

# Register the seeder call in DatabaseSeeder.php
SEEDER_FILE="database/seeders/DatabaseSeeder.php"
if ! grep -q "RolePermissionSeeder" "$SEEDER_FILE"; then
  sed -i "s/public function run(): void\n    {/public function run(): void\n    {\n        \$this->call(RolePermissionSeeder::class);/" "$SEEDER_FILE"
fi

# --- 8. Install and build frontend assets -----------------------------------
echo ">> Installing Node dependencies and building assets..."
npm install
npm run build

# --- 9. Run migrations and seed the database --------------------------------
echo ">> Running migrations..."
php artisan migrate

echo ">> Seeding roles and permissions..."
php artisan db:seed --class=RolePermissionSeeder

echo ""
echo "=========================================================="
echo " Done. What you now have:"
echo "  - Laravel project in ./${PROJECT_NAME}"
echo "  - Connected to PostgreSQL database '${DB_NAME}'"
echo "  - Breeze auth UI installed (login, register, dashboard views)"
echo "  - Spatie RBAC installed with 4 BANTAY roles + starter permissions"
echo ""
echo " Next steps:"
echo "  1. cd ${PROJECT_NAME}"
echo "  2. php artisan serve"
echo "  3. Visit http://127.0.0.1:8000/register to create your first user"
echo "  4. Manually assign that user the 'Administrator' role via tinker:"
echo "       php artisan tinker"
echo "       \$u = App\\Models\\User::first();"
echo "       \$u->assignRole('Administrator');"
echo "=========================================================="
