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
        Schema::create('produks', function (Blueprint $table) {
            $table->string('produk_id', 255)->primary();
            $table->string('merchant_id', 255);
            $table->string('kode_produk', 100)->unique();
            $table->string('nama', 100);
            $table->decimal('harga', 10, 2);
            $table->integer('stok');
            $table->string('gambar', 255)->nullable();
            $table->timestamps();

            $table->foreign('merchant_id')
                ->references('merchant_id')
                ->on('merchants')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produks');
    }
};
