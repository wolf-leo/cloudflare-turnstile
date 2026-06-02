<?php
/**
 * Cloudflare Turnstile PHP Plugin - Usage Example
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Wolfcode\CloudflareTurnstile\Widget;
use Wolfcode\CloudflareTurnstile\Turnstile;
use Wolfcode\CloudflareTurnstile\Exception\ValidationException;

$siteKey = 'YOUR_SITE_KEY';
$secretKey = 'YOUR_SECRET_KEY';

// ============================================
// Example 1: Simple Form
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
        $turnstile = new Turnstile($secretKey);
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

            <?php
            $widget = new Widget($siteKey);
            echo $widget->render();
            ?>

            <button type="submit">Submit</button>
        </form>
    <?php endif; ?>
</body>
</html>
