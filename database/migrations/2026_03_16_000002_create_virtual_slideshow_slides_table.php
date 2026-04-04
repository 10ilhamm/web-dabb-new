<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('virtual_slideshow_slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_id')->constrained('features')->onDelete('cascade');
            $table->enum('slide_type', ['hero', 'text', 'carousel', 'video', 'text_carousel'])->default('text');
            $table->string('title')->nullable();
            $table->string('title_en')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('subtitle_en')->nullable();
            $table->text('description')->nullable();
            $table->text('description_en')->nullable();
            $table->json('images')->nullable();      // array of storage paths
            $table->string('video_url')->nullable(); // YouTube or direct video URL
            $table->enum('layout', ['left', 'right', 'center'])->default('center');
            $table->string('bg_color')->nullable();  // e.g. #F8FAFC
            $table->json('info_popup')->nullable();  // {"0":"caption img 0","video":"caption video"}
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('virtual_slideshow_slides');
    }
};
