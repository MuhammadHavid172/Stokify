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
        Schema::create('penyesuaian_stok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kios_id')->constrained('kios')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('nomor');
            $table->date('tanggal');
            $table->enum('mode', ['manual', 'opname'])->default('manual');
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('total_kerugian')->default(0);
            $table->timestamps();

            $table->unique(['kios_id', 'nomor']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyesuaian_stok');
    }
};
