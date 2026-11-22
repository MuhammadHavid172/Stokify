<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('penjualan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kios_id')->constrained('kios')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('nomor');
            $table->dateTime('waktu_transaksi');
            $table->unsignedBigInteger('total');
            $table->unsignedBigInteger('bayar');
            $table->unsignedBigInteger('kembalian');
            $table->unsignedBigInteger('total_modal'); // HPP snapshot = jumlah subtotal_modal
            $table->string('metode_bayar')->default('tunai');
            $table->enum('status', ['selesai', 'dibatalkan'])->default('selesai');
            $table->string('token'); // Idempotency token
            $table->unsignedBigInteger('shift_id')->nullable(); // Disiapkan untuk Tahap 8
            $table->foreignId('dibatalkan_oleh')->nullable()->constrained('users');
            $table->dateTime('dibatalkan_pada')->nullable();
            $table->string('alasan_batal')->nullable();
            $table->timestamps();

            $table->unique(['kios_id', 'nomor']);
            $table->unique(['kios_id', 'token']);
            $table->index(['kios_id', 'waktu_transaksi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan');
    }
};
