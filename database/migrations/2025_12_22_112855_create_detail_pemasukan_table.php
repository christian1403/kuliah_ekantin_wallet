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
        Schema::create('detail_pemasukan', function (Blueprint $table) {
            $table->string('metode_id', 255);
            $table->string('transaction_id', 255);
            $table->timestamps();

            $table->foreign('metode_id')
                ->references('metode_id')
                ->on('metode_bayar')
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
        Schema::dropIfExists('detail_pemasukan');
    }
};
