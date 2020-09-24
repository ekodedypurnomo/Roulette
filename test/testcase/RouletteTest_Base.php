<?php

use Roulette\Base;

class RouletteTest_Base extends RouletteUnittest_Model {

    public $name = 'Roulette\Base';
    protected $skip = array('__construct');

    public function __construct(){
        parent::__construct();
    }

    public function index()
    {
        $me = $this;
        $class = $this->name;

        $me->test('is', method_exists($class, 'is'), function() use($me, $class)
        {
            $me->test('is, true', function() use($me, $class){
                $base = new $class();
                return $class::is($base);
            });
            $me->test('is, false', function() use($me, $class){
                $fake = new $this;
                return $class::is($fake) === false;
            });
        });

        $me->test('isNot', method_exists($class, 'isNot'), function() use($me, $class)
        {
            $me->test('isNot, true', function() use($me, $class){
                $base = new $class();
                return !$class::isNot($base);
            });
        });

        $me->test('create', method_exists($class, 'create'), function() use($me, $class)
        {
            $me->test('create, empty', function() use($me, $class){
                $base = $class::create();
                return $class::is($base);
            });
            $me->test('create, valid', function() use($me, $class){
                $base = new $class();
                return $class::create($base) === $base;
            });
            $me->test('create, invalid null', function() use($me, $class){
                $from = $class::create(null);
                return $class::is($from);
            });
            $me->test('create, invalid object', function() use($me, $class){
                $from = $class::create(new stdClass);
                return $class::is($from);
            });
            $me->test('create, invalid boolean', function() use($me, $class){
                $from = $class::create(new stdClass);
                return $class::is($from);
            });
        });

        /* start configurable */
        // $me->test('configure', method_exists($class, 'configure'), function() use($me, $class)
        // {
        //     $testdata = array(
        //         'first_name'=>'john',
        //         'last_name'=>'doe'
        //     );

        //     $obj = new Base;
        //     $me->test('configure, beforeconfig', property_exists($obj, 'first_name') === false);
        //     $obj->configure($testdata);
        //     $me->test('configure, afterconfig', $obj->first_name == $testdata['first_name']);
        //     $me->test('configure, afterconfig, another variable', $obj->last_name == $testdata['last_name']);
        // });

        // $me->test('getConfig', method_exists($class, 'getConfig'), function() use($me, $class)
        // {
        //     $obj = new Base;
        //     $val = rand ( 1 , 100 );
        //     $obj->setConfig('makan', $val);
        //     $me->test('getConfig, valid', $obj->getConfig('makan') === $val );
        // });

        // $me->test('setConfig', method_exists($class, 'setConfig'), function() use($me, $class)
        // {
        //     $obj = new Base;
        //     $val = rand ( 1 , 100 );
            
        //     $obj->setConfig('age', $val);
        //     $me->test('setConfig, valid', $obj->age === $val );
            
        //     $obj->setConfig('_age', $val);
        //     $me->test('setConfig, invalid', $obj->getConfig('_age') === null );

        //     $obj->setConfig('$age', $val);
        //     $me->test('setConfig, use $', $obj->getConfig('$age') === $val );

        //     $obj->setConfig('.age', $val);
        //     $me->test('setConfig, use .', $obj->getConfig('.age') === $val );

        // });

        // $me->test('config', method_exists($class, 'config'), function() use($me, $class)
        // {
        //     $obj = new Base;
        //     $val = rand ( 1 , 100 );
        //     $obj->config('age', $val);
        //     $me->test('config, set', $obj->age === $val);
        //     $me->test('config, get', $obj->config('age') == $val);
        // });

        // $me->test('enableConfig', method_exists($class, 'enableConfig'), function() use($me, $class)
        // {
        //     $obj = new Base;
        //     $me->test('enableConfig, beforechange', $obj->configEnabled('walk') === false );
            
        //     $obj->enableConfig('walk');
        //     $me->test('enableConfig, aftercange', $obj->configEnabled('walk') === true );

        //     $obj->enableConfig('walk', false);
        //     $me->test('enableConfig, disabled', $obj->configEnabled('walk') === false );

        //     $obj->enableConfig('walk', true);
        //     $me->test('enableConfig, enabled', $obj->configEnabled('walk') === true );
        // });

        // $me->test('configEnabled', method_exists($class, 'configEnabled'), function() use($me, $class)
        // {
        //     $obj = new Base;
        //     $me->test('configEnabled, beforechange', $obj->configEnabled('walk') === false );
            
        //     $obj->enableConfig('walk');
        //     $me->test('configEnabled, afterchange', $obj->configEnabled('walk') === true );

        //     $obj->enableConfig('walk', false);
        //     $me->test('configEnabled, disable', $obj->configEnabled('walk') === false );

        //     $obj->enableConfig('walk', true);
        //     $me->test('configEnabled, enable', $obj->configEnabled('walk') === true );
        // });

        // $me->test('disableConfig', method_exists($class, 'disableConfig'), function() use($me, $class)
        // {

        //     $obj = new Base;
        //     $me->test('disableConfig, beforechange', $obj->configDisabled('walk') === true );
            
        //     $obj->disableConfig('walk');
        //     $me->test('disableConfig, afterchange', $obj->configDisabled('walk') === true );

        //     $obj->disableConfig('walk', true);
        //     $me->test('disableConfig, disabled', $obj->configDisabled('walk') === true );

        //     $obj->disableConfig('walk', false);
        //     $me->test('disableConfig, enabled', $obj->configDisabled('walk') === false );
        // });

        // $me->test('configDisabled', method_exists($class, 'configDisabled'), function() use($me, $class)
        // {
        //     $obj = new Base;
        //     $me->test('configDisabled, beforechange', $obj->configDisabled('walk') === true );
            
        //     $obj->disableConfig('walk');
        //     $me->test('configDisabled, afterchange', $obj->configDisabled('walk') === true );

        //     $obj->disableConfig('walk', false);
        //     $me->test('configDisabled, enabled', $obj->configDisabled('walk') === false );

        //     $obj->disableConfig('walk', true);
        //     $me->test('configDisabled, disabled', $obj->configDisabled('walk') === true );
        // });

        // start observable
        /*
        $me->test('trigger', method_exists($class, 'trigger'), function() use($me, $class)
        {

            $obj = new Base;
            $dummy = (object) array('data'=>array());
            $obj->on('the_trigger', function() use($dummy){
                $dummy->data[] = 1;
            });
            $obj->on('the_trigger', function() use($dummy){
                $dummy->data[] = 'two';
                $dummy->success = true;
            });
            $me->test('trigger, return value', $obj->trigger('the_trigger') == true );
            $me->test('trigger, affected', $dummy->data == array(1,'two'));
        });

        $me->test('addEvent', method_exists($class, 'addEvent'), function() use($me, $class)
        {

            $obj = new Base;
            $me->test('addEvent, beforeadd', $obj->hasEvent('xxx') === false );

            $obj->addEvent('xxx');
            $me->test('addEvent, afteradd', $obj->hasEvent('xxx') === true );

            $obj->addEvent(array('yyy'));
            $me->test('addEvent, add in array', $obj->hasEvent('yyy') === true );
        });
        
        $me->test('hasEvent', method_exists($class, 'hasEvent'), function() use($me, $class)
        {

            $obj = new Base;
            $me->test('hasEvent, beforeadd', $obj->hasEvent('xxx') === false );

            $obj->addEvent('xxx');
            $me->test('hasEvent, afteradd', $obj->hasEvent('xxx') === true );

            $obj->removeEvent('xxx');
            $me->test('hasEvent, afterremove', $obj->hasEvent('xxx') === false );
        });
        
        $me->test('removeEvent', method_exists($class, 'removeEvent'), function() use($me, $class)
        {

            $obj = new Base;
            $obj->addEvent(array('xxx','yyy','zzz'));
            
            $me->test('removeEvent, beforeremove', $obj->hasEvent('xxx') === true );
            $obj->removeEvent('xxx');
            $me->test('removeEvent, afterremove', $obj->hasEvent('xxx') === false );

            $me->test('removeEvent, before remove in array', $obj->hasEvent('yyy') === true );
            $obj->removeEvent(array('yyy'));
            $me->test('removeEvent, after remove in array', $obj->hasEvent('yyy') === false );

            $me->test('removeEvent, before remove using arguments', $obj->hasEvent('zzz') === true );
            $obj->removeEvent(array('xxx', 'zzz'));
            $me->test('removeEvent, after remove using arguments', $obj->hasEvent('zzz') === false );

            $obj->removeEvent('aaa');
            $me->test('removeEvent, inexist event', $obj->hasEvent('aaa') === false );
        });
        
        $me->test('setObservable', method_exists($class, 'setObservable'), function() use($me, $class)
        {

            $obj = new Base;

            $me->test('setObservable, init', $obj->isObservable() === true );

            $obj->setObservable(false);
            $me->test('setObservable, disabled', $obj->isObservable() === false );

            $obj->setObservable(true);
            $me->test('setObservable, enabled', $obj->isObservable() === true );

            $obj->setObservable(false);
            $me->test('setObservable, reset to disabled', $obj->isObservable() === false );

            $obj->setObservable();
            $me->test('setObservable, check after reset', $obj->isObservable() === true );
        });

        $me->test('isObservable', method_exists($class, 'isObservable'), function() use($me, $class)
        {

            $obj = new Base;

            $me->test('isObservable, init', $obj->isObservable() === true );

            $obj->setObservable(false);
            $me->test('isObservable, params disabled', $obj->isObservable() === false );

            $obj->setObservable();
            $me->test('isObservable, empty params and enabled', $obj->isObservable() === true );
        });

        $me->test('enableEvent', method_exists($class, 'enableEvent'), function() use($me, $class)
        {

            $obj = new Base;
            
            $obj->addEvent('xxx');
            $me->test('enableEvent, afteradd', $obj->eventEnabled('xxx') === true );

            $obj->enableEvent('xxx', false);
            $me->test('enableEvent, disabled', $obj->eventEnabled('xxx') === false );

            $obj->enableEvent('xxx');
            $me->test('enableEvent, enabled', $obj->eventEnabled('xxx') === true );
        });

        $me->test('eventEnabled', method_exists($class, 'eventEnabled'), function() use($me, $class)
        {

            $obj = new Base;

            $me->test('eventEnabled, empty param', $obj->eventEnabled() === false );
            $me->test('eventEnabled, inexist', $obj->eventEnabled('xxx') === false );

            $obj->addEvent('xxx');
            $me->test('eventEnabled, afteradd', $obj->eventEnabled('xxx') === true );

            $obj->enableEvent('xxx', false);
            $me->test('eventEnabled, disabled', $obj->eventEnabled('xxx') === false );
        });
        
        $me->test('disableEvent', method_exists($class, 'disableEvent'), function() use($me, $class)
        {

            $obj = new Base;

            $obj->addEvent('xxx');
            $me->test('disableEvent, afteradd', $obj->eventDisabled('xxx') === false );

            $obj->disableEvent('xxx', false);
            $me->test('disableEvent, enabled', $obj->eventDisabled('xxx') === false );

            $obj->disableEvent('xxx');
            $me->test('disableEvent, disabled', $obj->eventDisabled('xxx') === true );
        });
        
        $me->test('eventDisabled', method_exists($class, 'eventDisabled'), function() use($me, $class)
        {

            $obj = new Base;

            $me->test('eventDisabled, empty param',  $obj->eventDisabled() === true );
            $me->test('eventDisabled, inexist event',  $obj->eventDisabled('xxx') === true );

            $obj->addEvent('xxx');
            $me->test('eventDisabled, exist event',  $obj->eventDisabled('xxx') === false );

            $obj->disableEvent('xxx');
            $me->test('eventDisabled, after disabling',  $obj->eventDisabled('xxx') === true );
        });
        
        $me->test('addListener', method_exists($class, 'addListener'), function() use($me, $class)
        {

            $obj = new Base;
            
            $me->test('addListener, beforeadd', $obj->hasListener('create') === false );
            $obj->addListener('create', function(){
                return 'on create';
            });
            $me->test('addListener, afteradd', $obj->hasListener('create') === true );

            $me->test('addListener, check inexist', $obj->hasListener('load') === false );
            $obj->addListener(array(
                'load' => function(){}
            ));
            $me->test('addListener, afteradd on array', $obj->hasListener('load') === true );
        });

        $me->test('removeListener', method_exists($class, 'removeListener'), function() use($me, $class)
        {

            $obj = new Base;

            $listener = function(){};

            $obj->addListener('load', $listener);
            $me->test('removeListener, afteradd', $obj->hasListener('load') === true );

            $obj->removeListener('load', $listener);
            $me->test('removeListener, afterremove', $obj->hasListener('load') === false );

            $obj->addListener('reload', $listener);
            $me->test('removeListener, afteradd on different event', $obj->hasListener('reload') === true );

            $obj->removeListener(null, $listener);
            $me->test('removeListener, on null event', $obj->hasListener('reload') === false );

            $obj->addListener('destroy', $listener);
            $me->test('removeListener, afteradd on different event again', $obj->hasListener('destroy') === true );
            
            $obj->removeListener(array('destroy'), $listener);
            $me->test('removeListener, afterremove with array params', $obj->hasListener('destroy') === false );
        });

        $me->test('hasListener', method_exists($class, 'hasListener'), function() use($me, $class)
        {

            $obj = new Base;

            $listener = function(){};

            $me->test('hasListener, beforeadd', $obj->hasListener('load') === false );

            $obj->addListener('load', $listener);
            $me->test('hasListener, afteradd', $obj->hasListener('load') === true );

            $obj->removeListener('load', $listener);
            $me->test('hasListener, afterremove', $obj->hasListener('load') === false );
        });

        $me->test('clearListener', method_exists($class, 'clearListener'), function() use($me, $class)
        {

            $obj = new Base;

            $me->test('clearListener, beforeadd', $obj->hasListener('load') === false );

            $obj->addListener('load', function(){});
            $me->test('clearListener, afteradd', $obj->hasListener('load') === true );

            $obj->clearListener('load');
            $me->test('clearListener, afterclear', $obj->hasListener('load') === false );
        });
        */
       
        $this->createUnfinishedTask();
    }

}