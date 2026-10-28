<?php

namespace Database\Seeders;

use App\Models\Barangay;
use Illuminate\Database\Seeder;

/**
 * Seeds Calapan City barangays with latitude/longitude for the geographic map.
 *
 * IMPORTANT: The coordinates below are APPROXIMATE placeholders (roughly where
 * each barangay sits within Calapan City, around 13.41 N, 121.18 E). They are
 * good enough to render markers on the dashboard map, but they have NOT been
 * surveyed. Before the thesis defense / real deployment, replace them with
 * verified values (e.g. from the City Planning Office, PhilGIS / PSA barangay
 * boundaries, or by dropping a pin on Google Maps / OpenStreetMap).
 *
 * Safe to re-run: uses updateOrCreate on the unique barangay name, so it will
 * not create duplicates. NOTE: re-running overwrites lat/long edited via the
 * admin UI for these names.
 *
 * Run with:  php artisan db:seed --class=BarangaySeeder
 */
class BarangaySeeder extends Seeder
{
    public function run(): void
    {
        $barangays = [
            // name                  latitude     longitude
            ['Bayanan I',           13.4230000, 121.1810000],
            ['Bayanan II',          13.4260000, 121.1830000],
            ['Bucayao',             13.4165000, 121.1850000],
            ['Camilmil',            13.4075000, 121.1830000],
            ['Canubing I',          13.4390000, 121.1760000],
            ['Guinobatan',          13.3960000, 121.1900000],
            ['Ibaba East',          13.4115000, 121.1815000],
            ['Ibaba West',          13.4105000, 121.1790000],
            ['Lalud',               13.4015000, 121.1895000],
            ['Lazareto',            13.4040000, 121.1770000],
            ['Libis',               13.4190000, 121.1730000],
            ['Parang',              13.4025000, 121.1700000],
            ['Salong',              13.3870000, 121.1830000],
            ['San Vicente Central', 13.4145000, 121.1795000],
            ['Sta. Isabel',         13.4060000, 121.1905000],
            ['Sto. Niño',           13.4085000, 121.1760000],
            ['Wawa',                13.4300000, 121.1950000],
        ];

        foreach ($barangays as [$name, $lat, $lng]) {
            Barangay::updateOrCreate(
                ['name' => $name],
                [
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'is_active' => true,
                ]
            );
        }
    }
}
