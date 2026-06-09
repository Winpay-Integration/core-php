<?php

namespace Winpay\Core\Services;

use Winpay\Core\Contracts\HttpClientInterface;
use Winpay\Core\Models\WinpayResponse;

class RetailService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    public function create(array $payload): WinpayResponse
    {
        return $this->httpClient->request('POST', '/v1.0/retail/payment', $payload);
    }

    public function status(array $payload): WinpayResponse
    {
        return $this->httpClient->request('POST', '/v1.0/retail/status', $payload);
    }

    public function cancel(array $payload): WinpayResponse
    {
        return $this->httpClient->request('POST', '/v1.0/retail/cancel', $payload);
    }
}
