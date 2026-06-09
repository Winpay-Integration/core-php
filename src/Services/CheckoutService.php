<?php

namespace Winpay\Core\Services;

use Winpay\Core\Contracts\HttpClientInterface;
use Winpay\Core\Models\WinpayResponse;

class CheckoutService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    public function create(array $payload): WinpayResponse
    {
        return $this->httpClient->request('POST', '/api/create', $payload);
    }

    public function find(string $id): WinpayResponse
    {
        return $this->httpClient->request('GET', "/api/find/{$id}");
    }

    public function findByRef(string $merchantRef): WinpayResponse
    {
        return $this->httpClient->request('GET', "/api/findByRef/{$merchantRef}");
    }

    public function update(string $id, array $payload): WinpayResponse
    {
        return $this->httpClient->request('PUT', "/api/update/{$id}", $payload);
    }

    public function delete(string $id): WinpayResponse
    {
        return $this->httpClient->request('DELETE', "/api/delete/{$id}");
    }

    public function deleteByRef(string $merchantRef): WinpayResponse
    {
        return $this->httpClient->request('DELETE', "/api/deleteByRef/{$merchantRef}");
    }
}
