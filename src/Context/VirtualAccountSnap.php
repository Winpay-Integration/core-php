<?php

namespace Winpay\Core\Context;

use Winpay\Core\Models\WinpayResponse;
use Winpay\Core\Services\VirtualAccountService;

class VirtualAccountSnap
{
    public function __construct(
        private readonly VirtualAccountService $service,
    ) {
    }

    public function create(array $payload): WinpayResponse
    {
        return $this->service->create($payload);
    }

    public function inquiry(array $payload): WinpayResponse
    {
        return $this->service->inquiry($payload);
    }

    public function status(array $payload): WinpayResponse
    {
        return $this->service->status($payload);
    }

    public function delete(array $payload): WinpayResponse
    {
        return $this->service->delete($payload);
    }
}
