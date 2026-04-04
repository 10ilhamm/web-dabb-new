<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Restructure profiles table from old denormalized schema to new normalized schema.
     * Old schema had separate columns for each page type (tugas_fungsi_*, struktur_*, sdm_*).
     * New schema uses generic columns with 'type' discriminator.
     */
    public function up(): void
    {
        // Skip if already in new format
        if (!Schema::hasColumn('profiles', 'tugas_fungsi_title')) {
            return;
        }

        // Create new table with correct structure
        Schema::create('profiles_new', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feature_id');

            // Main content
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->longText('description')->nullable();
            $table->longText('description_en')->nullable();

            // Profile-specific: page type and layout
            $table->enum('type', ['default', 'sdm_chart', 'struktur_image', 'tugas_fungsi'])->default('default');

            // Subtitle (for SDM chart pages)
            $table->string('subtitle')->nullable();
            $table->string('subtitle_en')->nullable();

            // Link fields (for tugas_fungsi pages)
            $table->string('link_text')->nullable();
            $table->string('link_url')->nullable();

            // Logo (for struktur_image pages)
            $table->string('logo_path')->nullable();

            // Chart data (for sdm_chart pages) - JSON
            $table->json('chart_data')->nullable();

            // Supporting images
            $table->json('images')->nullable();
            $table->json('image_positions')->nullable();

            // Ordering
            $table->integer('order')->default(0);

            // Timestamps
            $table->timestamps();

            // Foreign key
            $table->foreign('feature_id')->references('id')->on('features')->onDelete('cascade');

            // Indexes
            $table->index('feature_id');
            $table->index('order');
        });

        // Migrate data from old structure to new
        $oldProfiles = DB::table('profiles')->get();

        foreach ($oldProfiles as $oldProfile) {
            // Determine type based on which fields have data
            $type = 'default';
            $title = null;
            $title_en = null;
            $description = null;
            $description_en = null;
            $subtitle = null;
            $subtitle_en = null;
            $link_text = null;
            $link_url = null;
            $logo_path = null;
            $chart_data = null;
            $images = null;

            // Check tugas_fungsi fields
            if (!empty($oldProfile->tugas_fungsi_title)) {
                $type = 'tugas_fungsi';
                $title = $oldProfile->tugas_fungsi_title;
                $title_en = $oldProfile->tugas_fungsi_title_en;
                $description = $oldProfile->tugas_fungsi_desc;
                $link_text = $oldProfile->tugas_fungsi_link_text;
                $link_url = $oldProfile->tugas_fungsi_link_url;
                if ($oldProfile->tugas_fungsi_image) {
                    $images = json_encode([$oldProfile->tugas_fungsi_image]);
                }
            }
            // Check struktur fields
            elseif (!empty($oldProfile->struktur_title)) {
                $type = 'struktur_image';
                $title = $oldProfile->struktur_title;
                $title_en = $oldProfile->struktur_title_en;
                $description = $oldProfile->struktur_desc;
                $logo_path = $oldProfile->struktur_logo;
                if ($oldProfile->struktur_image) {
                    $images = json_encode([$oldProfile->struktur_image]);
                }
            }
            // Check sdm fields
            elseif (!empty($oldProfile->sdm_title)) {
                $type = 'sdm_chart';
                $title = $oldProfile->sdm_title;
                $title_en = $oldProfile->sdm_title_en;
                $description = $oldProfile->sdm_desc;
                $subtitle = $oldProfile->sdm_subtitle;
                $subtitle_en = $oldProfile->sdm_subtitle_en;
                if (!empty($oldProfile->sdm_chart_data)) {
                    $chart_data = $oldProfile->sdm_chart_data;
                }
            }

            DB::table('profiles_new')->insert([
                'feature_id' => $oldProfile->feature_id,
                'title' => $title ?? '',
                'title_en' => $title_en,
                'description' => $description,
                'description_en' => $description_en,
                'type' => $type,
                'subtitle' => $subtitle,
                'subtitle_en' => $subtitle_en,
                'link_text' => $link_text,
                'link_url' => $link_url,
                'logo_path' => $logo_path,
                'chart_data' => $chart_data,
                'images' => $images,
                'image_positions' => null,
                'order' => 1,
                'created_at' => $oldProfile->created_at,
                'updated_at' => $oldProfile->updated_at,
            ]);
        }

        // Drop old table and rename new one
        Schema::dropIfExists('profile_sections');  // Must drop before profiles due to FK
        Schema::drop('profiles');
        Schema::rename('profiles_new', 'profiles');

        // Recreate profile_sections table
        Schema::create('profile_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profile_id');

            // Section content
            $table->string('title')->nullable();
            $table->string('title_en')->nullable();
            $table->longText('description')->nullable();
            $table->longText('description_en')->nullable();

            // Section images
            $table->json('images')->nullable();
            $table->json('image_positions')->nullable();

            // Ordering
            $table->integer('order')->default(0);

            // Timestamps
            $table->timestamps();

            // Foreign key
            $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('cascade');

            // Indexes
            $table->index('profile_id');
            $table->index('order');
        });
    }

    public function down(): void
    {
        // Too complex to rollback - manual restore needed if required
    }
};
