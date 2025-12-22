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
        Schema::create('merchants', function (Blueprint $table) {
            $table->string('merchant_id', 255)->primary();
            $table->string('kode_merchant', 100)->unique()->nullable();
            $table->string('nama', 255);
            $table->string('email', 100);
            $table->text('alamat')->nullable();
            $table->string('no_hp', 16);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchants');
    }
};
