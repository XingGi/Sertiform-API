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
        Schema::create('form_field_options', function (Blueprint $table) {
            $table->id();
            // Menghubungkan setiap pilihan ke field spesifik di tabel form_fields
            $table->foreignId('form_field_id')->constrained()->onDelete('cascade');
            $table->string('label'); // Teks yang akan dilihat oleh user, misal: "Sangat Setuju"
            $table->string('value'); // Nilai yang akan disimpan di database, misal: "sangat_setuju"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_field_options');
    }
};
