# Cloudflare Turnstile PHP

A PHP SDK for Cloudflare Turnstile CAPTCHA alternative.

## Installation

```bash
composer require wolfcode/cloudflare-turnstile
```

## Quick Start

### 1. Render Widget (Frontend)

```php
use Wolfcode\CloudflareTurnstile\Widget;

$widget = new Widget(
    siteKey: 'YOUR_SITE_KEY',
);
echo $widget->render();
```

### 2. Validate Token (Backend)

```php
use Wolfcode\CloudflareTurnstile\Turnstile;
use Wolfcode\CloudflareTurnstile\Exception\ValidationException;

$turnstile = new Turnstile(
    secretKey: 'YOUR_SECRET_KEY',
);

try {
    $result = $turnstile->validate($_POST['cf-turnstile-response']);
    // Validation passed
} catch (ValidationException $e) {
    // Validation failed
    echo $e->getMessage();
}
```

## Widget Configuration

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `siteKey` | string | required | Site key from Cloudflare dashboard |
| `theme` | string | `auto` | Widget theme: `light`, `dark`, or `auto` |
| `size` | string | `normal` | Widget size: `normal` or `compact` |
| `language` | string | `null` | Language code (e.g., `en`, `zh-CN`) |
| `appearance` | string | `null` | `always` or `interaction-only` |
| `retryEnabled` | bool | `true` | Enable retry on network failure |
| `retryInterval` | int | `1500` | Retry interval in milliseconds |

## Turnstile Configuration

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `secretKey` | string | required | Secret key from Cloudflare dashboard |
| `expectedHostname` | string | `null` | Expected hostname for validation |
| `action` | string | `null` | Action identifier for validation |
| `timeout` | int | `10000` | Request timeout in milliseconds |

## Advanced Usage

### Custom Guzzle Client

```php
use GuzzleHttp\Client;
use Wolfcode\CloudflareTurnstile\Turnstile;

$client = new Client([
    'proxy' => 'tcp://localhost:8080',
    'timeout' => 30,
]);

$turnstile = new Turnstile(
    secretKey: 'YOUR_SECRET_KEY',
    client: $client,
);
```

### Check Validity Without Exception

```php
if ($turnstile->isValid($_POST['cf-turnstile-response'])) {
    // Valid token
}
```

### Get Remote IP and Token

```php
$token = Turnstile::getToken();
$remoteIp = Turnstile::getRemoteIp();
```

## Widget Render Methods

### Basic Render

```php
$widget = new Widget(siteKey: 'YOUR_SITE_KEY');
echo $widget->render();
```

### With Action

```php
echo $widget->render(action: 'login');
```

### With Custom Data

```php
echo $widget->render(data: 'session-12345');
```

### With Script Tag

```php
echo $widget->renderComplete();
```

## Testing

Cloudflare provides test keys for development:

| Key | Value |
|-----|-------|
| Site Key | `1x00000000000000000000AA` |
| Secret Key | `1x0000000000000000000000000000000AA` |

These keys always pass validation and won't count against your quota.

## Error Handling

```php
use Wolfcode\CloudflareTurnstile\Exception\ValidationException;

try {
    $turnstile->validate($token);
} catch (ValidationException $e) {
    $errorCodes = $e->getErrorCodes();
    
    foreach ($errorCodes as $code) {
        match ($code) {
            'missing-input-response' => // Token not provided
            'invalid-input-response' => // Invalid or expired token
            'timeout-or-duplicate' => // Token already used
            'hostname_mismatch' => // Hostname doesn't match
            'action_mismatch' => // Action doesn't match
            default => // Unknown error
        };
    }
}
```

## License

MIT License - see [LICENSE](LICENSE) for details.
