<?php

declare(strict_types=1);

namespace Roulette\Tests\Query;

use Roulette\Query\Operation;
use Roulette\Tunel\Codeigniter3;
use Roulette\Tests\TestCase;

class OperationTest extends TestCase
{
    private Operation $operation;

    private function resetLog(): void
    {
        $ref = new \ReflectionProperty(Operation::class, 'operations');
        $ref->setValue(null, []);
    }

    protected function setUp(): void
    {
        $this->operation = new Operation(new Codeigniter3());
        Operation::disableLog();
        $this->resetLog();
    }

    protected function tearDown(): void
    {
        Operation::disableLog();
        $this->resetLog();
    }

    public function testEnableDisableOperationLog(): void
    {
        $this->assertTrue(method_exists(Operation::class, 'enableLog'));
        $this->assertTrue(method_exists(Operation::class, 'disableLog'));
        $this->assertTrue(method_exists(Operation::class, 'isLogging'));

        $this->assertFalse(Operation::isLogging(), 'default');
        $this->assertSame(Operation::class, Operation::enableLog(), 'enabling returns class');
        $this->assertTrue(Operation::isLogging(), 'enabled');
        $this->assertSame(Operation::class, Operation::disableLog(), 'disabling returns class');
        $this->assertFalse(Operation::isLogging(), 'disabled');
    }

    public function testSetGetTunel(): void
    {
        $this->assertTrue(method_exists(Operation::class, 'setOperationTunel'));
        $this->assertTrue(method_exists(Operation::class, 'getOperationTunel'));

        $tunnel = new Codeigniter3();
        $this->assertSame(Operation::class, Operation::setOperationTunel($tunnel), 'set returns class');
        $this->assertSame(Operation::class, Operation::tunel($tunnel), 'tunel set returns class');
        $this->assertSame($tunnel, Operation::getOperationTunel(), 'getOperationTunel');
        $this->assertSame($tunnel, Operation::tunel(), 'tunel get');
    }

    public function testGetLog(): void
    {
        $this->assertTrue(method_exists(Operation::class, 'getLog'));

        Operation::enableLog();
        $this->assertEmpty(Operation::getLog(), 'default');

        $op = new Operation(new Codeigniter3());
        Operation::add($op);
        $this->assertNotEmpty(Operation::getLog(), 'add');
        $this->assertSame($op, Operation::getLastLog(), 'getLastOperation');
    }

    public function testHasPublicProperty(): void
    {
        $this->assertTrue(property_exists(Operation::class, 'option'));
        $this->assertTrue(property_exists(Operation::class, 'error'));
        $this->assertTrue(property_exists(Operation::class, 'query'));
        $this->assertTrue(property_exists(Operation::class, 'affectedRows'));
        $this->assertTrue(property_exists(Operation::class, 'result'));
        $this->assertTrue(property_exists(Operation::class, 'executeTime'));
    }

    public function testIsSuccess(): void
    {
        $this->assertTrue(method_exists(Operation::class, 'isSuccess'));

        $this->assertFalse($this->operation->isSuccess(), 'without tunnel');
        $this->operation->execute();
        $this->assertFalse($this->operation->isSuccess(), 'without tunnel execute');

        $this->operation->success = true;
        $this->assertTrue($this->operation->isSuccess(), 'successful');
    }

    public function testGetRecords(): void
    {
        $this->assertTrue(method_exists(Operation::class, 'getRecords'));

        $this->assertIsArray($this->operation->getRecords(), 'value');
    }
}
