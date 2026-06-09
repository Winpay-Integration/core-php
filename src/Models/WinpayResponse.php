<?php

namespace Winpay\Core\Models;

class WinpayResponse
{
    public function __construct(
        public readonly ?string $responseCode,
        public readonly ?string $responseMessage,
        public readonly int $httpStatusCode,
        public readonly array $data = [],
    ) {
    }

    public function isSuccess(): bool
    {
        return $this->httpStatusCode >= 200 && $this->httpStatusCode < 300;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
}
