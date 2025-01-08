<?php

namespace App\Controller\Auth\Services;

class AzureOAuthService
{
    private string $azureClientId;
    private string $azureClientSecret;
    private string $azureRedirectUri;

    public function __construct(string $azureClientId, string $azureClientSecret, string $azureRedirectUri)
    {
        $this->azureClientId = $azureClientId;
        $this->azureClientSecret = $azureClientSecret;
        $this->azureRedirectUri = $azureRedirectUri;
    }

    public function getClientId(): string
    {
        return $this->azureClientId;
    }

    public function getClientSecret(): string
    {
        return $this->azureClientSecret;
    }

    public function getRedirectUri(): string
    {
        return $this->azureRedirectUri;
    }
}
