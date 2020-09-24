<?php

class RouletteTest_Template extends RouletteUnittest_Model {

    public $name = 'Roulette\Template';

    protected $skip = array('is','isNot');
    
    public function __construct() {
        parent::__construct();
    }
    
    function index() {
        $me = $this;
        $class = $this->name;

        $me->test('setgetMarker', method_exists($class, 'setMarker') && method_exists($class, 'getMarker'), function() use($me, $class)
        {
            $tpl = new $class;
            
            $tpl->setMarker(array('[',']'));
            $me->test('setgetMarker, change marker', $tpl->getMarker() == array('[',']') );
            
            $tpl->setMarker(array('{','}'));
            $me->test('setgetMarker, rolling back marker',  $tpl->getMarker() == array('{','}') );
        });

        $me->test('setgetTemplate', method_exists($class, 'setTemplate') && method_exists($class, 'getTemplate'), function() use($me, $class)
        {
            $obj = new $class;
            $obj->setTemplate('name : {name}');
            $me->test('setgetTemplate, string', $obj->getTemplate() == array('name : {name}') );
            $obj->setTemplate(array('name : {name}', 'gender : {gender}'));
            $me->test('setgetTemplate, array', $obj->getTemplate() == array('name : {name}', 'gender : {gender}') );
        });

        $me->test('apply', method_exists($class, 'apply') , function() use($me, $class)
        {
            $obj = new $class;
            $replacer = array('name'=>'john', 'gender'=>'male');
            $result_should = 'name : john gender : male';

            $obj->setTemplate('name : {name} gender : {gender}');
            $me->test('apply, string', $obj->apply($replacer) == $result_should );
            
            $obj->setTemplate(array('name : {name}', ' ', 'gender : {gender}'));
            $me->test('apply, array', $obj->apply($replacer) == $result_should);

            $obj->setTemplate('name : {name} gender : {gender}');
            $me->test('apply, unfullfilled', $obj->apply(array('name'=>'me')) == 'name : me gender : ' );
        });

        $me->test('compile', method_exists($class, 'compile'), function() use($me, $class)
        {
            $obj = new $class;
            $replacer = array('name'=>'john','gender'=>'male');
            
            $result_should = 'name : '.$replacer['name'].' gender : '.$replacer['gender'];
            $me->test('compile, string', $class::compile('name : {name} gender : {gender}')->apply($replacer) == $result_should);

            $me->test('compile, array', $class::compile(array('name : {name}',' ','gender : {gender}'))->apply($replacer) == $result_should );
        });

        $me->test('parse', method_exists($class, 'parse'), function() use($me, $class)
        {
            $obj = new $class;
            $replacer = array('name'=>'john','gender'=>'male');
            
            $result_should = 'name : '.$replacer['name'].' gender : '.$replacer['gender'];
            $me->test('parse, string', $class::parse('name : {name} gender : {gender}',$replacer) == $result_should);

            $me->test('parse, array', $class::parse(array('name : {name}',' ','gender : {gender}'),$replacer) == $result_should );
        });

        $me->test('setMarker', method_exists($class, 'setMarker'), function() use($me, $class)
        {
            $tpl = new $class;
            
            $tpl->setMarker(array('[',']'));
            $me->test('setMarker, change marker', $tpl->getMarker() == array('[',']') );
        });

        $me->test('getMarker', method_exists($class, 'getMarker'), function() use($me, $class)
        {
            $tpl = new $class;
            
            $tpl->setMarker(array('[',']'));
            $me->test('getMarker, put marker', $tpl->getMarker() == array('[',']') );
        });

        $me->test('setTemplate', method_exists($class, 'setTemplate'), function() use($me, $class)
        {
            $obj = new $class;
            $replacer = array('name'=>'john', 'gender'=>'male');
            $result_should = 'name : john gender : male';

            $me->test('setTemplate, with marker', $obj->setTemplate('name : {name} gender : {gender}'));
        });

        $me->test('getTemplate', method_exists($class, 'getTemplate'), function() use($me, $class)
        {
            $obj = new $class;
            $obj->setTemplate('name : {name}');
            $me->test('getTemplate, string', $obj->getTemplate() == array('name : {name}') );
        });

        $this->createUnfinishedTask();
    }

}