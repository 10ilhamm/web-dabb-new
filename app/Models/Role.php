<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    /** @use HasFactory<\Database\Factories\RoleFactory> */
    use HasFactory;

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

    /**
     * Get all users with this role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role', 'name');
    }

    /**
     * Get role label map for dropdowns.
     */
    public static function roleLabels(): array
    {
        return static::orderBy('id')->pluck('label', 'name')->toArray();
    }

    /**
     * Get table name for profile data of this role.
     */
    public function profileTable(): ?string
    {
        return $this->table_name;
    }

    /**
     * Check if this role can be deleted.
     */
    public function canBeDeleted(): bool
    {
        return ! $this->is_system;
    }
}

