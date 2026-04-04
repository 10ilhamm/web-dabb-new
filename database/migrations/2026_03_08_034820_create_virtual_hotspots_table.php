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
        Schema::create('virtual_hotspots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('virtual_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('target_room_id')->nullable()->constrained('virtual_rooms')->nullOnDelete();
            $table->float('yaw');
            $table->float('pitch');
            $table->string('text_tooltip');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('virtual_hotspots');
    }
};
