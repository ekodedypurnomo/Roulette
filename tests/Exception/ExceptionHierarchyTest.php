<?php

declare(strict_types=1);

namespace Roulette\Tests\Exception;

use Roulette\Exception\AssociationException;
use Roulette\Exception\ModelNotFoundException;
use Roulette\Exception\QueryException;
use Roulette\Exception\RouletteException;
use Roulette\Exception\ValidationException;
use Roulette\Tests\TestCase;

class ExceptionHierarchyTest extends TestCase
{
    // --- RouletteException ---

    public function testRouletteExceptionIsRuntimeException(): void
    {
        $e = new RouletteException('base');
        $this->assertInstanceOf(\RuntimeException::class, $e);
    }

    // --- ValidationException ---

    public function testValidationExceptionExtendsRouletteException(): void
    {
        $e = new ValidationException(['name' => ['required']]);
        $this->assertInstanceOf(RouletteException::class, $e);
    }

    public function testValidationExceptionStoresErrors(): void
    {
        $errors = ['name' => ['required'], 'email' => ['invalid format']];
        $e = new ValidationException($errors);
        $this->assertSame($errors, $e->getErrors());
    }

    public function testValidationExceptionDefaultMessage(): void
    {
        $e = new ValidationException(['field' => ['bad']]);
        $this->assertStringContainsString('Validation failed', $e->getMessage());
        $this->assertStringContainsString('field', $e->getMessage());
    }

    public function testValidationExceptionCustomMessage(): void
    {
        $e = new ValidationException(['x' => []], 'Custom error');
        $this->assertSame('Custom error', $e->getMessage());
    }

    // --- ModelNotFoundException ---

    public function testModelNotFoundExceptionExtendsRouletteException(): void
    {
        $e = new ModelNotFoundException('App\User', '123');
        $this->assertInstanceOf(RouletteException::class, $e);
    }

    public function testModelNotFoundExceptionStoresModelClass(): void
    {
        $e = new ModelNotFoundException('App\User', '123');
        $this->assertSame('App\User', $e->getModelClass());
    }

    public function testModelNotFoundExceptionStoresId(): void
    {
        $e = new ModelNotFoundException('App\User', '42');
        $this->assertSame('42', $e->getId());
    }

    public function testModelNotFoundExceptionMessage(): void
    {
        $e = new ModelNotFoundException('App\User', '99');
        $this->assertStringContainsString('App\User', $e->getMessage());
        $this->assertStringContainsString('99', $e->getMessage());
    }

    public function testModelNotFoundExceptionNullId(): void
    {
        $e = new ModelNotFoundException('App\User');
        $this->assertNull($e->getId());
        $this->assertStringContainsString('App\User', $e->getMessage());
    }

    public function testModelNotFoundExceptionArrayId(): void
    {
        $e = new ModelNotFoundException('App\User', ['tenant' => 'a', 'id' => 'b']);
        $this->assertIsArray($e->getId());
        $this->assertStringContainsString('App\User', $e->getMessage());
    }

    // --- QueryException ---

    public function testQueryExceptionExtendsRouletteException(): void
    {
        $e = new QueryException('query failed');
        $this->assertInstanceOf(RouletteException::class, $e);
    }

    public function testQueryExceptionStoresQuery(): void
    {
        $e = new QueryException('bad query', 'SELECT * FROM nowhere');
        $this->assertSame('SELECT * FROM nowhere', $e->getQuery());
    }

    public function testQueryExceptionNullQuery(): void
    {
        $e = new QueryException('oops');
        $this->assertNull($e->getQuery());
    }

    public function testQueryExceptionChainsPrevious(): void
    {
        $prev = new \RuntimeException('cause');
        $e = new QueryException('wrapped', null, $prev);
        $this->assertSame($prev, $e->getPrevious());
    }

    // --- AssociationException ---

    public function testAssociationExceptionExtendsRouletteException(): void
    {
        $e = new AssociationException('bad assoc');
        $this->assertInstanceOf(RouletteException::class, $e);
    }
}
