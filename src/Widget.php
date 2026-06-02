<?php

declare(strict_types = 1);

namespace Wolfcode\CloudflareTurnstile;

class Widget
{
    private const SCRIPT_URL = 'https://challenges.cloudflare.com/turnstile/v0/api.js';

    public function __construct(
        private readonly string  $siteKey,
        private readonly string  $theme = 'auto',
        private readonly string  $size = 'normal',
        private readonly ?string $language = null,
        private readonly ?string $appearance = null,
        private readonly bool    $retryEnabled = true,
        private readonly int     $retryInterval = 1500,
        private readonly ?string $callback = null,
    ) {}

    public function getSiteKey(): string
    {
        return $this->siteKey;
    }

    /**
     * Render the Turnstile widget HTML.
     */
    public function render(?string $action = null, ?string $data = null): string
    {
        $attributes = [
            'class'        => 'cf-turnstile',
            'data-sitekey' => $this->siteKey,
            'data-theme'   => $this->theme,
            'data-size'    => $this->size,
        ];

        if ($action !== null) {
            $attributes['data-action'] = $action;
        }

        if ($data !== null) {
            $attributes['data-cdata'] = $data;
        }

        if ($this->language !== null) {
            $attributes['data-language'] = $this->language;
        }

        if ($this->appearance !== null) {
            $attributes['data-appearance'] = $this->appearance;
        }

        if (!$this->retryEnabled) {
            $attributes['data-retry'] = 'never';
        }else {
            $attributes['data-retry-interval'] = (string)$this->retryInterval;
        }

        if ($this->callback !== null) {
            $attributes['data-callback'] = $this->callback;
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

        if ($this->language !== null) {
            $params['lang'] = $this->language;
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
