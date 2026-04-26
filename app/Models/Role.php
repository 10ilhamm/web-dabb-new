<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'label',
        'table_name',
        'relation_name',
        'is_system',
        'description',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'role', 'name');
    }

    public function columns()
    {
        return $this->hasMany(RoleColumn::class)->orderBy('sort_order');
    }

    public static function roleLabels(): array
    {
        return self::pluck('label', 'name')->toArray();
    }

    public function canBeDeleted(): bool
    {
        return ! $this->is_system;
    }
}
