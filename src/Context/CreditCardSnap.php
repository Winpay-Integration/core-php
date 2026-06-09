<?php

namespace Winpay\Core\Context;

use Winpay\Core\Models\WinpayResponse;
use Winpay\Core\Services\CreditCardService;

class CreditCardSnap
{
    public function __construct(
        private readonly CreditCardService $service,
    ) {
    }

    public function create(array $payload): WinpayResponse
    {
        return $this->service->createPayment($payload);
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
