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
        Schema::create('kasir', function (Blueprint $table) {
            $table->string('kasir_id', 255)->primary();
            $table->string('merchant_id', 255);
            $table->string('nama', 100);
            $table->string('email', 100);
            $table->string('no_hp', 16);
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
        Schema::dropIfExists('kasir');
    }
};
