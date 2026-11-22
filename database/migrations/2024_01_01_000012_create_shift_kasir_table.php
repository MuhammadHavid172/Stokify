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
        Schema::create('shift_kasir', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kios_id')->constrained('kios')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->dateTime('waktu_buka');
            $table->unsignedBigInteger('modal_awal');
            $table->enum('status', ['berjalan', 'ditutup'])->default('berjalan');
            $table->unsignedBigInteger('user_aktif')->nullable()->unique();
            $table->dateTime('waktu_tutup')->nullable();
            $table->unsignedBigInteger('uang_fisik')->nullable();
            $table->bigInteger('uang_seharusnya')->nullable();
            $table->bigInteger('selisih')->nullable();
            $table->unsignedInteger('jumlah_transaksi')->nullable();
            $table->unsignedBigInteger('total_penjualan_tunai')->nullable();
            $table->unsignedInteger('jumlah_nota_batal')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('ditutup_oleh')->nullable()->constrained('users');
            $table->boolean('ditutup_paksa')->default(false);
            $table->timestamps();

            $table->index(['kios_id', 'status']);
            $table->index(['kios_id', 'waktu_buka']);
        });

        Schema::table('penjualan', function (Blueprint $table) {
            $table->foreign('shift_id')->references('id')->on('shift_kasir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
        });

        Schema::dropIfExists('shift_kasir');
    }
};
