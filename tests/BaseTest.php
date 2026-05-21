<?php

declare(strict_types=1);

namespace Roulette\Tests;

use Roulette\Base;

class BaseTest extends TestCase
{
    public function testIs(): void
    {
        $this->assertTrue(method_exists(Base::class, 'is'));

        $base = new Base();
        $this->assertTrue(Base::is($base), 'is, true');

        $fake = new \stdClass();
        $this->assertFalse(Base::is($fake), 'is, false');
    }

    public function testIsNot(): void
    {
        $this->assertTrue(method_exists(Base::class, 'isNot'));

        $base = new Base();
        $this->assertFalse(Base::isNot($base), 'isNot, true');
    }

    public function testCreate(): void
    {
        $this->assertTrue(method_exists(Base::class, 'create'));

        $base = Base::create();
        $this->assertInstanceOf(Base::class, $base, 'create, empty');

        $existing = new Base();
        $this->assertSame($existing, Base::create($existing), 'create, valid');

        $from = Base::create(null);
        $this->assertInstanceOf(Base::class, $from, 'create, invalid null');

        $from = Base::create(new \stdClass());
        $this->assertInstanceOf(Base::class, $from, 'create, invalid object');

        $from = Base::create(new \stdClass());
        $this->assertInstanceOf(Base::class, $from, 'create, invalid boolean');
    }
}
