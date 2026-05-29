<?php

declare(strict_types=1);

namespace Roulette\Tests\Validator;

use Roulette\Tests\TestCase;
use Roulette\Validator\Date;
use Roulette\Validator\DateTime;
use Roulette\Validator\Time;

class DateTest extends TestCase
{
    // --- Date ---

    public function testDateValid(): void
    {
        $v = new Date(null);
        $this->assertTrue($v->test('2024-01-15'));
        $this->assertTrue($v->test('2000-12-31'));
    }

    public function testDateInvalid(): void
    {
        $v = new Date(null);
        $this->assertFalse($v->test('15-01-2024'), 'wrong order');
        $this->assertFalse($v->test('2024/01/15'), 'wrong separator');
        $this->assertFalse($v->test('not-a-date'));
        $this->assertFalse($v->test(null));
        $this->assertFalse($v->test('2024-13-01'), 'month 13');
    }

    public function testDateCustomFormat(): void
    {
        $v = new Date('d/m/Y');
        $this->assertTrue($v->test('15/01/2024'));
        $this->assertFalse($v->test('2024-01-15'));
    }

    public function testDateMessage(): void
    {
        $v = new Date(null);
        $msg = $v->getMessage('bad');
        $this->assertStringContainsString('Y-m-d', $msg);
    }

    // --- DateTime ---

    public function testDateTimeValid(): void
    {
        $v = new DateTime(null);
        $this->assertTrue($v->test('2024-01-15 10:30:00'));
    }

    public function testDateTimeInvalid(): void
    {
        $v = new DateTime(null);
        $this->assertFalse($v->test('2024-01-15'));
        $this->assertFalse($v->test('not a datetime'));
        $this->assertFalse($v->test(null));
    }

    public function testDateTimeMessage(): void
    {
        $v = new DateTime(null);
        $msg = $v->getMessage('bad');
        $this->assertStringContainsString('Y-m-d H:i:s', $msg);
    }

    // --- Time ---

    public function testTimeValid(): void
    {
        $v = new Time(null);
        $this->assertTrue($v->test('10:30:00'));
        $this->assertTrue($v->test('00:00:00'));
        $this->assertTrue($v->test('23:59:59'));
    }

    public function testTimeInvalid(): void
    {
        $v = new Time(null);
        $this->assertFalse($v->test('25:00:00'), 'hour 25');
        $this->assertFalse($v->test('10:30'), 'missing seconds');
        $this->assertFalse($v->test(null));
    }

    public function testTimeMessage(): void
    {
        $v = new Time(null);
        $msg = $v->getMessage('bad');
        $this->assertStringContainsString('H:i:s', $msg);
    }
}
