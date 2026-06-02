<?php

declare(strict_types = 1);

namespace Wolfcode\CloudflareTurnstile;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\TransferException;
use Wolfcode\CloudflareTurnstile\Exception\ValidationException;

class Turnstile
{
    private const SITEVERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    private Client $client;

    public function __construct(
        private readonly string  $secretKey,
        private readonly ?string $expectedHostname = null,
        private readonly ?string $action = null,
        private readonly int     $timeout = 10000,
        ?Client                  $client = null,
    )
    {
        $this->client = $client ?? new Client([
            'base_uri'        => self::SITEVERIFY_URL,
            'timeout'         => $this->timeout / 1000,
            'connect_timeout' => 5,
            'headers'         => [
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Validate a Turnstile token.
     *
     * @throws ValidationException
     */
    public function validate(string $token, ?string $remoteIp = null): array
    {
        if (empty($token)) {
            throw new ValidationException(
                'Missing turnstile token',
                ['missing-input-response']
            );
        }

        $data = [
            'secret'   => $this->secretKey,
            'response' => $token,
        ];

        if ($remoteIp !== null) {
            $data['remoteip'] = $remoteIp;
        }

        $data['idempotency_key'] = $this->generateIdempotencyKey();

        $result = $this->sendRequest($data);

        if (!$result['success']) {
            $errorCodes = $result['error-codes'] ?? ['internal-error'];
            throw new ValidationException(
                'Turnstile validation failed: ' . implode(', ', $errorCodes),
                $errorCodes
            );
        }

        if ($this->expectedHostname !== null
            && ($result['hostname'] ?? '') !== $this->expectedHostname
        ) {
            throw new ValidationException(
                'Hostname mismatch: expected "' . $this->expectedHostname . '" but got "' . ($result['hostname'] ?? '') . '"',
                ['hostname_mismatch']
            );
        }

        if ($this->action !== null
            && ($result['action'] ?? '') !== $this->action
        ) {
            throw new ValidationException(
                'Action mismatch: expected "' . $this->action . '" but got "' . ($result['action'] ?? '') . '"',
                ['action_mismatch']
            );
        }

        return $result;
    }

    /**
     * Check if a token is valid without throwing exceptions.
     */
    public function isValid(string $token, ?string $remoteIp = null): bool
    {
        try {
            $this->validate($token, $remoteIp);
            return true;
        }catch (ValidationException $exception) {
            throw  $exception;
        }
    }

    /**
     * Get the remote IP address from the request.
     */
    public static function getRemoteIp(): ?string
    {
        return $_SERVER['HTTP_CF_CONNECTING_IP']
               ?? $_SERVER['HTTP_X_FORWARDED_FOR']
                  ?? $_SERVER['REMOTE_ADDR']
                     ?? null;
    }

    /**
     * Get the turnstile token from the request.
     */
    public static function getToken(): ?string
    {
        return $_POST['cf-turnstile-response'] ?? null;
    }

    private function sendRequest(array $data): array
    {
        try {
            $response = $this->client->post('', [
                'form_params' => $data,
            ]);

            $body   = $response->getBody()->getContents();
            $result = json_decode($body, true);

            if (!is_array($result)) {
                throw new ValidationException(
                    'Invalid response from Turnstile API',
                    ['invalid-response']
                );
            }

            return $result;
        }catch (ValidationException $e) {
            throw $e;
        }catch (TransferException $e) {
            throw new ValidationException(
                'Turnstile request failed: ' . $e->getMessage(),
                ['http-error'],
                0,
                $e
            );
        }catch (GuzzleException $e) {
            throw new ValidationException(
                'Turnstile request failed: ' . $e->getMessage(),
                ['guzzle-error'],
                0,
                $e
            );
        }
    }

    private function generateIdempotencyKey(): string
    {
        if (function_exists('random_bytes')) {
            $data = random_bytes(16);
        }elseif (function_exists('openssl_random_pseudo_bytes')) {
            $data = openssl_random_pseudo_bytes(16);
        }else {
            $data = mt_rand() . mt_rand();
        }

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
