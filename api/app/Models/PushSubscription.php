<?php

namespace App\Models;

use NotificationChannels\WebPush\PushSubscription as BasePushSubscription;

class PushSubscription extends BasePushSubscription
{
    protected $fillable = [
        'user_id',
        'endpoint',
        'endpoint_hash',
        'public_key',
        'auth_token',
        'content_encoding'
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            if ($model->endpoint) {
                $model->endpoint_hash = hash('sha256', $model->endpoint);
            }
        });
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}