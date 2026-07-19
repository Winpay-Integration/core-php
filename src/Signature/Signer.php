<?php

namespace Winpay\Core\Signature;

use Winpay\Core\Exceptions\WinpayException;

class Signer
{
    public function __construct(
        private readonly string $privateKey,
    ) {
    }

    public static function buildStringToSign(string $httpMethod, string $endpointUrl, ?array $requestBody, string $timestamp): string
    {
        $bodyJson = $requestBody !== null ? json_encode($requestBody, JSON_UNESCAPED_SLASHES) : '';
        $hashedBody = strtolower(hash('sha256', $bodyJson, false));

        return strtoupper($httpMethod) . ':' . $endpointUrl . ':' . $hashedBody . ':' . $timestamp;
    }

    public function sign(string $httpMethod, string $endpointUrl, ?array $requestBody, string $timestamp): string
    {
        $stringToSign = self::buildStringToSign($httpMethod, $endpointUrl, $requestBody, $timestamp);

        $privateKey = openssl_get_privatekey($this->privateKey);
        if ($privateKey === false) {
            throw new WinpayException('Failed to load private key: ' . openssl_error_string());
        }

        $signature = '';
        $result = openssl_sign($stringToSign, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        if (!$result) {
            throw new WinpayException('Failed to generate signature: ' . openssl_error_string());
        }

        return base64_encode($signature);
    }

    public static function verify(string $signature, string $httpMethod, string $endpointUrl, ?array $requestBody, string $timestamp, string $publicKey): bool
    {
        $bodyJson = $requestBody !== null ? json_encode($requestBody, JSON_UNESCAPED_SLASHES) : '';
        $hashedBody = strtolower(hash('sha256', $bodyJson, false));

        $stringToSign = strtoupper($httpMethod) . ':' . $endpointUrl . ':' . $hashedBody . ':' . $timestamp;

        $publicKey = openssl_get_publickey($publicKey);
        if ($publicKey === false) {
            throw new WinpayException('Failed to load public key: ' . openssl_error_string());
        }

        $result = openssl_verify($stringToSign, base64_decode($signature), $publicKey, OPENSSL_ALGO_SHA256);

        return $result === 1;
    }
}
