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
        Schema::table('kios', function (Blueprint $table) {
            $table->unsignedTinyInteger('lead_time_hari')->default(2)->after('status');
            $table->unsignedTinyInteger('hari_pengaman')->default(3)->after('lead_time_hari');
            $table->unsignedTinyInteger('cakupan_kulakan_hari')->default(7)->after('hari_pengaman');
            $table->unsignedSmallInteger('batas_stok_berlebih_hari')->default(60)->after('cakupan_kulakan_hari');
            $table->unsignedTinyInteger('margin_tipis_persen')->default(10)->after('batas_stok_berlebih_hari');
        });

        Schema::table('detail_penjualan', function (Blueprint $table) {
            $table->index(['kios_id', 'barang_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_penjualan', function (Blueprint $table) {
            $table->dropIndex(['kios_id', 'barang_id']);
        });

        Schema::table('kios', function (Blueprint $table) {
            $table->dropColumn([
                'lead_time_hari',
                'hari_pengaman',
                'cakupan_kulakan_hari',
                'batas_stok_berlebih_hari',
                'margin_tipis_persen',
            ]);
        });
    }
};
