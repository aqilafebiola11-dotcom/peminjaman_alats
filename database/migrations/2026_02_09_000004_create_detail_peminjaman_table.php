<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('detail_peminjaman', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_peminjaman');
            $table->foreign('id_peminjaman')->references('id_peminjaman')->on('peminjaman')->cascadeOnDelete();
            $table->unsignedBigInteger('id_alat');
            $table->foreign('id_alat')->references('id_alat')->on('alat')->cascadeOnDelete();
            $table->integer('jumlah_pinjam');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_peminjaman');
    }
};
