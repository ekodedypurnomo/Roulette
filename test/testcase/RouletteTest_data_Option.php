<?php

class RouletteTest_data_Option extends RouletteUnittest_Model {

    public $name = 'Roulette\Data\Option';

    protected $skip = array('is','isNot');

    public function __construct() {
        parent::__construct();
    }

    public function index()
    {
        $me = $this;
        $class = $this->name;
        
        $me->test('property', true, function() use($me, $class)
        {
            $me->test('property, fields', property_exists($class, 'fields'));

            $me->test('property, render', property_exists($class, 'render'));

            $me->test('property, display', property_exists($class, 'display'));

            $me->test('property, inline', property_exists($class, 'inline'));

            $me->test('property, merge', property_exists($class, 'merge'));
            
            $me->test('property, mergeMask', property_exists($class, 'mergeMask'));

            $me->test('property, autoLoad', property_exists($class, 'autoLoad'));

            $me->test('property, relations', property_exists($class, 'relations'));
        });

        $me->test('isInline', method_exists($class, 'isInline'), function() use($me, $class)
        {
            $obj = new $class();
            $me->test('isInline, default', $obj->isInline() === false);

            $obj = new $class(['inline'=>true]);
            $me->test('isInline, true', $obj->isInline());

            $obj = new $class(['inline'=>false]);
            $me->test('isInline, false', $obj->isInline() === false);

            $me->test('setInline', method_exists($class, 'setInline'), function() use($me, $class)
            {
                $obj = new $class(['inline'=>false]);
                $me->test('setInline, init', $obj->isInline() === false);

                $obj->setInline(true);
                $me->test('setInline, true', $obj->isInline() === true);

                $obj->setInline(false);
                $me->test('setInline, false', $obj->isInline() === false);
            });
        });

        $me->test('isMerge', method_exists($class, 'isMerge'), function() use($me, $class)
        {
            $obj = new $class();
            $me->test('isMerge, default', $obj->isMerge() === false);

            $obj = new $class(['merge'=>true]);
            $me->test('isMerge, true', $obj->isMerge());

            $obj = new $class(['merge'=>false]);
            $me->test('isMerge, false', $obj->isMerge() === false);

            $me->test('setMerge', method_exists($class, 'setMerge'), function() use($me, $class)
            {
                $obj = new $class(['merge'=>false]);
                $me->test('setMerge, init', $obj->isMerge() === false);

                $obj->setMerge(true);
                $me->test('setMerge, true', $obj->isMerge() === true);

                $obj->setMerge(false);
                $me->test('setMerge, false', $obj->isMerge() === false);
            });
        });

        $me->test('isRender', method_exists($class, 'isRender'), function() use($me, $class)
        {
            $obj = new $class();
            $me->test('isRender, default', $obj->isRender() === false);

            $obj = new $class(['render'=>true]);
            $me->test('isRender, true', $obj->isRender());

            $obj = new $class(['render'=>false]);
            $me->test('isRender, false', $obj->isRender() === false);

            $me->test('setRender', method_exists($class, 'setRender'), function() use($me, $class)
            {
                $obj = new $class(['render'=>false]);
                $me->test('setRender, init', $obj->isRender() === false);

                $obj->setRender(true);
                $me->test('setRender, true', $obj->isRender() === true);

                $obj->setRender(false);
                $me->test('setRender, false', $obj->isRender() === false);
            });
        });

        $me->test('isAutoLoad', method_exists($class, 'isAutoLoad'), function() use($me, $class)
        {
            $obj = new $class();
            $me->test('isAutoLoad, default', $obj->isAutoLoad() === false);

            $obj = new $class(['autoLoad'=>true]);
            $me->test('isAutoLoad, true', $obj->isAutoLoad());

            $obj = new $class(['autoLoad'=>false]);
            $me->test('isAutoLoad, false', $obj->isAutoLoad() === false);

            $me->test('setAutoLoad', method_exists($class, 'setAutoLoad'), function() use($me, $class)
            {
                $obj = new $class(['autoLoad'=>false]);
                $me->test('setAutoLoad, init', $obj->isAutoLoad() === false);

                $obj->setAutoLoad(true);
                $me->test('setAutoLoad, true', $obj->isAutoLoad() === true);

                $obj->setAutoLoad(false);
                $me->test('setAutoLoad, false', $obj->isAutoLoad() === false);
            });
        });

        $this->createUnfinishedTask();
    }

}