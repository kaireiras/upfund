<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // username & role sudah ada di migration create_users_table (branch ProjectDetails),
            // jadi hanya tambahkan kolom profil yang benar-benar baru dari branch userprofile.
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'avatar_url')) {
                $table->string('avatar_url', 1024)->nullable()->after('bio');
            }
            if (!Schema::hasColumn('users', 'bank_account_details')) {
                $table->string('bank_account_details')->nullable()->after('avatar_url');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('User')->after('bank_account_details');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'bio', 'avatar_url', 'bank_account_details', 'role']);
        });
    }
};