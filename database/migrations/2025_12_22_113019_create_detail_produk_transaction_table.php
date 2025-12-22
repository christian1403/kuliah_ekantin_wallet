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
        Schema::create('detail_produk_transaction', function (Blueprint $table) {
            $table->string('produk_id', 255);
            $table->string('transaction_id', 255);
            $table->integer('qty');
            $table->decimal('harga', 10, 2);
            $table->timestamps();

            $table->foreign('produk_id')
                ->references('produk_id')
                ->on('produks')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('transaction_id')
                ->references('transaction_id')
                ->on('wallet_transactions')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_produk_transaction');
    }
};
