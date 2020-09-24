<?php

class RouletteTest_ManagedCollection extends RouletteUnittest_Model {

    public $name = 'Roulette\ManagedCollection';
    public $skip = false;
    public $should = array(
        'acceptableKey',
        'acceptableValue',
        'acceptable',
        'setAcceptableKeys',
        'setAcceptableValues',
        'set',
        'add',
        'isRemoveableKey'
        );

    public function __construct() {
        parent::__construct();
    }

    public function index()
    {
        $me = $this;
        $class = $this->name;
        
        $this->createUnfinishedTask();
    }

}