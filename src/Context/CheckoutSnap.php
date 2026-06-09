<?php

namespace Winpay\Core\Context;

use Winpay\Core\Models\WinpayResponse;
use Winpay\Core\Services\CheckoutService;

class CheckoutSnap
{
    public function __construct(
        private readonly CheckoutService $service,
    ) {
    }

    public function create(array $payload): WinpayResponse
    {
        return $this->service->create($payload);
    }

    public function find(string $id): WinpayResponse
    {
        return $this->service->find($id);
    }

    public function findByRef(string $merchantRef): WinpayResponse
    {
        return $this->service->findByRef($merchantRef);
    }

    public function update(string $id, array $payload): WinpayResponse
    {
        return $this->service->update($id, $payload);
    }

    public function delete(string $id): WinpayResponse
    {
        return $this->service->delete($id);
    }

    public function deleteByRef(string $merchantRef): WinpayResponse
    {
        return $this->service->deleteByRef($merchantRef);
    }
}
