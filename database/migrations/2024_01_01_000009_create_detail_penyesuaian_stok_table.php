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
        Schema::create('detail_penyesuaian_stok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kios_id')->constrained('kios')->cascadeOnDelete();
            $table->foreignId('penyesuaian_stok_id')->constrained('penyesuaian_stok')->cascadeOnDelete();
            $table->foreignId('barang_id')->constrained('barang');
            $table->enum('jenis', ['rusak', 'hilang', 'kedaluwarsa', 'selisih_opname', 'koreksi_lain']);
            $table->integer('jumlah'); // Bertanda: positif menambah, negatif mengurangi, != 0
            $table->integer('stok_sebelum');
            $table->integer('stok_sesudah');
            $table->unsignedBigInteger('harga_modal'); // Snapshot harga modal saat itu
            $table->bigInteger('nilai'); // Bertanda: jumlah x harga_modal
            $table->string('alasan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_penyesuaian_stok');
    }
};
