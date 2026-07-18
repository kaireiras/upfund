<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('amount');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->timestamp('date')->useCurrent();
            $table->enum('status', ['pending', 'processing', 'paid', 'failed'])->default('pending');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
