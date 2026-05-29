<?php

declare(strict_types=1);

namespace Roulette\Tests\Support;

use PDO;
use ReflectionProperty;
use Roulette\Query\Operation;
use Roulette\Tests\TestCase;

/**
 * Base class for tests that require a live DB connection.
 * Each test gets a fresh in-memory SQLite instance.
 */
abstract class DbTestCase extends TestCase
{
    protected SqliteTunel $tunel;

    protected function setUp(): void
    {
        $pdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $this->tunel = new SqliteTunel($pdo);
        Operation::setOperationTunel($this->tunel);
    }

    protected function tearDown(): void
    {
        $ref = new ReflectionProperty(Operation::class, 'operationTunel');
        $ref->setValue(null, null);
    }

    protected function createUsersTable(): void
    {
        $this->tunel->exec(
            'CREATE TABLE users (id TEXT PRIMARY KEY, name TEXT, email TEXT)'
        );
    }

    protected function seedUser(string $id, string $name, string $email): void
    {
        $this->tunel->getPdo()->prepare(
            'INSERT INTO users (id, name, email) VALUES (?, ?, ?)'
        )->execute([$id, $name, $email]);
    }
}
