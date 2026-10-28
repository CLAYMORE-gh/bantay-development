<?php

namespace Database\Seeders;

use App\Models\Barangay;
use Illuminate\Database\Seeder;

/**
 * Loads Calapan City's 62 official barangays (PSA 2020 list) from
 * data/calapan_barangays.csv. Safe to re-run: it only creates names that
 * don't exist yet and never touches coordinates you've already set.
 *
 * The importer ignores suffixes such as "(POB.)", so "IBABA WEST (POB.)"
 * in an iClinicSys export matches "Ibaba West" here.
 */
class CalapanBarangaySeeder extends Seeder
{
    public function run(): void
    {
        $handle = fopen(__DIR__ . '/data/calapan_barangays.csv', 'r');
        fgetcsv($handle); // header

        $created = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $name = trim($row[0] ?? '');
            if ($name === '') {
                continue;
            }

            $barangay = Barangay::firstOrCreate(['name' => $name], ['is_active' => true]);
            $created += $barangay->wasRecentlyCreated ? 1 : 0;
        }
        fclose($handle);

        $this->command?->info("Barangays: {$created} created, " . (62 - $created) . ' already existed.');
    }
}
