<?php

declare(strict_types=1);

namespace Wolfcode\CloudflareTurnstile;

class Widget
{
    private const SCRIPT_URL = 'https://challenges.cloudflare.com/turnstile/v0/api.js';

    public function __construct(
        private readonly Configuration $config,
    ) {
    }

    /**
     * Render the Turnstile widget HTML.
     */
    public function render(?string $action = null, ?string $data = null): string
    {
        $attributes = [
            'class' => 'cf-turnstile',
            'data-sitekey' => $this->config->getSiteKey(),
            'data-theme' => $this->config->getTheme(),
            'data-size' => $this->config->getSize(),
        ];

        if ($action !== null) {
            $attributes['data-action'] = $action;
        } elseif ($this->config->getAction() !== null) {
            $attributes['data-action'] = $this->config->getAction();
        }

        if ($data !== null) {
            $attributes['data-cdata'] = $data;
        }

        if ($this->config->getLanguage() !== null) {
            $attributes['data-language'] = $this->config->getLanguage();
        }

        if ($this->config->getAppearance() !== null) {
            $attributes['data-appearance'] = $this->config->getAppearance();
        }

        if (!$this->config->isRetryEnabled()) {
            $attributes['data-retry'] = 'never';
        } else {
            $attributes['data-retry-interval'] = (string) $this->config->getRetryInterval();
        }

        $attributeString = $this->buildAttributeString($attributes);

        return '<div ' . $attributeString . '></div>';
    }

    /**
     * Render the Turnstile widget with explicit callback.
     */
    public function renderWithCallback(string $callbackName, ?string $action = null, ?string $data = null): string
    {
        $html = $this->render($action, $data);

        $html .= '<script>';
        $html .= 'window.turnstileCallback = function(token) {';
        $html .= '  document.getElementById("cf-turnstile-response").value = token;';
        $html .= '  if (typeof window.' . $callbackName . ' === "function") {';
        $html .= '    window.' . $callbackName . '(token);';
        $html .= '  }';
        $html .= '};';
        $html .= '</script>';
        $html .= '<input type="hidden" id="cf-turnstile-response" name="cf-turnstile-response" />';

        return $html;
    }

    /**
     * Get the script tag for loading Turnstile.
     */
    public function getScriptTag(): string
    {
        $params = [];

        if ($this->config->getLanguage() !== null) {
            $params['lang'] = $this->config->getLanguage();
        }

        $url = self::SCRIPT_URL;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return '<script src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" async defer></script>';
    }

    /**
     * Get the complete HTML including script and widget.
     */
    public function renderComplete(?string $action = null, ?string $data = null): string
    {
        return $this->getScriptTag() . "\n" . $this->render($action, $data);
    }

    /**
     * Render a hidden input for form submissions.
     */
    public function renderHiddenInput(): string
    {
        return '<input type="hidden" name="cf-turnstile-response" value="" />';
    }

    private function buildAttributeString(array $attributes): string
    {
        $parts = [];
        foreach ($attributes as $name => $value) {
            $parts[] = htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
        }
        return implode(' ', $parts);
    }
}
