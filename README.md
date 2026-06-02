# Cloudflare Turnstile PHP

A PHP SDK for Cloudflare Turnstile CAPTCHA alternative.

## Installation

```bash
composer require wolfcode/cloudflare-turnstile
```

## Quick Start

### 1. Configuration

```php
use Wolfcode\CloudflareTurnstile\Configuration;

$config = new Configuration(
    siteKey: 'YOUR_SITE_KEY',
    secretKey: 'YOUR_SECRET_KEY',
);
```

### 2. Render Widget

```php
use Wolfcode\CloudflareTurnstile\Widget;

$widget = new Widget($config);
echo $widget->render();
```

### 3. Validate Token

```php
use Wolfcode\CloudflareTurnstile\Turnstile;
use Wolfcode\CloudflareTurnstile\Exception\ValidationException;

$turnstile = new Turnstile($config);

try {
    $result = $turnstile->validate($_POST['cf-turnstile-response']);
    // Validation passed
} catch (ValidationException $e) {
    // Validation failed
    echo $e->getMessage();
}
```

## Configuration Options

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `siteKey` | string | required | Site key from Cloudflare dashboard |
| `secretKey` | string | required | Secret key from Cloudflare dashboard |
| `theme` | string | `auto` | Widget theme: `light`, `dark`, or `auto` |
| `size` | string | `normal` | Widget size: `normal` or `compact` |
| `language` | string | `null` | Language code (e.g., `en`, `zh-CN`) |
| `action` | string | `null` | Action identifier for validation |
| `appearance` | string | `null` | `always` or `interaction-only` |
| `timeout` | int | `10000` | Request timeout in milliseconds |
| `retryEnabled` | bool | `true` | Enable retry on network failure |
| `retryInterval` | int | `1500` | Retry interval in milliseconds |
| `expectedHostname` | string | `null` | Expected hostname for validation |

## Advanced Usage

### Custom Guzzle Client

```php
use GuzzleHttp\Client;
use Wolfcode\CloudflareTurnstile\Configuration;
use Wolfcode\CloudflareTurnstile\Turnstile;

$config = new Configuration(
    siteKey: 'YOUR_SITE_KEY',
    secretKey: 'YOUR_SECRET_KEY',
);

$client = new Client([
    'proxy' => 'tcp://localhost:8080',
    'timeout' => 30,
]);

$turnstile = new Turnstile($config, $client);
```

### Check Validity Without Exception

```php
$turnstile = new Turnstile($config);

if ($turnstile->isValid($_POST['cf-turnstile-response'])) {
    // Valid token
}
```

### Get Remote IP and Token

```php
$token = Turnstile::getToken();
$remoteIp = Turnstile::getRemoteIp();
```

## Widget Options

### Basic Render

```php
$widget = new Widget($config);
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
