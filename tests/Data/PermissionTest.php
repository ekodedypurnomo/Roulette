<?php

declare(strict_types=1);

namespace Roulette\Tests\Data;

use Roulette\Data\Permission;
use Roulette\Tests\TestCase;

class PermissionTest extends TestCase
{
    public function testCreate(): void
    {
        $this->assertTrue(method_exists(Permission::class, 'create'));

        $obj = Permission::create();
        $this->assertInstanceOf(Permission::class, $obj, 'valid');

        $siui = ['select' => true, 'insert' => true, 'update' => false, 'delete' => false];
        $obj = Permission::createFromHex('c');
        $this->assertEquals($siui, $obj->getPermission(), 'createFromHex hex c');

        $obj = Permission::createFromHex(12);
        $this->assertEquals($siui, $obj->getPermission(), 'createFromHex int 12');

        $obj = Permission::createFromHex('g');
        $this->assertIsArray($obj->getPermission(), 'createFromHex invalid g');
        $this->assertCount(4, $obj->getPermission(), 'createFromHex invalid g count');

        $obj = Permission::createFromHex();
        $this->assertIsArray($obj->getPermission(), 'createFromHex default');
        $this->assertCount(4, $obj->getPermission(), 'createFromHex default count');

        $siui2 = ['select' => true, 'insert' => false, 'update' => true, 'delete' => false];
        $obj = Permission::createFromDec('10');
        $this->assertEquals($siui2, $obj->getPermission(), 'createFromDec string 10');

        $obj = Permission::createFromDec(10);
        $this->assertEquals($siui2, $obj->getPermission(), 'createFromDec int 10');

        $obj = Permission::createFromDec('g');
        $this->assertIsArray($obj->getPermission(), 'createFromDec invalid');
        $this->assertCount(4, $obj->getPermission(), 'createFromDec invalid count');

        $obj = Permission::createFromDec();
        $this->assertIsArray($obj->getPermission(), 'createFromDec default');
        $this->assertCount(4, $obj->getPermission(), 'createFromDec default count');

        $obj = Permission::createFromBin('1010');
        $this->assertEquals($siui2, $obj->getPermission(), 'createFromBin 1010');

        $obj = Permission::createFromBin('g');
        $this->assertIsArray($obj->getPermission(), 'createFromBin invalid');
        $this->assertCount(4, $obj->getPermission(), 'createFromBin invalid count');

        $obj = Permission::createFromBin();
        $this->assertIsArray($obj->getPermission(), 'createFromBin default');
        $this->assertCount(4, $obj->getPermission(), 'createFromBin default count');
    }

    public function testGetPermission(): void
    {
        $this->assertTrue(method_exists(Permission::class, 'getPermission'));

        $obj = Permission::create('b');
        $this->assertIsArray($obj->getPermission(), 'valid hex b');

        $obj = Permission::create(1);
        $this->assertIsArray($obj->getPermission(), 'int becomes dec');

        $obj = Permission::create();
        $this->assertIsArray($obj->getPermission(), 'default');
        $this->assertCount(4, $obj->getPermission(), 'default count');
    }

    public function testSetPermission(): void
    {
        $this->assertTrue(method_exists(Permission::class, 'setPermission'));

        $obj = Permission::create();
        $obj->setPermission([false, true, true, false]);
        $this->assertEquals(
            ['select' => false, 'insert' => true, 'update' => true, 'delete' => false],
            $obj->getPermission(),
            'bool array'
        );

        $obj->setPermission(['delete' => 0, 'select' => 1, 'insert' => 1, 'update' => 1]);
        $this->assertEquals(
            ['select' => true, 'insert' => true, 'update' => true, 'delete' => false],
            $obj->getPermission(),
            'assoc array'
        );

        $obj->setPermission([1, 1, 1, 0]);
        $this->assertEquals(
            ['select' => true, 'insert' => true, 'update' => true, 'delete' => false],
            $obj->getPermission(),
            'int array'
        );

        $obj->setPermission(1);
        $this->assertEquals(
            ['select' => false, 'insert' => false, 'update' => false, 'delete' => true],
            $obj->getPermission(),
            'integer'
        );

        $obj->setPermission('f');
        $this->assertEquals(
            ['select' => true, 'insert' => true, 'update' => true, 'delete' => true],
            $obj->getPermission(),
            'hex f'
        );
    }

    public function testSetGetSelectPermission(): void
    {
        $this->assertTrue(method_exists(Permission::class, 'setSelectPermission'));
        $this->assertTrue(method_exists(Permission::class, 'getSelectPermission'));

        $obj = Permission::create('a');
        $obj->setSelectPermission(false);
        $this->assertFalse($obj->getSelectPermission(), 'false');

        $obj->setSelectPermission(true);
        $this->assertTrue($obj->getSelectPermission(), 'true');

        $obj->setSelectPermission('false');
        $this->assertIsBool($obj->getSelectPermission(), 'string coerced to bool');

        $obj->setSelectPermission(1);
        $this->assertIsBool($obj->getSelectPermission(), 'int coerced to bool');

        $obj = Permission::create('b');
        $this->assertTrue($obj->getSelectPermission(), 'b select');

        $obj = Permission::create();
        $this->assertFalse($obj->getSelectPermission(), 'default select');
    }

