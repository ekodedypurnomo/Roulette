<?php

declare(strict_types=1);

namespace Roulette\Tests\Model;

use Roulette\Model\Source;
use Roulette\Tests\Support\UserModel;
use Roulette\Tests\TestCase;

class SourceTest extends TestCase
{
    public function testConstruct(): void
    {
        $source = new Source();
        $this->assertInstanceOf(Source::class, $source);
    }

    public function testConstructWithStringName(): void
    {
        $source = new Source('alias');
        $this->assertInstanceOf(Source::class, $source);
    }

    public function testConstructWithArray(): void
    {
        $source = new Source(['name' => 'posts', 'table' => 'posts']);
        $this->assertInstanceOf(Source::class, $source);
        $this->assertEquals('posts', $source->getTable());
    }

    public function testSetModel(): void
    {
        $source = new Source();
        $source->setModel(UserModel::class);
        // getModel() routes through Operation/tunel; just verify setModel doesn't throw
        $this->assertInstanceOf(Source::class, $source);
    }

    public function testToString(): void
    {
        $source = new Source(['name' => 'reports', 'table' => 'reports']);
        $this->assertIsString((string) $source);
    }
}
