<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageView extends Model
{
    protected $fillable = ['user_id', 'path', 'ip', 'viewed_date'];

    protected $casts = [
        'viewed_date' => 'date',
    ];

    /**
     * Visitor that this page view belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
