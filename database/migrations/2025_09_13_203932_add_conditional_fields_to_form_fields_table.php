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
        Schema::table('form_fields', function (Blueprint $table) {
            // ID dari field yang menjadi pemicu
            $table->foreignId('conditional_on_field_id')->nullable()->constrained('form_fields')->onDelete('set null');
            // Nilai yang harus dipilih di field pemicu agar field ini muncul
            $table->string('conditional_on_value')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_fields', function (Blueprint $table) {
            // Hapus constraint dulu sebelum drop column
            $table->dropForeign(['conditional_on_field_id']);
            $table->dropColumn(['conditional_on_field_id', 'conditional_on_value']);
        });
    }
};
