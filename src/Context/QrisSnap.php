<?php

namespace Winpay\Core\Context;

use Winpay\Core\Models\WinpayResponse;
use Winpay\Core\Services\QrisService;

class QrisSnap
{
    public function __construct(
        private readonly QrisService $service,
    ) {
    }

    public function create(array $payload): WinpayResponse
    {
        return $this->service->generate($payload);
    }

    public function status(array $payload): WinpayResponse
    {
        return $this->service->query($payload);
    }

    public function cancel(array $payload): WinpayResponse
    {
        return $this->service->cancel($payload);
    }
}
