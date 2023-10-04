<?php

namespace App\Models\Traits;

use App\Models\ExternalAuthToken;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasExternalAuthTokens
{
    /**
     * The tokens owned by this model
     */
    public function external_auth_tokens(): HasMany
    {
        return $this->hasMany(\App\Models\ExternalAuthToken::class);
    }

    /**
     * Retrieve an access token by service.
     *
     * @param string $service
     * @param boolean $allowExpired
     * @return ExternalAuthToken
     */
    public function getExternalAuthToken(string $service, bool $allowExpired = false): ?ExternalAuthToken
    {
        $query = $this->external_auth_tokens();

        if(! $allowExpired) {
            $query->nonExpired();
        }
            
        return $query->where('service', $service)
            ->first();
    }

    /**
     * Set an access token by service.
     *
     * @param string $service
     * @param string $token
     * @param array $data
     * @param Carbon|null $expires_at
     * @return ExternalAuthToken
     */
    public function setExternalAuthToken(string $service, string $token, ?array $data, ?Carbon $expires_at): ExternalAuthToken
    {
        $existingToken = $this->getExternalAuthToken($service, allowExpired: true);

        if(! $existingToken) {
            $tokenRecord = new ExternalAuthToken(compact('service'));
            $tokenRecord->user()->associate($this);
        } else {
            $tokenRecord = $existingToken;
        }

        $tokenRecord->fill(compact('token', 'data', 'expires_at'));

        $tokenRecord->save();

        return $tokenRecord;
    }
}