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
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->string('mahasiswa_id', 255)->primary();
            $table->string('npm', 20)->unique()->nullable();
            $table->string('email', 100);
            $table->string('nama', 255);
            $table->text('alamat')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->char('jenis_kelamin', 1);
            $table->string('no_hp', 16);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};
