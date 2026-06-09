<?php

namespace Winpay\Core\Services;

use Winpay\Core\Contracts\HttpClientInterface;
use Winpay\Core\Models\WinpayResponse;

class VirtualAccountService
{
    private const SERVICE_CODE_CREATE = '27';
    private const SERVICE_CODE_INQUIRY = '30';
    private const SERVICE_CODE_STATUS = '26';
    private const SERVICE_CODE_DELETE = '31';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    public function create(array $payload): WinpayResponse
    {
        return $this->httpClient->request('POST', '/v1.0/transfer-va/create-va', $payload);
    }

    public function inquiry(array $payload): WinpayResponse
    {
        return $this->httpClient->request('POST', '/v1.0/transfer-va/inquiry-va', $payload);
    }

    public function status(array $payload): WinpayResponse
    {
        return $this->httpClient->request('POST', '/v1.0/transfer-va/status', $payload);
    }

    public function delete(array $payload): WinpayResponse
    {
        return $this->httpClient->request('DELETE', '/v1.0/transfer-va/delete-va', $payload);
    }
}
