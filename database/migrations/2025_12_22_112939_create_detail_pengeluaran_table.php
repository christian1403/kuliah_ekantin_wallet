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
        Schema::create('detail_pengeluaran', function (Blueprint $table) {
            $table->string('merchant_id', 255);
            $table->string('transaction_id', 255);
            $table->string('kasir_id', 255);
            $table->timestamps();

            $table->foreign('merchant_id')
                ->references('merchant_id')
                ->on('merchants')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('transaction_id')
                ->references('transaction_id')
                ->on('wallet_transactions')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('kasir_id')
                ->references('kasir_id')
                ->on('kasir')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pengeluaran');
    }
};
