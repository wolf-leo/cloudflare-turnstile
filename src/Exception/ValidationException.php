<?php

declare(strict_types = 1);

namespace Wolfcode\CloudflareTurnstile\Exception;

class ValidationException extends TurnstileException
{
    private array $errorCodes;

    public function __construct(string $message, array $errorCodes = [], int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errorCodes = $errorCodes;
    }

    public function getErrorCodes(): array
    {
        return $this->errorCodes;
    }
}
