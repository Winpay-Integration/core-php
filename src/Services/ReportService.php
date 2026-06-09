<?php

namespace Winpay\Core\Services;

use Winpay\Core\Contracts\HttpClientInterface;
use Winpay\Core\Models\WinpayResponse;

class ReportService
{
    private const SERVICE_CODE_BALANCE = '11';
    private const SERVICE_CODE_HISTORY = '12';
    private const SERVICE_CODE_STATEMENT = '14';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    public function balance(array $payload): WinpayResponse
    {
        return $this->httpClient->request('POST', '/v1.0/balance-inquiry', $payload);
    }

    public function transactionHistory(array $payload): WinpayResponse
    {
        return $this->httpClient->request('POST', '/v1.0/transaction-history-list', $payload);
    }

    public function bankStatement(array $payload): WinpayResponse
    {
        return $this->httpClient->request('POST', '/v1.0/bank-statement', $payload);
    }
}
