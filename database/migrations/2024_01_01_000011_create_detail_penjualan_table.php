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
        Schema::create('detail_penjualan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kios_id')->constrained('kios')->cascadeOnDelete();
            $table->foreignId('penjualan_id')->constrained('penjualan')->cascadeOnDelete();
            $table->foreignId('barang_id')->constrained('barang');
            $table->string('kode_barang');
            $table->string('nama_barang');
            $table->string('satuan');
            $table->unsignedInteger('jumlah');
            $table->unsignedBigInteger('harga_jual');
            $table->unsignedBigInteger('harga_modal');
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('subtotal_modal');
            $table->timestamps();

            $table->index(['kios_id', 'penjualan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_penjualan');
    }
};
