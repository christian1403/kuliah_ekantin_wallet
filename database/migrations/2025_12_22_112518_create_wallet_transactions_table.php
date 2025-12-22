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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->string('transaction_id', 255)->primary();
            $table->string('wallet_id', 255);
            $table->string('invoice', 100);
            $table->decimal('amount', 10, 2);
            $table->string('tipe_transaksi', 20);
            $table->timestamp('waktu_transaksi')->useCurrent();
            $table->text('deskripsi')->nullable();
            $table->decimal('curr_balance', 10, 2);
            $table->decimal('after_balance', 10, 2);
            $table->json('items')->nullable();
            $table->timestamps();

            $table->foreign('wallet_id')
                ->references('wallet_id')
                ->on('wallet')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
