<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fix bug join varchar-vs-bigint: kolom posts.project_url & comments.project_url
 * menyimpan ID project sebagai string(50), sementara relasi Eloquent menjoinnya
 * ke projects.id (bigint). MySQL meng-cast implisit (dev lokal lolos), tetapi
 * PostgreSQL (Supabase/prod) melarang perbandingan text = bigint sehingga
 * GET /projects/{id} (withCount posts/comments + eager postsPreview) error.
 *
 * Solusi: ubah kedua kolom menjadi unsignedBigInteger nullable agar join menjadi
 * bigint = bigint (portable di kedua vendor).
 *
 * CATATAN: schema-builder ->change() tidak menyisipkan klausa USING yang
 * diwajibkan Postgres saat konversi text -> bigint, jadi konversi dilakukan via
 * DB::statement bercabang per driver.
 *
 * TECH DEBT (ditunda, lihat CLAUDE.md): idealnya kolom ini di-rename menjadi
 * project_id + foreign key constraint ke projects. Ditunda karena menyentuh
 * Model/Resource/Controller/seeder di banyak branch.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        foreach (['posts', 'comments'] as $table) {
            // GUARD (portable): NULL-kan dulu semua nilai yang BUKAN digit murni,
            // supaya konversi ke bigint tidak meledak pada data kotor (mis. URL,
            // teks, desimal "12.0", atau string berspasi " 5 "). Tanpa ini,
            // Postgres melempar 22P02 "invalid input syntax for type bigint" dan
            // MySQL diam-diam mengubahnya jadi 0 — dua-duanya tidak diinginkan.
            // Baris non-numerik memang tautan yang sudah tidak valid.
            if ($driver === 'pgsql') {
                DB::statement("UPDATE {$table} SET project_url = NULL WHERE project_url !~ '^[0-9]+$'");
                // Postgres wajib klausa USING untuk konversi text -> bigint.
                DB::statement("ALTER TABLE {$table} ALTER COLUMN project_url TYPE BIGINT USING NULLIF(project_url, '')::bigint");
            } elseif ($driver === 'mysql' || $driver === 'mariadb') {
                // REGEXP MySQL: '^[0-9]+$' -> hanya digit. NULLIF empty juga ditangani regex.
                DB::statement("UPDATE {$table} SET project_url = NULL WHERE project_url NOT REGEXP '^[0-9]+$'");
                DB::statement("ALTER TABLE {$table} MODIFY project_url BIGINT UNSIGNED NULL");
            } else {
                // Fallback (mis. sqlite untuk test) — schema builder biasa.
                Schema::table($table, function ($t) {
                    $t->unsignedBigInteger('project_url')->nullable()->change();
                });
            }
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        foreach (['posts', 'comments'] as $table) {
            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE {$table} ALTER COLUMN project_url TYPE VARCHAR(50) USING project_url::varchar");
            } elseif ($driver === 'mysql' || $driver === 'mariadb') {
                DB::statement("ALTER TABLE {$table} MODIFY project_url VARCHAR(50) NULL");
            } else {
                Schema::table($table, function ($t) {
                    $t->string('project_url', 50)->nullable()->change();
                });
            }
        }
    }
};
