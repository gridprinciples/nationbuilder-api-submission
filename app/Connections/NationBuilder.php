<?php

namespace App\Connections;

use App\Models\ExternalAuthToken;
use Illuminate\Support\Facades\Auth;

class NationBuilder
{
    // A Guzzle client for the OAuth flow
    protected $oauth;

    // A Guzzle client for API requests
    protected $api;

    public function __construct()
    {
        // Make sure the configuration is set.

        if (!config('services.nationbuilder.key')) {
            throw new \Exception('Cannot use NationBuilder without a client ID.  Please set NATIONBUILDER_CLIENT_ID in the environment configuration.');
        }

        if (!config('services.nationbuilder.secret')) {
            throw new \Exception('Cannot use NationBuilder without a client secret.  Please set NATIONBUILDER_CLIENT_SECRET in the environment configuration.');
        }

        if (!config('services.nationbuilder.slug')) {
            throw new \Exception('Cannot use NationBuilder without a nation slug.  Please set NATIONBUILDER_CLIENT_SLUG in the environment configuration.');
        }

        $this->oauth = new \GuzzleHttp\Client([
            'base_uri' => $this->makeAuthUrl(),
        ]);

        $this->api = new \GuzzleHttp\Client([
            'base_uri' => $this->makeApiUrl(),
        ]);
    }

    public function get(string $url, ?array $params): ?array
    {
        $authToken = $this->getCurrentToken();

        if(! $authToken) {
            throw new \Illuminate\Auth\AuthenticationException('Unauthenticated.');
        }

        $response = $this->api->get($url, [
            'access_token' => $authToken->token,
            'query' => $params,
        ]);

        return json_decode($response->getBody(), true);
    }

    public function post(string $url, ?array $params): ?array
    {
        $authToken = $this->getCurrentToken();

        if(! $authToken) {
            throw new \Illuminate\Auth\AuthenticationException('Unauthenticated.');
        }

        $response = $this->api->post($url, [
            'access_token' => $authToken->token,
            'query' => $params,
        ]);

        return json_decode($response->getBody(), true);
    }

    public function put(string $url, ?array $params): ?array
    {
        $authToken = $this->getCurrentToken();

        if(! $authToken) {
            throw new \Illuminate\Auth\AuthenticationException('Unauthenticated.');
        }

        $response = $this->api->put($url, [
            'access_token' => $authToken->token,
            'query' => $params,
        ]);

        return json_decode($response->getBody(), true);
    }

    public function delete(string $url, ?array $params): ?array
    {
        $authToken = $this->getCurrentToken();

        if(! $authToken) {
            throw new \Illuminate\Auth\AuthenticationException('Unauthenticated.');
        }

        $response = $this->api->delete($url, [
            'access_token' => $authToken->token,
            'query' => $params,
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getOauthLoginUrl($params = []): string
    {
        $defaultParams = [
            'response_type' => 'code',
            'client_id' => config('services.nationbuilder.key'),
            'redirect_uri' => route('nationbuilder.oauth_callback'),
        ];

        return $this->makeAuthUrl() . '?' . http_build_query(array_merge($defaultParams, $params));
    }

    public function exchangeCodeForToken(string $code): string
    {
        $response = $this->oauth->post('token', [
            'query' => [
                'code' => $code,
                'grant_type' => 'authorization_code',
                'client_id' => config('services.nationbuilder.key'),
                'client_secret' => config('services.nationbuilder.secret'),
                'redirect_uri' => route('nationbuilder.oauth_callback'),
            ],
        ]);

        return data_get(json_decode($response->getBody(), true), 'access_token');
    }

    public function loggedIn(): bool
    {
        return (bool) $this->getCurrentToken();
    }

    protected function getCurrentToken(): ?ExternalAuthToken
    {
        return Auth::user()->getExternalAuthToken('nationbuilder');
    }

    protected function makeBaseUrl(): string
    {
        return 'https://' . config('services.nationbuilder.slug') . '.nationbuilder.test';
    }

    protected function makeAuthUrl(): string
    {
        return trim($this->makeBaseUrl() . '/oauth/', '/');
    }

    protected function makeApiUrl(): string
    {
        return trim($this->makeBaseUrl() . '/api/v1/', '/');
    }
}