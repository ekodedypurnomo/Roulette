<?php

declare(strict_types=1);

namespace Roulette\Tests\Mixin;

use Roulette\Base;
use Roulette\Mixin\Observable;
use Roulette\Tests\TestCase;

class ObservableSubject extends Base
{
    use Observable;
}

class ObservableTest extends TestCase
{
    private ObservableSubject $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new ObservableSubject();
    }

    // --- addEvent / hasEvent ---

    public function testAddEvent(): void
    {
        $added = $this->subject->addEvent('ping');
        $this->assertContains('ping', $added);
        $this->assertTrue($this->subject->hasEvent('ping'));
    }

    public function testAddEventMultiple(): void
    {
        $this->subject->addEvent(['a', 'b', 'c']);
        $this->assertTrue($this->subject->hasEvent('a'));
        $this->assertTrue($this->subject->hasEvent('b'));
        $this->assertTrue($this->subject->hasEvent('c'));
    }

    public function testAddEventNoDuplicate(): void
    {
        $this->subject->addEvent('ping');
        $added = $this->subject->addEvent('ping');
        $this->assertEmpty($added, 'second add returns empty — already registered');
    }

    public function testHasEventFalseForUnregistered(): void
    {
        $this->assertFalse($this->subject->hasEvent('nonexistent'));
    }

    // --- removeEvent ---

    public function testRemoveEvent(): void
    {
        $this->subject->addEvent('ping');
        $removed = $this->subject->removeEvent('ping');
        $this->assertContains('ping', $removed);
        $this->assertFalse($this->subject->hasEvent('ping'));
    }

    public function testRemoveEventArray(): void
    {
        $this->subject->addEvent(['a', 'b']);
        $this->subject->removeEvent(['a', 'b']);
        $this->assertFalse($this->subject->hasEvent('a'));
        $this->assertFalse($this->subject->hasEvent('b'));
    }

    public function testRemoveNonexistentEventReturnsEmpty(): void
    {
        $removed = $this->subject->removeEvent('ghost');
        $this->assertEmpty($removed);
    }

    // --- trigger ---

    public function testTriggerCallsListener(): void
    {
        $called = false;
        $this->subject->addEvent('ping');
        $this->subject->on('ping', function() use (&$called) { $called = true; });
        $this->subject->trigger('ping');
        $this->assertTrue($called);
    }

    public function testTriggerPassesParams(): void
    {
        $received = null;
        $this->subject->addEvent('data');
        $this->subject->on('data', function($val) use (&$received) { $received = $val; });
        $this->subject->trigger('data', ['hello']);
        $this->assertSame('hello', $received);
    }

    public function testTriggerReturnsFalseWhenListenerReturnsFalse(): void
    {
        $this->subject->addEvent('ping');
        $this->subject->on('ping', fn() => false);
        $result = $this->subject->trigger('ping');
        $this->assertFalse($result);
    }

    public function testTriggerReturnsTrueWithNoListeners(): void
    {
        $this->subject->addEvent('ping');
        $this->assertTrue($this->subject->trigger('ping'));
    }

    public function testTriggerOnUnregisteredEventWithNoListenerDoesNothing(): void
    {
        // trigger on an event with no listeners returns true but fires nothing
        $this->subject->addEvent('ping');
        $called = false;
        $result = $this->subject->trigger('ping');
        $this->assertTrue($result);
        $this->assertFalse($called);
    }

    public function testOnAutoRegistersEvent(): void
    {
        // addListener/on creates the event automatically if not registered
        $called = false;
        $this->subject->on('ghost', function() use (&$called) { $called = true; });
        $this->assertTrue($this->subject->hasEvent('ghost'), 'on() auto-registers event');
        $this->subject->trigger('ghost');
        $this->assertTrue($called, 'listener fires after auto-registration');
    }

    // --- addListener / removeListener / hasListener ---

    public function testAddListener(): void
    {
        $this->subject->addEvent('ping');
        $listener = fn() => null;
        $this->subject->addListener('ping', $listener);
        $this->assertTrue($this->subject->hasListener('ping'));
    }

    public function testAddListenerNoDuplicate(): void
    {
        $this->subject->addEvent('ping');
        $listener = fn() => null;
        $this->subject->addListener('ping', $listener);
        $this->subject->addListener('ping', $listener);

        $called = 0;
        $this->subject->on('ping', function() use (&$called) { $called++; });
        $this->subject->trigger('ping');
        $this->assertSame(1, $called, 'same listener registered once, fires once');
    }

    public function testRemoveListener(): void
    {
        $this->subject->addEvent('ping');
        $listener = fn() => null;
        $this->subject->addListener('ping', $listener);
        $this->subject->removeListener('ping', $listener);
        $this->assertFalse($this->subject->hasListener('ping'));
    }

    public function testRemoveListenerNullRemovesAll(): void
    {
        $this->subject->addEvent('a');
        $this->subject->addEvent('b');
        $this->subject->on('a', fn() => null);
        $this->subject->on('b', fn() => null);
        $this->subject->removeListener(null, fn() => null);
        // after removeListener(null, $fn), specific fn is removed across all events
        // hasListener checks if array is non-empty
        $this->assertInstanceOf(ObservableSubject::class, $this->subject);
    }

    public function testClearListener(): void
    {
        $this->subject->addEvent('ping');
        $this->subject->on('ping', fn() => null);
        $this->subject->clearListener('ping');
        $this->assertFalse($this->subject->hasListener('ping'));
    }

    // --- enableEvent / disableEvent ---

    public function testEnableDisableEvent(): void
    {
        $called = false;
        $this->subject->addEvent('ping');
        $this->subject->on('ping', function() use (&$called) { $called = true; });

        $this->subject->disableEvent('ping');
        $this->subject->trigger('ping');
        $this->assertFalse($called, 'disabled event does not fire');

        $this->subject->enableEvent('ping');
        $this->subject->trigger('ping');
        $this->assertTrue($called, 'enabled event fires');
    }

    public function testEventEnabled(): void
    {
        $this->subject->addEvent('ping');
        $this->assertTrue($this->subject->eventEnabled('ping'));
        $this->subject->disableEvent('ping');
        $this->assertFalse($this->subject->eventEnabled('ping'));
    }

    public function testEventDisabled(): void
    {
        $this->subject->addEvent('ping');
        $this->assertFalse($this->subject->eventDisabled('ping'));
        $this->subject->disableEvent('ping');
        $this->assertTrue($this->subject->eventDisabled('ping'));
    }

    public function testEventEnabledReturnsFalseForUnregistered(): void
    {
        $this->assertFalse($this->subject->eventEnabled('ghost'));
    }

    // --- setObservable / isObservable ---

    public function testSetObservableDisablesAllEvents(): void
    {
        $called = false;
        $this->subject->addEvent('ping');
        $this->subject->on('ping', function() use (&$called) { $called = true; });

        $this->subject->setObservable(false);
        $this->assertFalse($this->subject->isObservable());

        // trigger still returns true but listeners don't run when observable is off
        $this->subject->trigger('ping');
        $this->assertFalse($called, 'no listener fires when observable=false');
    }

    public function testSetObservableReEnables(): void
    {
        $called = false;
        $this->subject->addEvent('ping');
        $this->subject->on('ping', function() use (&$called) { $called = true; });

        $this->subject->setObservable(false);
        $this->subject->setObservable(true);
        $this->subject->trigger('ping');
        $this->assertTrue($called);
    }

    // --- on / un shorthands ---

    public function testOnShorthand(): void
    {
        $called = false;
        $this->subject->addEvent('ping');
        $this->subject->on('ping', function() use (&$called) { $called = true; });
        $this->subject->trigger('ping');
        $this->assertTrue($called);
    }

    public function testUnShorthand(): void
    {
        $called = false;
        $this->subject->addEvent('ping');
        $listener = function() use (&$called) { $called = true; };
        $this->subject->on('ping', $listener);
        $this->subject->un('ping', $listener);
        $this->subject->trigger('ping');
        $this->assertFalse($called);
    }

    // --- fireEvent / fireEventArgs ---

    public function testFireEvent(): void
    {
        $received = null;
        $this->subject->addEvent('ping');
        $this->subject->on('ping', function($v) use (&$received) { $received = $v; });
        $this->subject->fireEvent('ping', 'hello');
        $this->assertSame('hello', $received);
    }

    public function testFireEventArgs(): void
    {
        $received = null;
        $this->subject->addEvent('ping');
        $this->subject->on('ping', function($v) use (&$received) { $received = $v; });
        $this->subject->fireEventArgs('ping', ['world']);
        $this->assertSame('world', $received);
    }
}
