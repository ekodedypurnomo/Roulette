<?php

declare(strict_types=1);

namespace Roulette\Tests\Model;

use Roulette\Model\Policy;
use Roulette\Tests\TestCase;

class PolicyTest extends TestCase
{
    public function testConstruct(): void
    {
        $policy = new Policy('edit');
        $this->assertInstanceOf(Policy::class, $policy);
    }

    public function testConstructWithAssertions(): void
    {
        $policy = new Policy('view', fn() => false, fn() => false);
        $this->assertCount(2, $policy->getAssertions(), 'two assertions from constructor');
    }

    public function testAddAssertion(): void
    {
        $policy = new Policy('edit');
        $policy->addAssertion(fn() => true);
        $this->assertCount(1, $policy->getAssertions(), 'one assertion added');
    }

    public function testGetAssetions(): void
    {
        $policy = new Policy('edit');
        $this->assertIsArray($policy->getAssertions(), 'returns array');
        $this->assertEmpty($policy->getAssertions(), 'empty by default');
    }

    public function testAssertAllDenyReturnTrue(): void
    {
        // assert() returns true when no assertion denies (returns true)
        $policy = new Policy('edit', fn() => false, fn() => false);
        $this->assertTrue($policy->assert(), 'all deny=false → allowed');
    }

    public function testAssertOneDenyReturnsFalse(): void
    {
        // assert() returns false when any assertion denies (returns true)
        $policy = new Policy('edit', fn() => false, fn() => true);
        $this->assertFalse($policy->assert(), 'one deny=true → blocked');
    }

    public function testAssertNoAssertions(): void
    {
        $policy = new Policy('view');
        $this->assertTrue($policy->assert(), 'no assertions → allowed');
    }

    public function testReset(): void
    {
        $policy = new Policy('view', fn() => true);
        $this->assertCount(1, $policy->getAssertions(), 'before reset');
        $policy->reset();
        $this->assertCount(0, $policy->getAssertions(), 'after reset');
    }

    public function testCreateFactory(): void
    {
        $policy = Policy::create('test');
        $this->assertInstanceOf(Policy::class, $policy);
    }
}
