<?php

declare(strict_types=1);

/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Roulette\Mixin;

/**
 * ##Observable
 * A class inherit to \Roulette\Base will be able to has one or more events
 *
 *      $person = new Person(array(
 *          'listener'=>array(
 *              'sing'=>function(){
 *                  echo "la la la laa la ~";
 *              }
 *          )
 *      ));
 *
 *      // easy to trigger it
 *      $person->trigger('sing'); // will echo 'la la la laa la ~';
 *
 *      // easy to add listener or remove it
 *      $singing = function(){
 *          echo "you are the one that i loved ~";
 *      }
 *      $person->on('sing', $singing);
 *      $person->removeListener('sing', $singing);
 *
 * @package Roulette\Mixin
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
trait Observable
{
    /**
     * Array of listeners in this class
     * @var array
     */
    protected array $listeners = [];

    /**
     * Array of event to be captured
     * @var array
     */
    protected array $events = [];

    /**
     * Event capturing status, `false` mean disable event capturing
     * @var bool
     */
    protected bool $runEvents = true;

    /**
     * Sorthand for \Roulette\Base::addListener()
     *
     * @param string|null $eventName Event name or an Array event listener pairs
     * @param mixed $listener A callable function
     * @return static
     */
    function on(?string $eventName = null, mixed $listener = null): static
    {
        return $this->addListener($eventName, $listener);
    }

    /**
     * Sorthand for \Roulette\Base::removeListener()
     *
     * @param string|array|null $eventName Events which listener exists
     * @param mixed $listener Listener to be removed from event listeners
     * @return static
     */
    function un(string|array|null $eventName = null, mixed $listener = null): static
    {
        return $this->removeListener($eventName, $listener);
    }

    /**
     * Trigger an event to be executed
     *
     *      Example:
     *      class Person extends \Roulette\Base
     *      {
     *          function __construct($config){
     *              $this->addEvent('sing');
     *          }
     *      }
     *
     *      $person = new Person();
     *      $person->trigger('sing'); // will executed any `sing` listeners
     *
     * @param string|null $eventName String event name to be triggered
     * @param array $params Array of arguments will pass to listeners
     * @return bool `true` if the listeners executed, `false` if the listeners return false
     */
    function trigger(?string $eventName = null, array $params = []): bool
    {
        if ($this->hasEvent($eventName) && $this->eventEnabled($eventName))
        {
            if (array_key_exists($eventName, $this->listeners) && is_array($this->listeners[$eventName]))
            {
                foreach ($this->listeners[$eventName] as $listener)
                {
                    if (is_callable($listener))
                    {
                        if (call_user_func_array($listener, $params) === false) return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Fire the specified event with the passed parameter list
     * @param string|null $eventName
     * @param array|null $params
     * @return bool
     */
    function fireEventArgs(?string $eventName = null, ?array $params = null): bool
    {
        return $this->trigger($eventName, $params ?? []);
    }

    /**
     * Fire the specified event
     * @param string|null $eventName
     * @return bool
     */
    function fireEvent(?string $eventName = null): bool
    {
        $args = func_get_args();
        array_shift($args);
        return $this->trigger($eventName, $args);
    }

    /**
     * Add one or more events to the events list
     *
     *      Example:
     *      $person->addEvent('sing'); // return array('sing')
     *      $person->addEvent(array('run','dance','swim'));
     *      // return array('run','swim'), because event `dance` is already registered
     *
     * @param string|array|null $event String event name or array of events name
     * @return array Added events
     */
    function addEvent(string|array|null $event = null): array
    {
        $added = [];
        if (!is_array($event)) $event = [$event];

        foreach ($event as $eventName)
        {
            if (array_key_exists($eventName, $this->events) || !is_string($eventName)) continue;

            $this->events[$eventName]    = true;
            $this->listeners[$eventName] = [];
            $added[] = $eventName;
        }
        return $added;
    }

    /**
     * Check if an event is already exist.
     * Disabled event mean exist
     *
     *      Example:
     *      $person->hasEvent('sing'); // return false;
     *      $person->addEvent('sing');
     *      $person->hasEvent('sing'); // return true;
     *
     * @param string|null $eventName Event name
     * @return bool Has event status
     */
    function hasEvent(?string $eventName = null): bool
    {
        return array_key_exists($eventName, $this->events);
    }

    /**
     * Remove event from its events capturing
     *
     *      Example:
     *      $person->removeEvent('sing');
     *      // person no longer can 'sing'
     *      $person->trigger('sing');
     *      // do nothing
     *
     *      // using array
     *      $person->removeEvent(array('event1', 'event2'));
     *
     * @param string|array|null $event A name of event to be remove from its object
     * @return array Removed event names
     */
    function removeEvent(string|array|null $event = null): array
    {
        $removed = [];
        if (!is_array($event)) $event = [$event];

        foreach ($event as $eventName)
        {
            if (is_string($eventName) && array_key_exists($eventName, $this->events))
            {
                unset($this->events[$eventName]);
                $removed[] = $eventName;
            }
        }
        return $removed;
    }

    /**
     * Check if class using events capturing (is enabled or not)
     * @return bool enabled status
     */
    function isObservable(): bool
    {
        return $this->runEvents;
    }

    /**
     * Enabling/disabling events capturing
     *
     *      Example:
     *      $person->setObservable(false); // disable all event capturing
     *      $person->trigger('sing'); // do nothing
     *      $person->setObservable(true);
     *      $person->trigger('sing'); // doing 'sing' function
     *
     * @param bool $observable Enabled status, default `true`
     * @return static
     */
    function setObservable(bool $observable = true): static
    {
        $this->runEvents = $observable;
        return $this;
    }

    /**
     * Enable capturing of the event
     *
     *      Example:
     *      $person->enableEvent('sing', false); // person no longer can 'sing'
     *      $person->trigger('sing'); // do nothing
     *      $person->enableEvent('sing', true); // now person can sing
     *      $person->trigger('sing'); // executes
     *
     * @param string|null $eventName A name of event to be enabled
     * @param bool $enable Enabled status, default `true`
     * @return static
     */
    function enableEvent(?string $eventName = null, bool $enable = true): static
    {
        if (array_key_exists($eventName, $this->events))
        {
            $this->events[$eventName] = $enable;
        }
        return $this;
    }

    /**
     * Get enabled status of an event.
     *
     *      Example:
     *      $person->enableEvent('sing');
     *      $person->eventEnabled('sing'); // return: true
     *      $person->eventEnabled('walk'); // return: false, because event `walk` doesnt exist
     *
     * @param string|null $eventName Event name
     * @return bool Enabled status, return `false` if event doesnt exist
     */
    function eventEnabled(?string $eventName = null): bool
    {
        return $this->hasEvent($eventName) && (bool) $this->events[$eventName];
    }

    /**
     * Disable capturing of the event
     *
     *      Example:
     *      $person->disableEvent('sing'); // person no longer can 'sing' till the event enabled
     *      $person->trigger('sing'); // do nothing
     *      $person->disableEvent('sing', false); // now person can sing
     *      $person->trigger('sing'); // executes
     *
     * @param string|null $eventName A name of event to be disabled
     * @param bool $disable Disabled status, default `true`
     * @return static
     */
    function disableEvent(?string $eventName = null, bool $disable = true): static
    {
        if ($this->hasEvent($eventName))
        {
            $this->events[$eventName] = !$disable;
        }
        return $this;
    }

    /**
     * Get disabled status of an event.
     *
     *      Example:
     *      $person->disableEvent('sing', true);
     *      $person->eventDisabled('sing'); // return: true
     *      $person->eventDisabled('walk'); // return: true, because event `walk` doesnt exist
     *
     * @param string|null $eventName Event name
     * @return bool Disabled status, return `true` if event doesnt exist
     */
    function eventDisabled(?string $eventName = null): bool
    {
        return !$this->eventEnabled($eventName);
    }

    /**
     * Add listener on one or more events
     * will add event if event doesnt exist on event list
     *
     *      Example:
     *      $person->addListener('walk', $person->lazy);
     *      $person->addListener(array(
     *          'walk' => $person->lazy,
     *          'dance' => $person->lazy
     *      ));
     *
     * @param string|array|null $eventName Event name or an Array event listener pairs
     * @param mixed $listener A callable function
     * @return static
     */
    function addListener(string|array|null $eventName = null, mixed $listener = null): static
    {
        if (is_string($eventName)) $eventName = [$eventName => $listener];

        if (is_array($eventName))
        {
            foreach ($eventName as $event => $eventListener)
            {
                if (!is_callable($eventListener)) continue;

                if (!array_key_exists($event, $this->events))    $this->events[$event]    = true;
                if (!array_key_exists($event, $this->listeners)) $this->listeners[$event] = [];
                if (!is_array($this->listeners[$event]))         $this->listeners[$event] = [];

                if (!in_array($eventListener, $this->listeners[$event]))
                {
                    $this->listeners[$event][] = $eventListener;
                }
            }
        }
        return $this;
    }

    /**
     * Remove attached listener from event listeners
     * if listener attaced on any events, it will be removed too unless the $eventName is described
     *
     *      Examples:
     *      $person->remove_listner('sing', $person->lazy);
     *      // person will not 'lazy' when 'sing'
     *      // person still 'lazy' if 'walk' or 'dance'
     *
     *      $person->removeListener(null, $person->lazy); // will remove any listener on each events
     *      // person will not 'lazy' anymore
     *
     * @param string|array|null $eventName Events which listener exists
     * @param mixed $listener Listener to be removed from event listeners
     * @return static
     */
    function removeListener(string|array|null $eventName = null, mixed $listener = null): static
    {
        if (is_null($eventName))
        {
            foreach ($this->listeners as $event => $_)
            {
                $this->removeListener($event, $listener);
            }
            return $this;
        }

        if (is_array($eventName))
        {
            foreach ($this->listeners as $event => $_)
            {
                if (in_array($event, $eventName)) $this->removeListener($event, $listener);
            }
            return $this;
        }

        if (array_key_exists($eventName, $this->listeners))
        {
            if (!is_array($this->listeners[$eventName]))
            {
                $this->listeners[$eventName] = [$this->listeners[$eventName]];
            }
            foreach ($this->listeners[$eventName] as $i => $eventListener)
            {
                if ($eventListener === $listener) unset($this->listeners[$eventName][$i]);
            }
        }
        return $this;
    }

    /**
     * Clear all listeners from an event
     *
     *      Example:
     *      $person->on('sing', $peson->lazy);
     *      $person->clearListener('sing');
     *      // sing listeners are removed
     *
     *      $person->clearListener();
     *      // person have no any single listener on each event
     *
     * @param string|null $eventName Events want to clear the listeners
     * @return static
     */
    function clearListener(?string $eventName = null): static
    {
        if (array_key_exists($eventName, $this->listeners))
        {
            $this->listeners[$eventName] = [];
        }
        return $this;
    }

    /**
     * Check if an event has one or more listeners
     * return `false` if event doesn't exist
     *
     * @param string|null $eventName Events name to be checked
     * @return bool Has listener status
     */
    function hasListener(?string $eventName = null): bool
    {
        return $this->hasEvent($eventName) && !empty($this->listeners[$eventName]);
    }
}
