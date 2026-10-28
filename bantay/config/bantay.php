<?php

return [
    // Full path to pg_dump / pg_restore. On Windows these usually aren't on
    // PATH - set the full path in .env, e.g.
    //   PG_DUMP_PATH="C:\Program Files\PostgreSQL\18\bin\pg_dump.exe"
    //   PG_RESTORE_PATH="C:\Program Files\PostgreSQL\18\bin\pg_restore.exe"
    'pg_dump_path' => env('PG_DUMP_PATH', 'pg_dump'),
    'pg_restore_path' => env('PG_RESTORE_PATH', 'pg_restore'),

    // Keep the original uploaded workbook after it has been processed?
    // Default false: iClinicSys exports can contain patient names, dates of
    // birth and free-text notes. Only the whitelisted fields are copied into
    // the database, so the file is deleted once the import finishes (the
    // SHA-256 hash is kept for duplicate-file detection).
    'keep_uploaded_files' => env('BANTAY_KEEP_UPLOADED_FILES', false),

    'import' => [
        // ICD-10 codes starting with these letters are not diseases and are
        // skipped on import. "Z" = factors influencing health status and
        // contact with health services (medical certificates, general
        // examinations, follow-ups...). Add more letters here if the City
        // Health Office decides other chapters are out of scope too.
        'skip_code_prefixes' => ['Z'],
    ],
];
