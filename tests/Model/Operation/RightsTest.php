<?php

declare(strict_types=1);

namespace Roulette\Tests\Model\Operation;

use Roulette\Model\Operation\Rights;
use Roulette\Tests\TestCase;

class RightsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (!class_exists('Roulette\Model\Operation\Permission')) {
            $this->markTestSkipped('Roulette\Model\Operation\Permission class not yet implemented.');
        }
    }

    public function testCreateFromString(): void
    {
        $rights = Rights::create('f00');
        $this->assertInstanceOf(Rights::class, $rights);
    }

    public function testOwnerFullRights(): void
    {
        $rights = Rights::create('f00');
        $this->assertTrue((bool) $rights->ownerCanRead(), 'owner can read');
        $this->assertTrue((bool) $rights->ownerCanCreate(), 'owner can create');
        $this->assertTrue((bool) $rights->ownerCanUpdate(), 'owner can update');
        $this->assertTrue((bool) $rights->ownerCanDestroy(), 'owner can destroy');
    }

    public function testOwnerNoRights(): void
    {
        $rights = Rights::create('000');
        $this->assertFalse((bool) $rights->ownerCanRead(), 'owner cannot read');
        $this->assertFalse((bool) $rights->ownerCanCreate(), 'owner cannot create');
    }

    public function testGroupRights(): void
    {
        $rights = Rights::create('ff0');
        $this->assertTrue((bool) $rights->groupCanRead(), 'group can read');
        $this->assertFalse((bool) $rights->otherCanRead(), 'other cannot read');
    }

    public function testToString(): void
    {
        $rights = Rights::create('f80');
        $str = $rights->toString();
        $this->assertIsString($str);
        $this->assertEquals(3, strlen($str), 'toString is 3 hex chars');
    }

    public function testCreateFromArray(): void
    {
        $rights = Rights::create(['owner' => 'f', 'group' => '8', 'other' => '0']);
        $this->assertTrue((bool) $rights->ownerCanRead());
        $this->assertTrue((bool) $rights->groupCanRead());
        $this->assertFalse((bool) $rights->otherCanRead());
    }

    public function testShortStringPaddedWithZeros(): void
    {
        $rights = Rights::create('f');
        $this->assertTrue((bool) $rights->ownerCanRead());
        $this->assertFalse((bool) $rights->groupCanRead(), 'group padded to 0');
    }
}
