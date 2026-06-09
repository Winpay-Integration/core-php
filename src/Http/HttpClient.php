<?php

namespace Winpay\Core\Http;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Winpay\Core\Contracts\ConfigInterface;
use Winpay\Core\Contracts\HttpClientInterface;
use Winpay\Core\Exceptions\HttpException;
use Winpay\Core\Exceptions\WinpayException;
use Winpay\Core\Models\WinpayResponse;
use Winpay\Core\Signature\Signer;

class HttpClient implements HttpClientInterface
{
    private readonly Signer $signer;
    private $onRequest;

    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly ConfigInterface $config,
        ?callable $onRequest = null,
        private readonly string $userAgent = 'winpay-core-php/1.0.0',
    ) {
        $this->signer = new Signer($config->getMerchantPrivateKey());
        $this->onRequest = $onRequest;
    }

    public function request(string $method, string $endpoint, ?array $body = null): WinpayResponse
    {
        $timestamp = $this->generateTimestamp();
        $externalId = $this->generateExternalId();

        $signature = $this->signer->sign($method, $endpoint, $body, $timestamp);
        $stringToSign = Signer::buildStringToSign($method, $endpoint, $body, $timestamp);

        $request = $this->requestFactory->createRequest($method, $this->config->getBaseUrl() . $endpoint);

        $request = $request
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('X-TIMESTAMP', $timestamp)
            ->withHeader('X-SIGNATURE', $signature)
            ->withHeader('X-PARTNER-ID', $this->config->getPartnerId())
            ->withHeader('X-EXTERNAL-ID', $externalId)
            ->withHeader('CHANNEL-ID', $this->config->getChannelId())
            ->withHeader('User-Agent', $this->userAgent);

        if ($body !== null) {
            $stream = $this->streamFactory->createStream(json_encode($body));
            $request = $request->withBody($stream);
        }

        if ($this->onRequest) {
            try {
                ($this->onRequest)(
                    method: $method,
                    url: (string) $request->getUri(),
                    body: $body,
                    headers: $request->getHeaders(),
                    stringToSign: $stringToSign,
                );
            } catch (\Throwable) {
            }
        }

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (\Throwable $e) {
            throw new WinpayException('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }

        return $this->parseResponse($response);
    }

    private function parseResponse(ResponseInterface $response): WinpayResponse
    {
        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new WinpayException('Invalid JSON response: ' . $body, $statusCode);
        }

        $winpayResponse = new WinpayResponse(
            responseCode: $data['responseCode'] ?? null,
            responseMessage: $data['responseMessage'] ?? null,
            httpStatusCode: $statusCode,
            data: $data,
        );

        if ($statusCode >= 400) {
            throw new HttpException(
                $winpayResponse->responseMessage ?? 'Unknown error',
                $statusCode,
                null,
                $statusCode,
                $data,
            );
        }

        return $winpayResponse;
    }

    private function generateTimestamp(): string
    {
        return (new \DateTimeImmutable())->format('Y-m-d\TH:i:sP');
    }

    private function generateExternalId(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
