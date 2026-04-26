<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleColumn extends Model
{
    protected $fillable = [
        'role_id',
        'column_name',
        'column_type',
        'column_label',
        'column_length',
        'is_nullable',
        'is_unique',
        'default_value',
        'options',
        'sort_order',
    ];

    protected $casts = [
        'is_nullable' => 'boolean',
        'is_unique' => 'boolean',
        'options' => 'array',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
