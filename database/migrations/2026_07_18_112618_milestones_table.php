<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('title', 100);
            $table->text('description')->nullable();
            $table->timestamp('from')->useCurrent();
            $table->timestamp('to')->useCurrent();
            $table->integer('budget');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('milestones');
    }
};
