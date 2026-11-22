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
        // 1. Indeks komposit pada tabel penjualan
        Schema::table('penjualan', function (Blueprint $table) {
            $table->index(['kios_id', 'status'], 'penjualan_kios_id_status_index');
            $table->index(['kios_id', 'user_id'], 'penjualan_kios_id_user_id_index');
        });

        // 2. Indeks komposit pada tabel mutasi_stok
        Schema::table('mutasi_stok', function (Blueprint $table) {
            $table->index(['kios_id', 'barang_id', 'created_at'], 'mutasi_stok_kios_barang_created_index');
        });

        // 3. Indeks komposit pada tabel barang_masuk
        Schema::table('barang_masuk', function (Blueprint $table) {
            $table->index(['kios_id', 'status'], 'barang_masuk_kios_id_status_index');
            $table->index(['kios_id', 'tanggal'], 'barang_masuk_kios_id_tanggal_index');
        });

        // 4. Indeks komposit pada tabel penyesuaian_stok
        Schema::table('penyesuaian_stok', function (Blueprint $table) {
            $table->index(['kios_id', 'mode'], 'penyesuaian_stok_kios_id_mode_index');
            $table->index(['kios_id', 'tanggal'], 'penyesuaian_stok_kios_id_tanggal_index');
        });

        // 5. Indeks komposit pada tabel shift_kasir
        Schema::table('shift_kasir', function (Blueprint $table) {
            $table->index(['kios_id', 'user_id'], 'shift_kasir_kios_id_user_id_index');
        });

        // 6. Indeks komposit pada tabel log_aktivitas
        Schema::table('log_aktivitas', function (Blueprint $table) {
            $table->index(['kios_id', 'created_at'], 'log_aktivitas_kios_id_created_at_index');
            $table->index(['kios_id', 'aksi'], 'log_aktivitas_kios_id_aksi_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_aktivitas', function (Blueprint $table) {
            $table->dropIndex('log_aktivitas_kios_id_created_at_index');
            $table->dropIndex('log_aktivitas_kios_id_aksi_index');
        });

        Schema::table('shift_kasir', function (Blueprint $table) {
            $table->dropIndex('shift_kasir_kios_id_user_id_index');
        });

        Schema::table('penyesuaian_stok', function (Blueprint $table) {
            $table->dropIndex('penyesuaian_stok_kios_id_mode_index');
            $table->dropIndex('penyesuaian_stok_kios_id_tanggal_index');
        });

        Schema::table('barang_masuk', function (Blueprint $table) {
            $table->dropIndex('barang_masuk_kios_id_status_index');
            $table->dropIndex('barang_masuk_kios_id_tanggal_index');
        });

        Schema::table('mutasi_stok', function (Blueprint $table) {
            $table->dropIndex('mutasi_stok_kios_barang_created_index');
        });

        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropIndex('penjualan_kios_id_status_index');
            $table->dropIndex('penjualan_kios_id_user_id_index');
        });
    }
};
