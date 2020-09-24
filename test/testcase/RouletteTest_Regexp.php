<?php

class RouletteTest_Regexp extends RouletteUnittest_Model {

    public $name = 'Roulette\Regexp';

    protected $skip = array('is','isNot');

    public function __construct() {
        parent::__construct();
    }

    public function index()
    {
        $me = $this;
        $class = $this->name;

        $me->test('__construct', method_exists($class, '__construct'), function() use($me, $class)
        {
            $obj = new $class();
            $me->test('__construct, default', $obj);
        });

        $me->test('create', method_exists($class, 'create'), function() use($me, $class)
        {
            $obj = new $class();
            $objString = $obj->getString();
            $me->test('create, default', empty($objString));
        });

        $me->test('setgetString', method_exists($class, 'setString') and method_exists($class, 'getString'), function() use($me, $class)
        {
            $me->test('setString', method_exists($class, 'setString'));
            $me->test('getString', method_exists($class, 'getString'));

            $obj = new $class();
            $objString = $obj->getString();
            $me->test('getString, default', empty($objString));

            $obj = new $class('/abc/');
            $objString = $obj->getString();
            $me->test('getString, valued', $objString == '/abc/');          
        });

        $me->test('setgetReplaceString', method_exists($class, 'setReplaceString') and method_exists($class, 'getReplaceString'), function() use($me, $class)
        {
            $me->test('setReplaceString', method_exists($class, 'setReplaceString'));
            $me->test('getReplaceString', method_exists($class, 'getReplaceString'));

            $obj = new $class();
            $objString = $obj->getReplaceString();
            $me->test('getReplaceString, default', empty($objString));

            $obj = new $class('/abc/','ABC');
            $objString = $obj->getReplaceString();
            $me->test('getReplaceString, valued', $objString == 'ABC');            
        });

        $me->test('test', method_exists($class, 'test'), function() use($me, $class)
        {
            $obj = new $class('/abc/','ABC');
            $me->test('test, default', !$obj->test());
            $me->test('test, true', $obj->test('abc'));
            $me->test('test, true2', $obj->test('abcd'));
            $me->test('test, false', !$obj->test('ab'));
            $me->test('test, false2', !$obj->test(1));
        });

        $me->test('replace', method_exists($class, 'replace'), function() use($me, $class)
        {
            $obj = new $class('/abc/','ABC');
            $me->test('replace, default', $obj->replace() == '');
            $me->test('replace, true', $obj->replace('abc') == 'ABC');
            $me->test('replace, true2', $obj->replace('abcd') == 'ABCd');
            $me->test('replace, false', $obj->replace('ab') == 'ab');
            $me->test('replace, false2', $obj->replace(1) == 1);
        });
        
        $this->createUnfinishedTask();
    }

}