<?php

namespace Tests\Unit;

use App\Support\IClinicSysMapper;
use App\Support\NameNormalizer;
use PHPUnit\Framework\TestCase;

class IClinicSysMapperTest extends TestCase
{
    /** The exact header row of SEPTEMBER-2026-SOUTH.xlsx */
    private const REAL_HEADERS = [
        '#', 'DATE', 'TIME', 'CCT (Y/N)', 'PHIC(M/D)', 'NAME', 'SITIO/BARANGAY', 'DATE OF BIRTH',
        'AGE', 'SEX', 'CS', 'PWD(Y/N)', 'TYPE', 'CAUSE', 'COMPLAINTS', 'ICD10 CODE', 'DIAGNOSIS',
        'TREATMENTS', 'REFERRED', 'REMARKS',
    ];

    public function test_real_iclinicsys_headers_map_to_the_whitelisted_fields(): void
    {
        $map = IClinicSysMapper::mapHeaders(self::REAL_HEADERS);

        $this->assertSame([], $map['missing']);
        $this->assertSame('consultation_date', $map['fields'][1]);   // DATE
        $this->assertSame('barangay', $map['fields'][6]);            // SITIO/BARANGAY
        $this->assertSame('age', $map['fields'][8]);
        $this->assertSame('sex', $map['fields'][9]);
        $this->assertSame('icd10_code', $map['fields'][15]);
        $this->assertSame('diagnosis', $map['fields'][16]);
        $this->assertCount(6, $map['fields']);
    }

    public function test_identifying_and_free_text_columns_are_discarded_not_mapped(): void
    {
        $map = IClinicSysMapper::mapHeaders(self::REAL_HEADERS);

        foreach (['NAME', 'DATE OF BIRTH', 'COMPLAINTS', 'TREATMENTS', 'REMARKS', 'PHIC(M/D)', 'PWD(Y/N)'] as $sensitive) {
            $this->assertContains($sensitive, $map['discarded'], "$sensitive must be discarded");
        }
        $this->assertNotContains('SEX', $map['discarded']);
        $this->assertNotContains('AGE', $map['discarded']);
    }

    public function test_date_of_birth_is_not_mistaken_for_the_consultation_date(): void
    {
        $map = IClinicSysMapper::mapHeaders(['DATE OF BIRTH', 'DATE', 'SEX', 'AGE', 'BARANGAY', 'ICD10 CODE']);
        $this->assertSame('consultation_date', $map['fields'][1]);
        $this->assertArrayNotHasKey(0, $map['fields']);
    }

    public function test_original_six_column_template_still_works(): void
    {
        $map = IClinicSysMapper::mapHeaders(['Disease', 'ICD10 Code', 'Barangay', 'Consultation Date', 'Age', 'Sex']);
        $this->assertSame([], $map['missing']);
        $this->assertSame([], $map['discarded']);
    }

    public function test_missing_required_columns_are_reported(): void
    {
        $map = IClinicSysMapper::mapHeaders(['DATE', 'AGE', 'ICD10 CODE']);
        $this->assertSame(['barangay', 'sex'], $map['missing']);

        $noDx = IClinicSysMapper::mapHeaders(['DATE', 'AGE', 'SEX', 'BARANGAY']);
        $this->assertSame(['icd10_code'], $noDx['missing']);
    }

    public function test_excel_serial_numbers_become_dates(): void
    {
        $this->assertSame('2026-09-02', IClinicSysMapper::parseDate(46267));
        $this->assertSame('2026-09-02', IClinicSysMapper::parseDate(46267.0));
        $this->assertSame('2026-09-02', IClinicSysMapper::parseDate('46267'));
        $this->assertSame('2026-09-02', IClinicSysMapper::parseDate(46267.75)); // time-of-day fraction ignored
        $this->assertSame('2024-01-01', IClinicSysMapper::parseDate(45292));
    }

    public function test_date_strings_and_objects_and_garbage(): void
    {
        $this->assertSame('2026-09-02', IClinicSysMapper::parseDate('2026-09-02'));
        $this->assertSame('2026-09-02', IClinicSysMapper::parseDate('09/02/2026'));
        $this->assertSame('2026-09-02', IClinicSysMapper::parseDate(new \DateTime('2026-09-02 13:00')));
        $this->assertNull(IClinicSysMapper::parseDate('not a date'));
        $this->assertNull(IClinicSysMapper::parseDate(''));
        $this->assertNull(IClinicSysMapper::parseDate(null));
        $this->assertNull(IClinicSysMapper::parseDate(7));       // plain small number is not a date
    }

    public function test_barangay_names_match_regardless_of_case_suffix_and_enye(): void
    {
        $official = NameNormalizer::barangay('Santo Niño');
        $this->assertSame($official, NameNormalizer::barangay('SANTO NIÑO'));
        $this->assertSame($official, NameNormalizer::barangay("  santo   niño "));
        // "n" + combining tilde (how some Excel exports encode it)
        $this->assertSame($official, NameNormalizer::barangay("SANTO NI\u{006E}\u{0303}O"));

        $this->assertSame(NameNormalizer::barangay('Ibaba West'), NameNormalizer::barangay('IBABA WEST (POB.)'));
        $this->assertSame(NameNormalizer::barangay('Santa Rita'), NameNormalizer::barangay('SANTA RITA (BUNGAHAN)'));
        $this->assertSame(NameNormalizer::barangay('Santa Cruz'), NameNormalizer::barangay('STA. CRUZ'));
        $this->assertSame('NAG-IBA I', NameNormalizer::barangay('Nag-iba I'));
    }

    public function test_non_calapan_values_do_not_collapse_into_official_names(): void
    {
        $this->assertNotSame(NameNormalizer::barangay('Navotas'), NameNormalizer::barangay('GEN. T. DE LEON'));
        // "I" vs "II" must stay different
        $this->assertNotSame(NameNormalizer::barangay('Bayanan I'), NameNormalizer::barangay('BAYANAN II'));
        $this->assertNotSame(NameNormalizer::barangay('Canubing I'), NameNormalizer::barangay('CANUBING II'));
        // "(BARANGAY I)" is dropped, so this is "POBLACION I" - not an official Calapan barangay
        $this->assertSame('POBLACION I', NameNormalizer::barangay('POBLACION I (BARANGAY I)'));
    }
}
