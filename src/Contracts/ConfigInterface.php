<?php

namespace Winpay\Core\Contracts;

interface ConfigInterface
{
    public function getPartnerId(): string;

    public function getMerchantPrivateKey(): string;

    public function getChannelId(): string;

    public function getBaseUrl(): string;

    public function getWinpayPublicKey(): ?string;

    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value): void;

    public function all(): array;
}
