<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public static function getEnumValues($table, $column)
    {
        $type = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM {$table} WHERE Field = '{$column}'")[0]->Type;
        preg_match('/^enum\((.*)\)$/', $type, $matches);
        $enum = [];
        foreach (explode(',', $matches[1]) as $value) {
            $enum[] = trim($value, "'");
        }
        return $enum;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role',
        'photo',
        'google_id',
        'email_verified_at',
    ];

    /**
     * Get the role model for this user.
     */
    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role', 'name');
    }

    /**
     * Get UI labels for each role (dynamic from roles table).
     */
    public static function roleLabels(): array
    {
        return Role::roleLabels();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /* Relationships */
    public function getProfileAttribute()
    {
        $role = Role::where('name', $this->role)->first();

        if (! $role || ! $role->relation_name) {
            return null;
        }

        $relation = $role->relation_name;

        if (! method_exists($this, $relation)) {
            return null;
        }

        return $this->{$relation};
    }

    public function userUmum()
    {
        return $this->hasOne(UserUmum::class);
    }

    public function userPelajar()
    {
        return $this->hasOne(UserPelajar::class);
    }

    public function userInstansi()
    {
        return $this->hasOne(UserInstansi::class);
    }

    public function userAdmin()
    {
        return $this->hasOne(UserAdmin::class);
    }

    public function userPegawai()
    {
        return $this->hasOne(UserPegawai::class);
    }
}
