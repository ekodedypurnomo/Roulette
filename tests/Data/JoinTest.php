<?php

declare(strict_types=1);

namespace Roulette\Tests\Data;

use Roulette\Data\Join;
use Roulette\Tests\TestCase;

class JoinTest extends TestCase
{
    public function testConstruct(): void
    {
        $join = new Join();
        $this->assertInstanceOf(Join::class, $join);
    }

    public function testIdentifyNonArray(): void
    {
        $join = new Join(['identifier' => '/^user_/']);
        $this->assertNull($join->identify('not-array'), 'non-array returns null');
        $this->assertNull($join->identify(null), 'null returns null');
    }

    public function testIdentifyWithCallable(): void
    {
        $join = new Join(['identifier' => fn($key) => str_starts_with($key, 'user_')]);
        $data = ['user_name' => 'Alice', 'post_title' => 'Hello', 'user_email' => 'a@b.com'];

        $result = $join->identify($data);
        $this->assertArrayHasKey('user_name', $result);
        $this->assertArrayHasKey('user_email', $result);
        $this->assertArrayNotHasKey('post_title', $result);
    }

    public function testIdentifyWithRegexp(): void
    {
        $join = new Join(['identifier' => '/^pfx_/']);
        $data = ['pfx_id' => 1, 'pfx_name' => 'Bob', 'other' => 99];

        $result = $join->identify($data);
        $this->assertArrayHasKey('pfx_id', $result);
        $this->assertArrayHasKey('pfx_name', $result);
        $this->assertArrayNotHasKey('other', $result);
    }

    public function testResolveNonArray(): void
    {
        $join = new Join(['resolver' => fn($key) => $key]);
        $this->assertNull($join->resolve('not-array'), 'non-array returns null');
    }

    public function testResolveWithCallable(): void
    {
        $join = new Join([
            'identifier' => fn($key) => str_starts_with($key, 'u_'),
            'resolver'   => fn($key) => substr($key, 2),
        ]);
        $data = ['u_name' => 'Carol', 'u_email' => 'c@d.com', 'age' => 25];

        $result = $join->fetchData($data);
        $this->assertArrayHasKey('name', $result, 'u_ prefix stripped');
        $this->assertArrayHasKey('email', $result, 'u_ prefix stripped');
        $this->assertArrayNotHasKey('age', $result, 'non-prefixed excluded');
    }

    public function testFetchData(): void
    {
        // fetchData = identify + resolve; without a resolver, resolve returns {}
        // Use a callable resolver that passes keys through as-is
        $join = new Join([
            'identifier' => '/^x_/',
            'resolver'   => fn($key) => $key,
        ]);
        $data = ['x_id' => 1, 'name' => 'Bob'];

        $result = $join->fetchData($data);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('x_id', $result);
        $this->assertArrayNotHasKey('name', $result);
    }

    public function testIdentifyNoMatchReturnsEmpty(): void
    {
        $join = new Join(['identifier' => '/^NOMATCH_/']);
        $data = ['name' => 'Dave', 'email' => 'd@e.com'];

        $result = $join->identify($data);
        $this->assertIsArray($result);
        $this->assertEmpty($result, 'no matching fields');
    }
}
