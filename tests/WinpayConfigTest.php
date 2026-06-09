<?php

namespace Winpay\Core\Tests;

use PHPUnit\Framework\TestCase;
use Winpay\Core\Config\WinpayConfig;
use Winpay\Core\Exceptions\WinpayException;

class WinpayConfigTest extends TestCase
{
    private string $privateKeyPem = '';

    protected function setUp(): void
    {
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($key, $this->privateKeyPem);
    }

    public function testDefaults(): void
    {
        $config = new WinpayConfig([
            'partner_id' => 'test123',
            'merchant_private_key' => $this->privateKeyPem,
        ]);

        $this->assertSame('test123', $config->getPartnerId());
        $this->assertSame($this->privateKeyPem, $config->getMerchantPrivateKey());
        $this->assertSame('WEB', $config->getChannelId());
        $this->assertSame('https://sandbox-snap.winpay.id', $config->getBaseUrl());
        $this->assertNull($config->getWinpayPublicKey());
    }

    public function testCustomValues(): void
    {
        $config = new WinpayConfig([
            'partner_id' => 'partner-001',
            'merchant_private_key' => $this->privateKeyPem,
            'channel_id' => 'MOBILE',
            'base_url' => 'https://api.winpay.id',
            'winpay_public_key' => $this->privateKeyPem,
            'timeout' => 60,
        ]);

        $this->assertSame('partner-001', $config->getPartnerId());
        $this->assertSame('MOBILE', $config->getChannelId());
        $this->assertSame('https://api.winpay.id', $config->getBaseUrl());
        $this->assertSame($this->privateKeyPem, $config->getWinpayPublicKey());
        $this->assertSame(60, $config->get('timeout'));
    }

    public function testMissingMerchantPrivateKeyThrowsException(): void
    {
        $this->expectException(WinpayException::class);
        $this->expectExceptionMessage('merchant_private_key is not configured');

        $config = new WinpayConfig(['partner_id' => 'test']);
        $config->getMerchantPrivateKey();
    }

    public function testEmptyMerchantPrivateKeyThrowsException(): void
    {
        $this->expectException(WinpayException::class);

        $config = new WinpayConfig([
            'partner_id' => 'test',
            'merchant_private_key' => '',
        ]);
        $config->getMerchantPrivateKey();
    }

    public function testKeyResolutionFromFilePath(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'winpay_test_key_');
        file_put_contents($tempFile, $this->privateKeyPem);

        $config = new WinpayConfig([
            'partner_id' => 'test',
            'merchant_private_key' => $tempFile,
        ]);

        $this->assertSame($this->privateKeyPem, $config->getMerchantPrivateKey());

        unlink($tempFile);
    }

    public function testKeyResolutionNonexistentPathReturnsRaw(): void
    {
        $config = new WinpayConfig([
            'partner_id' => 'test',
            'merchant_private_key' => '/nonexistent/path/key.pem',
        ]);

        $this->assertSame('/nonexistent/path/key.pem', $config->getMerchantPrivateKey());
    }

    public function testGetAndSet(): void
    {
        $config = new WinpayConfig([
            'partner_id' => 'test',
            'merchant_private_key' => $this->privateKeyPem,
        ]);

        $this->assertSame(30, $config->get('timeout'));
        $this->assertSame('default', $config->get('nonexistent', 'default'));
        $this->assertNull($config->get('nonexistent'));

        $config->set('custom_key', 'custom_value');
        $this->assertSame('custom_value', $config->get('custom_key'));
    }

    public function testAll(): void
    {
        $config = new WinpayConfig([
            'partner_id' => 'test',
            'merchant_private_key' => $this->privateKeyPem,
        ]);

        $all = $config->all();
        $this->assertArrayHasKey('partner_id', $all);
        $this->assertArrayHasKey('base_url', $all);
        $this->assertArrayHasKey('channel_id', $all);
    }
}
