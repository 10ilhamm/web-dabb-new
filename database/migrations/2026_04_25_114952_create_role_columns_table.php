<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->string('column_name', 100);           // DB column name
            $table->string('column_type', 50);            // string, text, integer, date, enum, boolean, file
            $table->string('column_label', 100);          // Display label
            $table->integer('column_length')->nullable(); // For string/integer length
            $table->boolean('is_nullable')->default(true);
            $table->boolean('is_unique')->default(false);
            $table->string('default_value', 255)->nullable();
            $table->text('options')->nullable();          // JSON for enum/select options
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_columns');
    }
};
