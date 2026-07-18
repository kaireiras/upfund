<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('image_url', 1024)->nullable();
            $table->text('description')->nullable();
            $table->timestamp('date')->useCurrent();
            $table->string('project_url', 50)->nullable();
            // Catatan: Tipe string tidak di-constraint secara native dengan id() (big integer) di database level.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
