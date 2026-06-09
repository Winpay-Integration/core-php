<?php

namespace Winpay\Core\Services;

use Winpay\Core\Contracts\HttpClientInterface;
use Winpay\Core\Models\WinpayResponse;

class CreditCardService
{
    private const SERVICE_CODE_PAYMENT = '54';
    private const SERVICE_CODE_STATUS = '55';
    private const SERVICE_CODE_CANCEL = '57';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    public function createPayment(array $payload): WinpayResponse
    {
        return $this->httpClient->request('POST', '/v1.0/debit/payment-host-to-host', $payload);
    }

    public function status(array $payload): WinpayResponse
    {
        return $this->httpClient->request('POST', '/v1.0/debit/status', $payload);
    }

    public function cancel(array $payload): WinpayResponse
    {
        return $this->httpClient->request('POST', '/v1.0/debit/cancel', $payload);
    }
}