    public function testSetGetInsertPermission(): void
    {
        $this->assertTrue(method_exists(Permission::class, 'setInsertPermission'));
        $this->assertTrue(method_exists(Permission::class, 'getInsertPermission'));

        $obj = Permission::create('a');
        $obj->setInsertPermission(false);
        $this->assertFalse($obj->getInsertPermission(), 'false');

        $obj->setInsertPermission(true);
        $this->assertTrue($obj->getInsertPermission(), 'true');

        $obj->setInsertPermission('false');
        $this->assertIsBool($obj->getInsertPermission(), 'string coerced to bool');

        $obj->setInsertPermission(1);
        $this->assertIsBool($obj->getInsertPermission(), 'int coerced to bool');

        $obj = Permission::create('f');
        $this->assertTrue($obj->getInsertPermission(), 'f insert');

        $obj = Permission::create();
        $this->assertFalse($obj->getInsertPermission(), 'default insert');
    }

    public function testSetGetUpdatePermission(): void
    {
        $this->assertTrue(method_exists(Permission::class, 'setUpdatePermission'));
        $this->assertTrue(method_exists(Permission::class, 'getUpdatePermission'));

        $obj = Permission::create('a');
        $obj->setUpdatePermission(false);
        $this->assertFalse($obj->getUpdatePermission(), 'false');

        $obj->setUpdatePermission(true);
        $this->assertTrue($obj->getUpdatePermission(), 'true');

        $obj->setUpdatePermission('false');
        $this->assertIsBool($obj->getUpdatePermission(), 'string coerced to bool');

        $obj->setUpdatePermission(1);
        $this->assertIsBool($obj->getUpdatePermission(), 'int coerced to bool');

        $obj = Permission::create('f');
        $this->assertTrue($obj->getUpdatePermission(), 'f update');

        $obj = Permission::create();
        $this->assertFalse($obj->getUpdatePermission(), 'default update');
    }

    public function testSetGetDeletePermission(): void
    {
        $this->assertTrue(method_exists(Permission::class, 'setDeletePermission'));
        $this->assertTrue(method_exists(Permission::class, 'getDeletePermission'));

        $obj = Permission::create('a');
        $obj->setDeletePermission(false);
        $this->assertFalse($obj->getDeletePermission(), 'false');

        $obj->setDeletePermission(true);
        $this->assertTrue($obj->getDeletePermission(), 'true');

        $obj->setDeletePermission('false');
        $this->assertIsBool($obj->getDeletePermission(), 'string coerced to bool');

        $obj->setDeletePermission(1);
        $this->assertIsBool($obj->getDeletePermission(), 'int coerced to bool');

        $obj = Permission::create('f');
        $this->assertTrue($obj->getDeletePermission(), 'f delete');

        $obj = Permission::create();
        $this->assertFalse($obj->getDeletePermission(), 'default delete');
    }

    public function testToHex(): void
    {
        $this->assertTrue(method_exists(Permission::class, 'toHex'));

        $obj = Permission::create('f');
        $this->assertEquals('F', $obj->toHex(), 'f -> F');

        $obj = Permission::create();
        $this->assertEquals('0', $obj->toHex(), 'default -> 0');

        $obj = Permission::create(8);
        $this->assertEquals('8', $obj->toHex(), 'int 8 -> 8');
    }

    public function testToDec(): void
    {
        $this->assertTrue(method_exists(Permission::class, 'toDec'));

        $obj = Permission::create('a');
        $this->assertEquals(10, $obj->toDec(), 'a -> 10');

        $obj = Permission::create();
        $this->assertEquals(0, $obj->toDec(), 'default -> 0');

        $obj = Permission::create(8);
        $this->assertEquals(8, $obj->toDec(), 'int 8 -> 8');

        $obj = Permission::create('8');
        $this->assertEquals(8, $obj->toDec(), 'string 8 -> 8');
    }

    public function testToBin(): void
    {
        $this->assertTrue(method_exists(Permission::class, 'toBin'));

        $obj = Permission::create('a');
        $this->assertEquals('1010', $obj->toBin(), 'a -> 1010');

        $obj = Permission::create();
        $this->assertEquals('0', $obj->toBin(), 'default -> 0');

        $obj = Permission::create(8);
        $this->assertEquals('1000', $obj->toBin(), 'int 8 -> 1000');

        $obj = Permission::create('8');
        $this->assertEquals('1000', $obj->toBin(), 'string 8 -> 1000');
    }
}
