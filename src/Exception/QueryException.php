<?php

declare(strict_types=1);

namespace Roulette\Exception;

class QueryException extends RouletteException
{
    private mixed $query;

    public function __construct(string $message, mixed $query = null, ?\Throwable $previous = null)
    {
        $this->query = $query;
        parent::__construct($message, 0, $previous);
    }

    public function getQuery(): mixed
    {
        return $this->query;
    }
}
