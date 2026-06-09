# Winpay Core — PHP Library Framework-Agnostic

Library inti buat integrasi Winpay Payment Gateway. Bisa dibungkus framework apa aja (Laravel, Symfony, Yii, dll).

## Requirements

- PHP ^8.1
- PSR-18 (HTTP Client)
- PSR-17 (HTTP Factory)
- ext-openssl

## Arsitektur

```
WinpayClient
 ├── ConfigInterface         (credential, endpoint, kunci)
 ├── HttpClient              (SNAP: signing RSA-SHA256)
 │    └── Signer             (sign & verify pake merchant private key)
 ├── CheckoutClient          (Checkout Page: signing HMAC-SHA256)
 ├── SnapContext             (grup semua channel SNAP)
 │    ├── VirtualAccountSnap → VirtualAccountService
 │    ├── QrisSnap           → QrisService
 │    ├── EwalletSnap        → EwalletService
 │    ├── CreditCardSnap     → CreditCardService
 │    ├── RetailSnap         → RetailService
 │    ├── ReportSnap         → ReportService
 │    └── CheckoutSnap       → CheckoutService
 └── class Service           (definisi endpoint, tanpa logika HTTP)
```

### Dua Skema Auth yang Beda

| Skema | Header | Algoritma | Dipake buat |
|---|---|---|---|
| **SNAP** | `X-TIMESTAMP`, `X-SIGNATURE`, `X-PARTNER-ID`, `X-EXTERNAL-ID`, `CHANNEL-ID` | RSA-SHA256 (`openssl_sign`) | VA, QRIS, eWallet, CC, Retail, Report |
| **Checkout** | `X-Winpay-Timestamp`, `X-Winpay-Signature`, `X-Winpay-Key` | HMAC-SHA256 (`hash_hmac`) | Checkout Page |

## Contract (buat yang mau bikin wrapper)

Ini yang perlu diimplement biar bisa bikin wrapper framework sendiri:

### `ConfigInterface`
```php
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
```

Key yang dipake library:
- `partner_id`, `merchant_private_key`, `channel_id`, `base_url`, `winpay_public_key`
- `checkout_key`, `checkout_secret_key`, `checkout_base_url`
- `timeout` (default 30), `verify_ssl` (default true)

### `HttpClientInterface`
```php
interface HttpClientInterface
{
    public function request(string $method, string $endpoint, ?array $body = null): WinpayResponse;
}
```

## Cara Pake dari Wrapper

```php
use Winpay\Core\WinpayClient;

// Langsung lempar array (otomatis pake WinpayConfig)
$client = new WinpayClient([
    'partner_id'          => 'your_partner_id',
    'merchant_private_key' => '-----BEGIN PRIVATE KEY-----\n...',
    'channel_id'          => 'WEB',
    'base_url'            => 'https://sandbox-api.bmstaging.id/snap',
    'winpay_public_key'   => '-----BEGIN PUBLIC KEY-----\n...',
    'checkout_key'        => 'your_checkout_key',
    'checkout_secret_key' => 'your_checkout_secret_key',
    'checkout_base_url'   => 'https://sandbox-checkout.winpay.id',
]);

// SNAP channel (RSA-SHA256)
$client->snap()->va()->create($payload);
$client->snap()->va()->inquiry($payload);
$client->snap()->va()->status($payload);
$client->snap()->va()->delete($payload);

$client->snap()->qris()->create($payload);
$client->snap()->qris()->status($payload);
$client->snap()->qris()->cancel($payload);

$client->snap()->ewallet()->create($payload);
$client->snap()->ewallet()->status($payload);
$client->snap()->ewallet()->cancel($payload);

$client->snap()->creditCard()->create($payload);
$client->snap()->creditCard()->status($payload);
$client->snap()->creditCard()->cancel($payload);

$client->snap()->retail()->create($payload);
$client->snap()->retail()->status($payload);
$client->snap()->retail()->cancel($payload);

$client->snap()->report()->balance($payload);
$client->snap()->report()->history($payload);
$client->snap()->report()->statement($payload);

// Checkout Page (HMAC-SHA256)
$client->snap()->checkout()->create($payload);
$client->snap()->checkout()->find($id);
$client->snap()->checkout()->findByRef($merchantRef);
$client->snap()->checkout()->update($id, $payload);
$client->snap()->checkout()->delete($id);
$client->snap()->checkout()->deleteByRef($merchantRef);

// Verifikasi callback (SNAP RSA signature)
$verified = $client->verifyCallback('POST', '/callback/path', $body, $timestamp, $signature);
```

Bisa juga pake custom `ConfigInterface` dan PSR-18/PSR-17 bikinan sendiri:

```php
$client = new WinpayClient(
    config: $myConfig,                        // ConfigInterface
    httpClient: $psr18Client,                 // ClientInterface
    requestFactory: $psr17RequestFactory,     // RequestFactoryInterface
    streamFactory: $psr17StreamFactory,       // StreamFactoryInterface
    onRequest: $callback,                     // ?callable (buat logging/monitoring)
    userAgent: 'my-wrapper/1.0',              // string
);
```

### Callback `onRequest`

Duluan dipanggil sebelum tiap request HTTP. Dapet method, URL, body, headers, dan string-to-sign:

```php
$onRequest = function (
    string $method,
    string $url,
    ?array $body,
    array $headers,
    string $stringToSign,
): void {
    // Catet atau monitor request
};
$client = new WinpayClient(config: [...], onRequest: $onRequest);
```

## Response

Semua method service balikin `WinpayResponse`:

| Property | Type | Deskripsi |
|---|---|---|
| `responseCode` | `?string` | misal `"2002700"` |
| `responseMessage` | `?string` | misal `"Successful"` |
| `httpStatusCode` | `int` | misal `200` |
| `data` | `array` | Full response dari server |
| `isSuccess()` | `bool` | `true` kalo 2xx |
| `get(key, default)` | `mixed` | Akses data pake key |

## Alur Signature SNAP

```
hash('sha256', json_encode($body))
        ↓
strtoupper($method) . ':' . $endpoint . ':' . $hash . ':' . $timestamp
        ↓
openssl_sign($stringToSign, $signature, $privateKey, OPENSSL_ALGO_SHA256)
        ↓
base64_encode($signature) → header X-SIGNATURE
```

## Alur Signature Checkout

```
hash_hmac('sha256', $timestamp, $secretKey)
        ↓
Hasilnya → header X-Winpay-Signature
```

## Enum

`VaChannel`, `VaType`, `EwalletChannel`, `RetailChannel`, `TransactionStatus` — semuanya backed string enum.

## Exception

| Exception | Kapan kepake |
|---|---|
| `WinpayException` | Config error, jaringan error, JSON nggak valid |
| `HttpException` | Response 4xx/5xx (extends `WinpayException`) |

## Testing

```bash
php vendor/bin/phpunit
```