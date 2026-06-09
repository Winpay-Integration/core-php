<?php

namespace Winpay\Core;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Winpay\Core\Config\WinpayConfig;
use Winpay\Core\Contracts\ConfigInterface;
use Winpay\Core\Exceptions\WinpayException;
use Winpay\Core\Http\CheckoutClient;
use Winpay\Core\Http\HttpClient;
use Winpay\Core\Services\CheckoutService;
use Winpay\Core\Services\CreditCardService;
use Winpay\Core\Services\EwalletService;
use Winpay\Core\Services\QrisService;
use Winpay\Core\Services\ReportService;
use Winpay\Core\Services\RetailService;
use Winpay\Core\Context\SnapContext;
use Winpay\Core\Services\VirtualAccountService;
use Winpay\Core\Signature\Signer;

class WinpayClient
{
    public const VERSION = '1.0.0';

    public readonly VirtualAccountService $va;
    public readonly QrisService $qris;
    public readonly EwalletService $ewallet;
    public readonly CreditCardService $creditCard;
    public readonly RetailService $retail;
    public readonly ReportService $report;
    public readonly CheckoutService $checkout;

    private readonly ConfigInterface $config;
    private ?SnapContext $snapContext = null;
    private $onRequest;

    public function __construct(
        array|ConfigInterface $config,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        ?callable $onRequest = null,
        private readonly string $userAgent = 'winpay-core-php/' . self::VERSION,
    ) {
        $this->onRequest = $onRequest;
        $this->config = is_array($config) ? new WinpayConfig($config) : $config;

        $http = new HttpClient(
            httpClient: $httpClient ?? new \GuzzleHttp\Client([
                'timeout' => $this->config->get('timeout', 30),
                'verify' => $this->config->get('verify_ssl', true),
            ]),
            requestFactory: $requestFactory ?? new \GuzzleHttp\Psr7\HttpFactory(),
            streamFactory: $streamFactory ?? new \GuzzleHttp\Psr7\HttpFactory(),
            config: $this->config,
            onRequest: $this->onRequest,
            userAgent: $this->userAgent,
        );

        $this->va = new VirtualAccountService($http);
        $this->qris = new QrisService($http);
        $this->ewallet = new EwalletService($http);
        $this->creditCard = new CreditCardService($http);
        $this->retail = new RetailService($http);
        $this->report = new ReportService($http);

        $checkoutBaseUrl = $this->config->get('checkout_base_url', 'https://sandbox-checkout.winpay.id');
        $checkoutHttp = new CheckoutClient(
            httpClient: $httpClient ?? new \GuzzleHttp\Client([
                'timeout' => $this->config->get('timeout', 30),
                'verify' => $this->config->get('verify_ssl', true),
            ]),
            requestFactory: $requestFactory ?? new \GuzzleHttp\Psr7\HttpFactory(),
            streamFactory: $streamFactory ?? new \GuzzleHttp\Psr7\HttpFactory(),
            baseUrl: $checkoutBaseUrl,
            key: $this->config->get('checkout_key', ''),
            secretKey: $this->config->get('checkout_secret_key', ''),
            onRequest: $this->onRequest,
            userAgent: $this->userAgent,
        );

        $this->checkout = new CheckoutService($checkoutHttp);
    }

    public function snap(): SnapContext
    {
        if ($this->snapContext === null) {
            $this->snapContext = new SnapContext(
                vaService: $this->va,
                qrisService: $this->qris,
                ewalletService: $this->ewallet,
                creditCardService: $this->creditCard,
                retailService: $this->retail,
                reportService: $this->report,
                checkoutService: $this->checkout,
            );
        }

        return $this->snapContext;
    }

    public function verifyCallback(string $httpMethod, string $endpointUrl, array $requestBody, string $timestamp, string $signature): bool
    {
        $publicKey = $this->config->getWinpayPublicKey();
        if ($publicKey === null) {
            throw new WinpayException('winpay_public_key is not configured');
        }

        return Signer::verify($signature, $httpMethod, $endpointUrl, $requestBody, $timestamp, $publicKey);
    }

    public function getConfig(): ConfigInterface
    {
        return $this->config;
    }
}
