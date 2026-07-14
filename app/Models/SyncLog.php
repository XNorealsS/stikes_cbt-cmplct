<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncLog extends Model
{
    protected $fillable = [
        'sync_type',
        'triggered_by',
        'triggered_by_user_id',
        'started_at',
        'finished_at',
        'status',
        'total_fetched',
        'total_inserted',
        'total_updated',
        'total_deactivated',
        'total_errors',
        'error_log',
        'notes',
        'duration_seconds',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by_user_id');
    }
}
