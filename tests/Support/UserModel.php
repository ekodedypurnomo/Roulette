<?php

declare(strict_types=1);

namespace Roulette\Tests\Support;

use Roulette\Model;
use Roulette\Model\Prototype;

/**
 * Minimal test model backed by SQLite in-memory "users" table.
 * Schema: id TEXT PRIMARY KEY, name TEXT, email TEXT
 */
class UserModel extends Model
{
    static protected ?Prototype $prototype = null;

    static protected bool $useCache = false;
}

UserModel::prototype([
    'table'   => 'users',
    'primary' => 'id',
    'autoId'  => true,
    'fields'  => [
        ['name' => 'id', 'update' => false],
        'name',
        'email',
    ],
]);
