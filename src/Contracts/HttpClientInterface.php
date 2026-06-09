<?php

namespace Winpay\Core\Contracts;

use Winpay\Core\Models\WinpayResponse;

interface HttpClientInterface
{
    public function request(string $method, string $endpoint, ?array $body = null): WinpayResponse;
}
