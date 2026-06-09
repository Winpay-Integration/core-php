<?php

namespace Winpay\Core\Services;

use Winpay\Core\Contracts\HttpClientInterface;
use Winpay\Core\Models\WinpayResponse;

class QrisService
{
    private const SERVICE_CODE_GENERATE = '47';
    private const SERVICE_CODE_QUERY = '51';
    private const SERVICE_CODE_CANCEL = '77';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    public function generate(array $payload): WinpayResponse
    {
        return $this->httpClient->request('POST', '/v1.0/qr/qr-mpm-generate', $payload);
    }

    public function query(array $payload): WinpayResponse
    {
        return $this->httpClient->request('POST', '/v1.0/qr/qr-mpm-query', $payload);
    }

    public function cancel(array $payload): WinpayResponse
    {
        return $this->httpClient->request('POST', '/v1.0/qr/qr-mpm-cancel', $payload);
    }
}
