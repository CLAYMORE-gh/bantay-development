<?php

namespace App\Support;

/**
 * Normalizes place/disease names so "SANTO NIÑO", "Santo Niño" and
 * "santo niño " all compare equal.
 *
 * Why this exists: the old matching did strtolower() in PHP and LOWER() in
 * PostgreSQL. PHP 8.1's strtolower() is byte-based and leaves "Ñ" untouched
 * while PostgreSQL lowercases it, so names containing "ñ" never matched.
 * Here BOTH sides go through the same mb_strtoupper() in PHP.
 */
final class NameNormalizer
{
    /**
     * Barangay names. iClinicSys writes values like "IBABA WEST (POB.)" or
     * "SANTA RITA (BUNGAHAN)"; the official PSA name has no suffix, so any
     * parenthetical is dropped before comparing.
     */
    public static function barangay(?string $value): string
    {
        $v = self::nfc((string) $value);

        $v = preg_replace('/\(.*?\)/u', ' ', $v);          // "(POB.)", "(BUNGAHAN)"
        $v = preg_replace('/\bSTA\.?(?=\s)/iu', 'SANTA', $v); // "STA. CRUZ" -> "SANTA CRUZ"
        $v = preg_replace('/\bSTO\.?(?=\s)/iu', 'SANTO', $v); // "STO. NIÑO" -> "SANTO NIÑO"
        $v = mb_strtoupper($v, 'UTF-8');
        $v = preg_replace('/[^\p{L}\p{M}\p{N}\- ]+/u', ' ', $v); // punctuation -> space; keep letters, accents, hyphen (NAG-IBA)
        $v = preg_replace('/\s+/u', ' ', $v);

        return trim($v);
    }

    /** Disease names and other free labels: case/space/accent-composition insensitive. */
    public static function generic(?string $value): string
    {
        $v = mb_strtoupper(self::nfc((string) $value), 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $v));
    }

    private static function nfc(string $v): string
    {
        // Excel can hand back "n" + a separate combining tilde instead of the
        // single "ñ" character. Compose it with intl when available...
        if (class_exists(\Normalizer::class)) {
            $n = \Normalizer::normalize($v, \Normalizer::FORM_C);
            if ($n !== false) {
                $v = $n;
            }
        }

        // ...and always apply this small fallback so the result is identical
        // on machines where the intl extension is not installed.
        return str_replace(["n\u{0303}", "N\u{0303}"], ["\u{00F1}", "\u{00D1}"], $v);
    }
}
