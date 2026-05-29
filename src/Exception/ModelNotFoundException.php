<?php

declare(strict_types=1);

namespace Roulette\Exception;

class ModelNotFoundException extends RouletteException
{
    private string $modelClass;
    private mixed $id;

    public function __construct(string $modelClass, mixed $id = null)
    {
        $this->modelClass = $modelClass;
        $this->id = $id;
        $idStr = is_array($id) ? json_encode($id) : (string) $id;
        parent::__construct("No record found for {$modelClass}" . ($id !== null ? " with id {$idStr}" : ''));
    }

    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    public function getId(): mixed
    {
        return $this->id;
    }
}
