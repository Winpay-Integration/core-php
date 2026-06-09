<?php

namespace Winpay\Core\Context;

use Winpay\Core\Models\WinpayResponse;
use Winpay\Core\Services\ReportService;

class ReportSnap
{
    public function __construct(
        private readonly ReportService $service,
    ) {
    }

    public function balance(array $payload): WinpayResponse
    {
        return $this->service->balance($payload);
    }

    public function history(array $payload): WinpayResponse
    {
        return $this->service->transactionHistory($payload);
    }

    public function statement(array $payload): WinpayResponse
    {
        return $this->service->bankStatement($payload);
    }
}
