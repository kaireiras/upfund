<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interaction', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('target_id');
            $table->enum('target_type', ['project', 'post'])->default('post');
            $table->boolean('like')->default(false);
            $table->integer('share')->default(0);
            $table->boolean('not_interested')->default(false);
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('date')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interaction');
    }
};
