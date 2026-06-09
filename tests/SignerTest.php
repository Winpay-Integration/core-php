<?php

namespace Winpay\Core\Tests;

use PHPUnit\Framework\TestCase;
use Winpay\Core\Exceptions\WinpayException;
use Winpay\Core\Signature\Signer;

class SignerTest extends TestCase
{
    private string $privateKey = '';
    private string $publicKey = '';

    protected function setUp(): void
    {
        $key = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        openssl_pkey_export($key, $this->privateKey);
        $details = openssl_pkey_get_details($key);
        $this->publicKey = $details['key'];
    }

    public function testSignAndVerify(): void
    {
        $signer = new Signer($this->privateKey);

        $signature = $signer->sign('POST', '/v1.0/transfer-va/create-va', ['amount' => '10000'], '2024-01-01T12:00:00+07:00');

        $this->assertNotEmpty($signature);
        $this->assertTrue(base64_decode($signature, true) !== false);

        $verified = Signer::verify(
            $signature,
            'POST',
            '/v1.0/transfer-va/create-va',
            ['amount' => '10000'],
            '2024-01-01T12:00:00+07:00',
            $this->publicKey,
        );

        $this->assertTrue($verified);
    }

    public function testVerifyFailsWithWrongSignature(): void
    {
        $signer = new Signer($this->privateKey);

        $signature = $signer->sign('POST', '/v1.0/transfer-va/create-va', ['amount' => '10000'], '2024-01-01T12:00:00+07:00');

        $verified = Signer::verify(
            $signature,
            'POST',
            '/v1.0/transfer-va/create-va',
            ['amount' => '99999'],
            '2024-01-01T12:00:00+07:00',
            $this->publicKey,
        );

        $this->assertFalse($verified);
    }

    public function testSignWithInvalidPrivateKey(): void
    {
        $this->expectException(WinpayException::class);

        $signer = new Signer('invalid-key');
        $signer->sign('GET', '/test', null, 'ts');
    }

    public function testVerifyWithInvalidPublicKey(): void
    {
        $this->expectException(WinpayException::class);

        Signer::verify('sig', 'GET', '/test', null, 'ts', 'invalid-key');
    }

    public function testHttpMethodIsUppercased(): void
    {
        $signer = new Signer($this->privateKey);

        $sigLower = $signer->sign('post', '/test', null, 'ts');
        $sigUpper = $signer->sign('POST', '/test', null, 'ts');

        $this->assertSame($sigLower, $sigUpper);
    }

    public function testNullBodyIsEmptyString(): void
    {
        $signer = new Signer($this->privateKey);
        $sig = $signer->sign('GET', '/test', null, 'ts');
        $this->assertNotEmpty($sig);
    }

    public function testSignatureDifferentPerMethod(): void
    {
        $signer = new Signer($this->privateKey);

        $sigPost = $signer->sign('POST', '/test', ['a' => 1], 'ts');
        $sigGet = $signer->sign('GET', '/test', ['a' => 1], 'ts');

        $this->assertNotSame($sigPost, $sigGet);
    }

    public function testSignatureDifferentPerEndpoint(): void
    {
        $signer = new Signer($this->privateKey);

        $sig1 = $signer->sign('POST', '/va/create', ['a' => 1], 'ts');
        $sig2 = $signer->sign('POST', '/va/status', ['a' => 1], 'ts');

        $this->assertNotSame($sig1, $sig2);
    }

    public function testSignatureDifferentPerTimestamp(): void
    {
        $signer = new Signer($this->privateKey);

        $sig1 = $signer->sign('POST', '/test', ['a' => 1], 'ts1');
        $sig2 = $signer->sign('POST', '/test', ['a' => 1], 'ts2');

        $this->assertNotSame($sig1, $sig2);
    }

    public function testBuildStringToSign(): void
    {
        $result = Signer::buildStringToSign('POST', '/v1.0/test', ['foo' => 'bar'], '2024-01-01T12:00:00+07:00');

        $expectedHash = strtolower(hash('sha256', json_encode(['foo' => 'bar'])));
        $expected = 'POST:/v1.0/test:' . $expectedHash . ':2024-01-01T12:00:00+07:00';

        $this->assertSame($expected, $result);
    }

    public function testBuildStringToSignWithNullBody(): void
    {
        $result = Signer::buildStringToSign('GET', '/v1.0/test', null, 'ts');

        $expectedHash = strtolower(hash('sha256', ''));
        $expected = 'GET:/v1.0/test:' . $expectedHash . ':ts';

        $this->assertSame($expected, $result);
    }
}
