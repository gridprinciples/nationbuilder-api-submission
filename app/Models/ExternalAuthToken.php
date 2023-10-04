<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalAuthToken extends Model
{
    protected $fillable = [
        'service', 
        'token', 
        'data',
        'expires_at',
    ];

    protected $casts = [
        'token' => 'encrypted',
        'data' => 'encrypted:collection',
        'expires_at' => 'timestamp',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function scopeNonExpired(Builder $query)
    {
        $query->where(function (Builder $query) {
            $query->where('expires_at', '>', now())
                ->orWhereNull('expires_at');
        });
    }
}
