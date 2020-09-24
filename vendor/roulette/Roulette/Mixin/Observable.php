<?php
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
     *
     * @property    Array $listeners A list of listener 
     * @access      protected
     */
    protected $listeners = array();

    /**
     * Array of event to be captured
     *
     * @property    Array $events A list of events 
     * @access      protected
     */
    protected $events = array();

    /**
     * Event capturing status, `false` mean disable event capturing
     *
     * @property    Array $runEvents 
     * @access      protected
     */
    protected $runEvents = true;
    
	/**
     * Sorthand for \Roulette\Base::addListener()
     * 
     * @param String $eventName Event name or an Array event listener pairs
     * @param Mixed $listener A callable function
     * @return \Roulette\Base
     */
    function on($eventName = null, $listener = null)
    {
        return $this->addListener($eventName, $listener);
    }

    /**
     * Sorthand for \Roulette\Base::removeListener()
     * 
     * @param String|Array $eventName Events which listener exists
     * @param Object $listener Listener (reference to the listener) to be removed from event listeners
     * @return Mixed Removed listener
     */
    function un($eventName = null, $listener = null)
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
     *              function __construct($config)
     *              $this->addEvent('sing'); 
     *          }
     *      }
     * 
     *      $person = new Person();
     *      $person->trigger('sing'); // will executed any `sing` listeners
     * 
     * @param String $eventName String event name to be triggered
     * @param Array $params Array of arguments will pass to listeners
     * @return Boolean `true` if the listeners executed, `false` if the listeners return false
     */
    function trigger($eventName = null, $params = array())
    {
        if ( ! is_array($params) ){
            $params = array($params);
        }
        if ( $this->hasEvent($eventName) and $this->eventEnabled($eventName) ){
            if ( array_key_exists($eventName, $this->listeners) and is_array($this->listeners[$eventName]) ){
                foreach ($this->listeners[$eventName] as $i => $listener) {
                    if (is_callable($listener)){
                        $listener_result = call_user_func_array($listener, $params);
                        if ($listener_result === false){
                            return $listener_result;
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * Fire the specified event with the passed parameter list
     * @param  string $eventName 
     * @param  array $params    
     * @return mixed           
     */
    function fireEventArgs($eventName = null, $params = null)
    {
        return call_user_func_array(array($this, 'trigger'), array($eventName, $params));
    }

    /**
     * Fire the specified event
     * @param  string $eventName 
     * @return mixed            
     */
    function fireEvent($eventName = null)
    {
        $args = func_get_args();
        array_shift($args);
        return call_user_func_array(array($this, 'trigger'), array($eventName, $params));
    }

    /**
     * Add one or more events to the events list
     * 
     *      Example:
     *      class Person extends \Roulette\Base
     *      {
     *          function __construct($config){
     *              function __construct($config)
     *          }
     *      }
     * 
     *      $person = new Person();
     *      $person->addEvent('sing'); // return array('sing')
     *      $person->addEvent(array(
     *          'run','dance','swim'
     *      ));
     *      // return array('run','swim'), because event `dance` is already registered
     * 
     * @param String|Array $event String event name or array of events name
     * @return Array Added events, registered event will not returned
     */
    function addEvent($event = null)
    {
        $events_added = array();

        if ( ! is_array($event) ){
            $event = array($event);
        }

        foreach($event as $eventName){
            
            if ( array_key_exists($eventName, $this->events) || !is_string($eventName) ) continue;
            
            $eventName;
            $this->events[$eventName] = true;
            $this->listeners[$eventName] = array();
            $events_added[] = $eventName;
        }
        return $events_added;
    }

    /**
     * Check if an event is already exist. 
     * Disabled event mean exist  
     * 
     *      Example:
     *      class Person extends \Roulette\Base
     *      {
     *          function __construct($config){
     *              function __construct($config)
     *          }
     *      }
     * 
     *      $person = new Person();
     *      $person->hasEvent('sing'); // return false;
     *      // add a `sing` event
     *      $person->addEvent('sing');
     *      $person->hasEvent('sing'); // return true;
     * 
     * @param String $eventName Event name
     * @return Boolean Has event status
     */
    function hasEvent($eventName = null)
    {
        return array_key_exists($eventName, $this->events);
    }

    /**
     * Remove event from its events capturing
     *
     *      Example:
     *      class Person extends \Roulette\Base
     *      {
     *          function __construct($config){
     *              function __construct($config)
     *              $this->addEvent('sing','dance');
     *          }
     *      }
     * 
     *      $person = new Person(array(
     *          'listeners'=>array(
     *              'sing'=>function() use($person){
     *                  echo "i would'n sing anymore if the event removed";
     *              }
     *          )
     *      ));
     *      $person->trigger('sing');
     *      // echo: i would'n sing anymore if the event removed     
     * 
     *      $person->removeEvent('sing');
     *      // person no longer can 'sing'
     *      $person->trigger('sing');
     *      // do nothing
     *  
     *      // using array
     *      $person->removeEvent(array(
     *          'event1', 'event2'
     *      ));
     * 
     * @param String $event A name of event to be remove from its object
     * @return \Roulette\Base
     */
    function removeEvent($event = null)
    {
        $removedEvents = array();
        if (!is_array($event)){
            $event = array($event);
        }
        foreach ($event as $eventName) {
            if ( is_string($eventName) && array_key_exists($eventName, $this->events) ) {
                unset($this->events[$eventName]);
                $removedEvents[] = $eventName;
            }
        }
        return $removedEvents;
    }

    /**
     * Check if class using events capturing (is enabled or not)
     * 
     * @return Boolean enabled status
     */
    function isObservable()
    {
        return (boolean) $this->runEvents;
    }

    /**
     * Enabling events capturing
     *
     *      Example:
     *      class Person extends \Roulette\Base
     *      {
     *          function __construct($config){
     *              function __construct($config)
     *              $this->addEvent('sing','dance');
     *          }
     *      }
     *      
     *      $person->enableEvents(false); // disable all event capturing
     *      $person->trigger('sing');
     *      // do nothing
     *      
     *      // enable all event capturing
     *      // it doesnt affect from disabled event
     *      // only change the capturing status
     *      $person->enableEvents(true);
     *      $person->trigger('sing');
     *      // doing 'sing' function
     *       
     * @param String $observable Enabled status, default `true`
     * @return \Roulette\Base
     */
    function setObservable($observable = true)
    {
        $this->runEvents = (boolean) $observable;
        return $this;
    }

    /**
     * Enable capturing of the event 
     *
     *      Example:
     *      class Person extends \Roulette\Base
     *      {
     *          function __construct($config){
     *              function __construct($config)
     *              $this->addEvent('sing','dance');
     *          }
     *      }
     * 
     *      $person = new Person(array(
     *          'listeners'=>array(
     *              'sing'=>function() use($person){
     *                  echo "i would'n sing anymore if the event removed";
     *              }
     *          )
     *      ))
     *      
     *      $person->enableEvent('sing',false); // person no longer can 'sing' till the event enabled
     *      $person->trigger('sing');
     *      // do nothing
     * 
     *      $person->enableEvent('sing',true); // now person can sing anymore
     *      $person->trigger('sing');
     *      // echo: i would'n sing anymore if the event removed
     *       
     * @param String $eventName A name of event to be enabled
     * @param String $enable Enabled status, default `true`
     * @return \Roulette\Base
     */
     
    function enableEvent($eventName = null, $enable = true)
    {
        if (array_key_exists($eventName, $this->events)){
            $this->events[$eventName] = (boolean)$enable;
        }
        return $this;
    }

    /**
     * Get enabled status of an event.
     *
     *      Example:
     *      class Person extends \Roulette\Base
     *      {
     *          function __construct($config){
     *              function __construct($config)
     *              $this->addEvent('sing');
     *          }
     *      }
     *      
     *      $person->enableEvent('sing');
     *      $person->eventEnabled('sing'); // return: true
     *      $person->eventEnabled('walk'); // return: false, because event `walk` doesnt exist
     * 
     * @param String $eventName Event name
     * @return Boolean Disabled status, return `false` if event doesnt exist
     */
    function eventEnabled($eventName = null)
    {
        $enabled = false;
        if ( $this->hasEvent($eventName) ){
            $enabled = (boolean) $this->events[$eventName];
        }
        return $enabled;
    }
    
    /**
     * Disable capturing of the event
     *
     *      Example:
     *      class Person extends \Roulette\Base
     *      {
     *          function __construct($config){
     *              function __construct($config)
     *              $this->addEvent('sing','dance');
     *          }
     *      }
     * 
     *      $person = new Person(array(
     *          'listeners'=>array(
     *              'sing'=>function() use($person){
     *                  echo "i would'n sing anymore if the event removed";
     *              }
     *          )
     *      ))
     *      
     *      $person->disableEvent('sing'); // person no longer can 'sing' till the event enabled
     *      $person->trigger('sing');
     *      // do nothing
     * 
     *      $person->disableEvent('sing',false); // now person can sing anymore
     *      $person->trigger('sing');
     *      // echo: i would'n sing anymore if the event removed
     *       
     * @param String $eventName A name of event to be enabled
     * @param String $disable Disabled status, default `true`
     * @return \Roulette\Base
     */
    function disableEvent($eventName = null, $disable = true)
    {
        if ($this->events[$eventName]){
            $this->events[$eventName] = ! (boolean)$disable;
        }
        return $this;
    }


    /**
     * Get disabled status of an event.
     *
     *      Example:
     *      class Person extends \Roulette\Base
     *      {
     *          function __construct($config){
     *              function __construct($config)
     *              $this->addEvent('sing');
     *          }
     *      }
     *      
     *      $person->disableEvent('sing',true);
     *      $person->eventDisabled('sing'); // return: true
     *      $person->eventDisabled('walk'); // return: true, because event `walk` doesnt exist
     * 
     * @param String $eventName Event name
     * @return Boolean Disabled status, return `true` if event doesnt exist
     */
    function eventDisabled($eventName = null)
    {
        $disabled = true;
        if ( $this->hasEvent($eventName) ){
            $disabled = ! $this->events[$eventName];
        }
        return $disabled;
    }

    /**
     * Add listener on one or more events
     * will add event if event doesnt exist on event list
     * 
     *      Example:
     *      class Person extends \Roulette\Base
     *      {
     *          function __construct($config){
     *              function __construct($config)
     *          }
     *          function lazy(){
     *              echo 'i dont want do anything at this time'
     *          }
     *      }
     *      $person = new Person();
     *      $person->addListener('walk', $person->lazy);
     *      $person->addListener(array(
     *          'walk' => $person->lazy,
     *          'dance' => $person->lazy
     *      ));
     * 
     * @param String $eventName Event name or an Array event listener pairs
     * @param Mixed $listener A callable function
     * @return \Roulette\Base
     */
    function addListener($eventName = null, $listener = null){
        if ( (is_array($eventName) or is_string($eventName)) ){
            if (!is_array($eventName)){
                $eventName = array($eventName => $listener);
            }
            foreach ($eventName as $event => $event_listener) {
                if ( ! is_callable($event_listener) ) continue;

                if ( ! array_key_exists($event, $this->events) ) $this->events[$event] = true;
                if ( ! array_key_exists($event, $this->listeners) ) $this->listeners[$event] = array();

                if ( ! is_array($this->listeners[$event]) ){
                    $this->listeners[$event] = array();
                }
                if ( ! in_array($listener, $this->listeners[$event]) ){
                    array_push($this->listeners[$event], $event_listener);
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
     *      class Person extends \Roulette\Base
     *      {
     *          function __construct($config){
     *              function __construct($config)
     *              $this->addEvent('sing','dance','walk');
     *          }
     *          function lazy(){
     *              echo 'i dont want do anything at this time'
     *          }
     *      }
     *  
     *      $person = new Person();
     *      $person->on('sing',$peson->lazy);
     *      $person->on('dance',$peson->lazy);
     *      $person->on('walk',$peson->lazy);
     * 
     *      $person->remove_listner('sing',$person->lazy);
     *      // person will not 'lazy' when 'sing'
     *      // person still 'lazy' if 'walk' or 'dance'
     * 
     *      $person->remove_listner(array('walk'),$person->lazy);
     *      // person will not 'lazy' when 'walk'
     *      // person still 'lazy' in 'dance'
     *      
     *      $person->removeListener(null, $person->lazy); // will remove any listener on each events that have $person->lazy function
     *      // person will not 'lazy' anymore
     *      
     * @param String|Array $eventName Events which listener exists
     * @param Object $listener Listener (reference to the listener) to be removed from event listeners
     * @return Mixed Removed listener
     */
    function removeListener($eventName = null, $listener = null){
        
        // null eventName mean to remove attached listener on any events
        if (is_null($eventName)){
            foreach ($this->listeners as $event => $listeners) {
                $this->removeListener($event, $listener);
            }
            return;
        }

        // array eventName mean event listener pairs mode
        if ( is_array($eventName) ){
            foreach ($this->listeners as $event => $listeners) {
                if (in_array($event, $eventName)){
                    $this->removeListener($event, $listener);
                }
            }
            return;
        }

        if ( array_key_exists($eventName, $this->listeners) ) {
            if ( ! is_array($this->listeners[$eventName]) ){
                $this->listeners[$eventName] = array($this->listeners[$eventName]);
            }
            foreach ($this->listeners[$eventName] as $i => $event_listener) {
                if ($event_listener === $listener){
                    unset($this->listeners[$eventName][$i]);
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
     *      class Person extends \Roulette\Base
     *      {
     *          function __construct($config){
     *              function __construct($config)
     *              $this->addEvent('sing','dance','walk');
     *          }
     *      }
     *  
     *      $person = new Person();
     *      $person->on('sing',$peson->lazy);
     *      $person->clearListener('sing');
     *      // sing listeners are removed     
     * 
     *      $person->clearListener();
     *      // person have no any single listener on each event
     *      
     * @param String|Array $eventName Events want to clear the listeners
     * @return \Roulette\Base
     */
    function clearListener($eventName = null)
    {
        if ( array_key_exists($eventName, $this->listeners) ){
            $this->listeners[$eventName] = array();
        }
        return $this;
    }

    /**
     * Check if an event has one or more listeners
     * return `false` if event doesn't exist
     * 
     * @param String $eventName Events name to be checked
     * @return Boolean Has listener status
     */
    function hasListener($eventName = null)
    {
        return $this->hasEvent($eventName) and ! empty($this->listeners[$eventName]);
    }
}