<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Virtual3dRoom extends Model
{
    protected $table = 'virtual3d_rooms';
    
    protected $fillable = [
        'feature_id',
        'name',
        'description',
        'thumbnail_path',
        'wall_color',
        'floor_color',
        'ceiling_color',
        'doors',
        'door_link_type', // Keep for backward compatibility/migration
        'door_wall',      // Keep for backward compatibility/migration
        'door_target',    // Keep for backward compatibility/migration
        'door_label',     // Keep for backward compatibility/migration
    ];

    protected $casts = [
        'doors' => 'array',
    ];

    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }

    public function media()
    {
        return $this->hasMany(Virtual3dMedia::class, 'virtual3d_room_id');
    }
}
