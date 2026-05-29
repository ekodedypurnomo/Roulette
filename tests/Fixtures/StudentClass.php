<?php

declare(strict_types=1);

namespace Roulette\Tests\Fixtures;

use Roulette\Model;
use Roulette\Model\Prototype;

class StudentClass extends Model
{
    static protected ?Prototype $prototype = null;
}

StudentClass::prototype([
    'table'   => 'class',
    'primary' => 'id',
    'fields'  => [
        ['name' => 'id', 'display' => 'ID', 'update' => false, 'unique' => true],
        'name',
    ],
    'associations' => [
        ['name' => 'students', 'type' => 'hasMany', 'model' => 'Student', 'field' => 'class'],
    ],
]);
