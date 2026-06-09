<?php

namespace Winpay\Core\Tests;

use PHPUnit\Framework\TestCase;
use Winpay\Core\Exceptions\WinpayException;
use Winpay\Core\Services\CheckoutService;
use Winpay\Core\Services\CreditCardService;
use Winpay\Core\Services\EwalletService;
use Winpay\Core\Services\QrisService;
use Winpay\Core\Services\ReportService;
use Winpay\Core\Services\RetailService;
use Winpay\Core\Context\SnapContext;
use Winpay\Core\Context\VirtualAccountSnap;
use Winpay\Core\Services\VirtualAccountService;
use Winpay\Core\WinpayClient;

class WinpayClientTest extends TestCase
{
    private string $privateKey = '';
    private string $publicKey = '';

    protected function setUp(): void
    {
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($key, $this->privateKey);
        $details = openssl_pkey_get_details($key);
        $this->publicKey = $details['key'];
    }

    public function testConstructWithArray(): void
    {
        $client = new WinpayClient([
            'partner_id' => 'test',
            'merchant_private_key' => $this->privateKey,
        ]);

        $this->assertInstanceOf(VirtualAccountService::class, $client->va);
        $this->assertInstanceOf(QrisService::class, $client->qris);
        $this->assertInstanceOf(EwalletService::class, $client->ewallet);
        $this->assertInstanceOf(CreditCardService::class, $client->creditCard);
        $this->assertInstanceOf(ReportService::class, $client->report);
        $this->assertInstanceOf(RetailService::class, $client->retail);
        $this->assertInstanceOf(CheckoutService::class, $client->checkout);
    }

    public function testVerifyCallbackSuccess(): void
    {
        $client = new WinpayClient([
            'partner_id' => 'test',
            'merchant_private_key' => $this->privateKey,
            'winpay_public_key' => $this->publicKey,
        ]);

        $body = ['orderId' => 'ORD-001', 'amount' => '10000'];
        $timestamp = '2024-01-01T12:00:00+07:00';

        $signature = base64_encode(
            openssl_sign(
                strtoupper('POST') . ':/callback:' . strtolower(hash('sha256', json_encode($body, JSON_UNESCAPED_SLASHES))) . ':' . $timestamp,
                $sig,
                $this->privateKey,
                OPENSSL_ALGO_SHA256,
            ) ? $sig : ''
        );

        $result = $client->verifyCallback('POST', '/callback', $body, $timestamp, $signature);

        $this->assertTrue($result);
    }

    public function testVerifyCallbackFailsWithWrongSignature(): void
    {
        $client = new WinpayClient([
            'partner_id' => 'test',
            'merchant_private_key' => $this->privateKey,
            'winpay_public_key' => $this->publicKey,
        ]);

        $result = $client->verifyCallback('POST', '/callback', [], 'ts', 'invalid-signature');

        $this->assertFalse($result);
    }

    public function testVerifyCallbackWithoutPublicKey(): void
    {
        $this->expectException(WinpayException::class);
        $this->expectExceptionMessage('winpay_public_key is not configured');

        $client = new WinpayClient([
            'partner_id' => 'test',
            'merchant_private_key' => $this->privateKey,
        ]);

        $client->verifyCallback('POST', '/callback', [], 'ts', 'sig');
    }

    public function testGetConfig(): void
    {
        $client = new WinpayClient([
            'partner_id' => 'test-partner',
            'merchant_private_key' => $this->privateKey,
            'channel_id' => 'MOBILE',
        ]);

        $config = $client->getConfig();

        $this->assertSame('test-partner', $config->getPartnerId());
        $this->assertSame('MOBILE', $config->getChannelId());
    }

    public function testSnapReturnsSnapContext(): void
    {
        $client = new WinpayClient([
            'partner_id' => 'test',
            'merchant_private_key' => $this->privateKey,
        ]);

        $this->assertInstanceOf(SnapContext::class, $client->snap());
    }

    public function testSnapIsSingleton(): void
    {
        $client = new WinpayClient([
            'partner_id' => 'test',
            'merchant_private_key' => $this->privateKey,
        ]);

        $this->assertSame($client->snap(), $client->snap());
    }

    public function testSnapVaDelegatesCorrectly(): void
    {
        $client = new WinpayClient([
            'partner_id' => 'test',
            'merchant_private_key' => $this->privateKey,
        ]);

        $vaSnap = $client->snap()->va();
        $this->assertInstanceOf(VirtualAccountSnap::class, $vaSnap);
    }
}
