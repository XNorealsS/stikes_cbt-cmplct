<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity',
        'description',
        'ip_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper to log activities static-style
     */
    public static function log(string $activity, ?string $description = null, ?int $userId = null): void
    {
        try {
            self::create([
                'user_id' => $userId ?? auth()->id(),
                'activity' => $activity,
                'description' => $description,
                'ip_address' => request()->ip(),
            ]);
        } catch (\Exception $e) {
            // Silently fail logging in case of database issues
        }
    }
}
