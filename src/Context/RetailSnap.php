<?php

namespace Winpay\Core\Context;

use Winpay\Core\Models\WinpayResponse;
use Winpay\Core\Services\RetailService;

class RetailSnap
{
    public function __construct(
        private readonly RetailService $service,
    ) {
    }

    public function create(array $payload): WinpayResponse
    {
        return $this->service->create($payload);
    }

    public function status(array $payload): WinpayResponse
    {
        return $this->service->status($payload);
    }

    public function cancel(array $payload): WinpayResponse
    {
        return $this->service->cancel($payload);
    }
}
