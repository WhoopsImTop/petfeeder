<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FeederEvent extends Model
{
    use HasFactory;
    protected $fillable = [
        'household_id',
        'detected_at',
        'label',
        'action',
        'confidence',
        'mouth_status',
        'detections',
        'image_path',
        'activity_log_id',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'confidence' => 'float',
        'detections' => 'array',
    ];

    protected $appends = ['image_url'];

    public function household(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function activityLog(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ActivityLog::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->image_path);
    }
}
