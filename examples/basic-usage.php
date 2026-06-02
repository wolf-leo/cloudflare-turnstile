<?php
/**
 * Cloudflare Turnstile PHP Plugin - Usage Example
 *
 * This example demonstrates how to use the Turnstile plugin in a PHP application.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Wolfcode\CloudflareTurnstile\Configuration;
use Wolfcode\CloudflareTurnstile\Turnstile;
use Wolfcode\CloudflareTurnstile\Widget;
use Wolfcode\CloudflareTurnstile\Exception\ValidationException;

// ============================================
// Configuration
// ============================================

$config = new Configuration(
    siteKey: 'YOUR_SITE_KEY',           // Replace with your site key
    secretKey: 'YOUR_SECRET_KEY',       // Replace with your secret key
    theme: 'auto',                      // light, dark, or auto
    size: 'normal',                     // normal or compact
    language: 'en',                     // Optional: language code
    action: 'login',                    // Optional: action identifier
    expectedHostname: 'example.com',    // Optional: expected hostname
    timeout: 10000,                     // Request timeout in ms
    retryEnabled: true,                 // Enable retry on network failure
    retryInterval: 1500,                // Retry interval in ms
);

// ============================================
// Example 1: Simple Form with Widget
// ============================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turnstile Example</title>
</head>
<body>
    <h1>Contact Form</h1>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <?php
        $turnstile = new Turnstile($config);
        $token = Turnstile::getToken();
        $remoteIp = Turnstile::getRemoteIp();

        try {
            $result = $turnstile->validate($token, $remoteIp);
            echo '<div style="color: green;">Form submitted successfully!</div>';
            echo '<pre>' . print_r($_POST, true) . '</pre>';
        } catch (ValidationException $e) {
            echo '<div style="color: red;">Verification failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>

    <?php else: ?>
        <form method="POST" action="">
            <div>
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div>
                <label for="message">Message:</label>
                <textarea id="message" name="message" required></textarea>
            </div>

            <?php
            // Render the Turnstile widget
            $widget = new Widget($config);
            echo $widget->render();
            ?>

            <button type="submit">Submit</button>
        </form>
    <?php endif; ?>
</body>
</html>
<?php

// ============================================
// Example 2: AJAX Form Submission
// ============================================
?>
<!--
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AJAX Turnstile Example</title>
</head>
<body>
    <h1>AJAX Form</h1>
    <form id="ajaxForm">
        <div>
            <label for="email2">Email:</label>
            <input type="email" id="email2" name="email" required>
        </div>

        <?php
        $widget = new Widget($config);
        echo $widget->render();
        ?>

        <button type="submit">Submit</button>
    </form>

    <div id="result"></div>

    <script>
    document.getElementById('ajaxForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        try {
            const response = await fetch('/api/submit.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            document.getElementById('result').textContent = JSON.stringify(result, null, 2);
        } catch (error) {
            document.getElementById('result').textContent = 'Error: ' + error.message;
        }
    });
    </script>
</body>
</html>
-->

// ============================================
// Example 3: API Endpoint (api/submit.php)
// ============================================
/*
<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Wolfcode\CloudflareTurnstile\Configuration;
use Wolfcode\CloudflareTurnstile\Turnstile;
use Wolfcode\CloudflareTurnstile\Exception\ValidationException;

header('Content-Type: application/json');

$config = new Configuration(
    siteKey: $_ENV['TURNSTILE_SITE_KEY'] ?? '',
    secretKey: $_ENV['TURNSTILE_SECRET_KEY'] ?? '',
);

$turnstile = new Turnstile($config);
$token = Turnstile::getToken();
$remoteIp = Turnstile::getRemoteIp();

try {
    $result = $turnstile->validate($token, $remoteIp);

    echo json_encode([
        'success' => true,
        'message' => 'Form submitted successfully',
        'data' => $_POST,
    ]);
} catch (ValidationException $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'errors' => $e->getErrorCodes(),
    ]);
}
*/

// ============================================
// Example 4: Using Test Keys
// ============================================
/*
// Cloudflare provides test keys for development:
$testConfig = new Configuration(
    siteKey: '1x00000000000000000000AA',    // Always passes
    secretKey: '1x0000000000000000000000000000000AA',  // Always passes
);

// Use these keys during development to avoid hitting rate limits
*/
