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
        Schema::create('wallet', function (Blueprint $table) {
            $table->string('wallet_id', 255)->primary();
            $table->string('mahasiswa_id', 255);
            $table->decimal('balance', 10, 2);
            $table->decimal('balance_settled', 10, 2);
            $table->text('pin');
            $table->timestamps();

            $table->foreign('mahasiswa_id')
                ->references('mahasiswa_id')
                ->on('mahasiswa')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet');
    }
};
