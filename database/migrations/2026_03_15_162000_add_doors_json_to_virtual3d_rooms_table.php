<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('virtual3d_rooms', function (Blueprint $table) {
            $table->json('doors')->nullable()->after('ceiling_color');
        });

        // Migrate existing single-door data into the new JSON column
        $rooms = DB::table('virtual3d_rooms')->get();
        foreach ($rooms as $room) {
            $doorWall = $room->door_wall ?? 'back';
            $doors = [
                'front' => ['link_type' => 'none', 'target' => null, 'label' => null],
                'left'  => ['link_type' => 'none', 'target' => null, 'label' => null],
                'right' => ['link_type' => 'none', 'target' => null, 'label' => null],
                'back'  => ['link_type' => 'none', 'target' => null, 'label' => null],
            ];

            if ($room->door_link_type && $room->door_link_type !== 'none') {
                $doors[$doorWall] = [
                    'link_type' => $room->door_link_type,
                    'target'    => $room->door_target,
                    'label'     => $room->door_label,
                ];
            }

            DB::table('virtual3d_rooms')
                ->where('id', $room->id)
                ->update(['doors' => json_encode($doors)]);
        }
    }

    public function down(): void
    {
        Schema::table('virtual3d_rooms', function (Blueprint $table) {
            $table->dropColumn('doors');
        });
    }
};
