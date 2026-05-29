<?php

declare(strict_types=1);

namespace Roulette\Exception;

class ValidationException extends RouletteException
{
    private array $errors;

    public function __construct(array $errors, string $message = '')
    {
        $this->errors = $errors;
        parent::__construct($message ?: 'Validation failed: ' . implode(', ', array_keys($errors)));
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
