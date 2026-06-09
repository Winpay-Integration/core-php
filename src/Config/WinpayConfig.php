<?php

namespace Winpay\Core\Config;

use Winpay\Core\Contracts\ConfigInterface;
use Winpay\Core\Exceptions\WinpayException;

class WinpayConfig implements ConfigInterface
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = array_merge($this->getDefaults(), $config);
    }

    public function getPartnerId(): string
    {
        return $this->config['partner_id'];
    }

    public function getMerchantPrivateKey(): string
    {
        return $this->loadKey('merchant_private_key');
    }

    public function getChannelId(): string
    {
        return $this->config['channel_id'];
    }

    public function getBaseUrl(): string
    {
        return $this->config['base_url'];
    }

    public function getWinpayPublicKey(): ?string
    {
        $value = $this->config['winpay_public_key'] ?? null;
        if ($value === null || $value === '') {
            return null;
        }

        return $this->resolveKey($value);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->config[$key] = $value;
    }

    public function all(): array
    {
        return $this->config;
    }

    private function loadKey(string $key): string
    {
        $value = $this->config[$key] ?? null;
        if ($value === null || $value === '') {
            throw new WinpayException("{$key} is not configured");
        }

        return $this->resolveKey($value);
    }

    private function resolveKey(string $value): string
    {
        if (str_starts_with(trim($value), '-----BEGIN')) {
            return $value;
        }

        if (file_exists($value)) {
            $content = file_get_contents($value);
            if ($content === false) {
                throw new WinpayException("Cannot read key file: {$value}");
            }
            return $content;
        }

        return $value;
    }

    private function getDefaults(): array
    {
        return [
            'base_url' => 'https://sandbox-snap.winpay.id',
            'channel_id' => 'WEB',
            'timeout' => 30,
            'verify_ssl' => true,
        ];
    }
}
