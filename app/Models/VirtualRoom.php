<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Feature;
use App\Models\VirtualHotspot;

class VirtualRoom extends Model
{
    protected $fillable = [
        'feature_id',
        'name',
        'description',
        'thumbnail_path',
        'image_360_path',
    ];

    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }

    public function hotspots()
    {
        return $this->hasMany(VirtualHotspot::class, 'virtual_room_id');
    }
}
