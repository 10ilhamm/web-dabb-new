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
        Schema::create('virtual3d_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('virtual3d_room_id')->constrained('virtual3d_rooms')->cascadeOnDelete();
            $table->string('wall'); // 'front', 'back', 'left', 'right'
            $table->string('type'); // 'image', 'video'
            $table->string('file_path');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            
            // Positioning variables using percentages (from left and top) and size percentages
            $table->decimal('position_x', 5, 2)->default(50.00); // 0-100%
            $table->decimal('position_y', 5, 2)->default(50.00); // 0-100%
            $table->decimal('width', 5, 2)->default(30.00);      // 0-100%
            $table->decimal('height', 5, 2)->default(40.00);     // 0-100%
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('virtual3d_media');
    }
};
