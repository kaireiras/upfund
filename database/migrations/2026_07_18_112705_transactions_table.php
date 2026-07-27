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
            $table->string('order_id')->unique(); // ID unik transaksi untuk Midtrans (misal: TRX-10023)
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->integer('amount'); // Total nominal investasi (misal 22.000.000)
            $table->string('snap_token')->nullable(); // Menampung token dari Midtrans Snap
            $table->string('payment_type')->nullable(); // Menampung jenis pembayaran (qris, bank_transfer, gopay)
            $table->enum('status', ['pending', 'processing', 'paid', 'failed', 'expired'])->default('pending');
            $table->timestamp('date')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
