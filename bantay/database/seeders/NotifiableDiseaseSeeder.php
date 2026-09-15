<?php

namespace Database\Seeders;

use App\Models\Disease;
use App\Models\Icd10Code;
use Illuminate\Database\Seeder;

/**
 * Seeds the diseases and ICD-10 codes for the Philippine Integrated Disease
 * Surveillance and Response (PIDSR) list of notifiable diseases — Category I
 * (Immediately Notifiable) and Category II (Weekly Notifiable).
 *
 * ICD-10 codes below are standard WHO ICD-10 (2019 revision) codes. A few
 * entries are marked [VERIFY] where the exact sub-code can vary by source or
 * where PIDSR groups several conditions under one syndrome name that doesn't
 * map to a single clean ICD-10 code — cross-check these against your City
 * Health Office's actual reference sheet or the WHO ICD-10 browser
 * (icd.who.int/browse10/2019/en) before treating them as final for the thesis.
 */
class NotifiableDiseaseSeeder extends Seeder
{
    public function run(): void
    {
        $entries = [
            // --- Category I: Immediately Notifiable ---
            ['disease' => 'Acute Flaccid Paralysis', 'code' => 'A80', 'description' => 'Acute poliomyelitis'],
            ['disease' => 'Anthrax', 'code' => 'A22', 'description' => 'Anthrax'],
            ['disease' => 'Ebola Virus Disease', 'code' => 'A98.4', 'description' => 'Ebola virus disease'],
            ['disease' => 'Hand, Foot and Mouth Disease', 'code' => 'B08.4', 'description' => 'Enteroviral vesicular stomatitis with exanthem'],
            ['disease' => 'Malaria', 'code' => 'B50', 'description' => 'Plasmodium falciparum malaria'],
            ['disease' => 'Malaria', 'code' => 'B51', 'description' => 'Plasmodium vivax malaria'],
            ['disease' => 'Malaria', 'code' => 'B54', 'description' => 'Unspecified malaria'],
            ['disease' => 'Measles', 'code' => 'B05', 'description' => 'Measles'],
            ['disease' => 'Rubella', 'code' => 'B06', 'description' => 'Rubella (German measles)'],
            ['disease' => 'Meningococcal Disease', 'code' => 'A39', 'description' => 'Meningococcal infection'],
            ['disease' => 'MERS-CoV', 'code' => 'B34.2', 'description' => '[VERIFY] Coronavirus infection, unspecified — ICD-10 predates a dedicated MERS code'],
            ['disease' => 'Neonatal Tetanus', 'code' => 'A33', 'description' => 'Tetanus neonatorum'],
            ['disease' => 'Paralytic Shellfish Poisoning', 'code' => 'T61.2', 'description' => '[VERIFY] Other fish and shellfish poisoning'],
            ['disease' => 'Rabies', 'code' => 'A82', 'description' => 'Rabies'],
            ['disease' => 'Severe Acute Respiratory Infection', 'code' => 'J22', 'description' => '[VERIFY] Unspecified acute lower respiratory infection — SARI is a syndrome, not a single ICD-10 category'],
            ['disease' => 'Zika Virus Disease', 'code' => 'A92.5', 'description' => 'Zika virus disease'],

            // --- Category II: Weekly Notifiable ---
            ['disease' => 'Acute Bloody Diarrhea', 'code' => 'A09', 'description' => '[VERIFY] Infectious gastroenteritis and colitis, unspecified — often used as the general code for this syndrome'],
            ['disease' => 'Bacterial Meningitis', 'code' => 'G00', 'description' => 'Bacterial meningitis, not elsewhere classified'],
            ['disease' => 'Encephalitis Syndrome', 'code' => 'G04', 'description' => 'Encephalitis, myelitis and encephalomyelitis'],
            ['disease' => 'Acute Hemorrhagic Fever Syndrome', 'code' => 'A99', 'description' => 'Viral hemorrhagic fever, unspecified'],
            ['disease' => 'Acute Viral Hepatitis', 'code' => 'B15', 'description' => 'Acute hepatitis A'],
            ['disease' => 'Acute Viral Hepatitis', 'code' => 'B16', 'description' => 'Acute hepatitis B'],
            ['disease' => 'Acute Viral Hepatitis', 'code' => 'B17', 'description' => 'Other acute viral hepatitis'],
            ['disease' => 'Chikungunya', 'code' => 'A92.0', 'description' => 'Chikungunya virus disease'],
            ['disease' => 'Cholera', 'code' => 'A00', 'description' => 'Cholera'],
            ['disease' => 'Dengue Fever', 'code' => 'A90', 'description' => 'Dengue fever [classic dengue]'],
            ['disease' => 'Dengue Hemorrhagic Fever', 'code' => 'A91', 'description' => 'Dengue hemorrhagic fever'],
            ['disease' => 'Diphtheria', 'code' => 'A36', 'description' => 'Diphtheria'],
            ['disease' => 'Influenza-like Illness', 'code' => 'J11', 'description' => 'Influenza, virus not identified'],
            ['disease' => 'Leptospirosis', 'code' => 'A27', 'description' => 'Leptospirosis'],
            ['disease' => 'Tetanus', 'code' => 'A35', 'description' => 'Other tetanus (non-neonatal)'],
            ['disease' => 'Pertussis', 'code' => 'A37', 'description' => 'Whooping cough'],
            ['disease' => 'Rotavirus Enteritis', 'code' => 'A08.0', 'description' => 'Rotaviral enteritis'],
            ['disease' => 'Typhoid Fever', 'code' => 'A01.0', 'description' => 'Typhoid fever'],
            ['disease' => 'Paratyphoid Fever', 'code' => 'A01.1', 'description' => '[VERIFY grouping] Paratyphoid fever A — ICD-10 splits paratyphoid into A01.1/A01.2/A01.3 by type'],
        ];

        foreach ($entries as $entry) {
            $disease = Disease::firstOrCreate(
                ['name' => $entry['disease']],
                ['is_active' => true]
            );

            Icd10Code::firstOrCreate(
                ['code' => $entry['code']],
                [
                    'description' => $entry['description'],
                    'disease_id' => $disease->id,
                    'is_active' => true,
                ]
            );
        }
    }
}
