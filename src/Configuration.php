<?php

declare(strict_types = 1);

namespace Wolfcode\CloudflareTurnstile;

class Configuration
{
    public function __construct(
        private readonly string  $siteKey,
        private readonly string  $secretKey,
        private readonly string  $theme = 'auto',
        private readonly string  $size = 'normal',
        private readonly ?string $language = null,
        private readonly ?string $action = null,
        private readonly ?string $appearance = null,
        private readonly int     $timeout = 10000,
        private readonly bool    $retryEnabled = true,
        private readonly int     $retryInterval = 1500,
        private readonly ?string $expectedHostname = null,
    ) {}

    public function getSiteKey(): string
    {
        return $this->siteKey;
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function getTheme(): string
    {
        return $this->theme;
    }

    public function getSize(): string
    {
        return $this->size;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function getAppearance(): ?string
    {
        return $this->appearance;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function isRetryEnabled(): bool
    {
        return $this->retryEnabled;
    }

    public function getRetryInterval(): int
    {
        return $this->retryInterval;
    }

    public function getExpectedHostname(): ?string
    {
        return $this->expectedHostname;
    }

    public static function fromArray(array $config): self
    {
        return new self(
            siteKey         : $config['site_key'] ?? '',
            secretKey       : $config['secret_key'] ?? '',
            theme           : $config['theme'] ?? 'auto',
            size            : $config['size'] ?? 'normal',
            language        : $config['language'] ?? null,
            action          : $config['action'] ?? null,
            appearance      : $config['appearance'] ?? null,
            timeout         : $config['timeout'] ?? 10000,
            retryEnabled    : $config['retry_enabled'] ?? true,
            retryInterval   : $config['retry_interval'] ?? 1500,
            expectedHostname: $config['expected_hostname'] ?? null,
        );
    }
}
