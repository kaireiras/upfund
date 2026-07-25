<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->text('bio')->nullable()->after('email');
            $table->string('avatar_url', 1024)->nullable()->after('bio');
            $table->string('bank_account_details')->nullable()->after('avatar_url'); // Contoh: "Bank BCA - 1228499201"
            $table->string('role')->default('User')->after('bank_account_details');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'bio', 'avatar_url', 'bank_account_details', 'role']);
        });
    }
};