<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\VirtualRoom;

class VirtualHotspot extends Model
{
    protected $fillable = [
        'virtual_room_id',
        'target_room_id',
        'yaw',
        'pitch',
        'text_tooltip',
    ];

    public function room()
    {
        return $this->belongsTo(VirtualRoom::class, 'virtual_room_id');
    }

    public function targetRoom()
    {
        return $this->belongsTo(VirtualRoom::class, 'target_room_id');
    }
}
