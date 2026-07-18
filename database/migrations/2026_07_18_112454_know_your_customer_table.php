<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('know_your_customer', function (Blueprint $table) {
            $table->id();
            $table->string('company_document_url', 1024)->nullable();
            $table->string('bank_document_url', 1024)->nullable();
            $table->string('address', 1024)->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('know_your_customer');
    }
};
