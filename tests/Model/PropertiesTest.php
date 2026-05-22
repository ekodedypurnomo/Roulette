<?php

declare(strict_types=1);

namespace Roulette\Tests\Model;

use Roulette\Model;
use Roulette\Model\Properties;
use Roulette\Tests\TestCase;

class PropertiesTest extends TestCase
{
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(Properties::class));
    }

    public function testGetProperties(): void
    {
        // getProperties() returns void — just verify it exists and is callable
        $this->assertTrue(method_exists(Properties::class, 'getProperties'));
    }
}
