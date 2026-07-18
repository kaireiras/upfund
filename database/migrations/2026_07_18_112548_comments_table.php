<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('target_id');
            $table->enum('target_type', ['project', 'post'])->default('post');
            $table->text('comment');
            $table->timestamp('date')->useCurrent();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('project_url', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
