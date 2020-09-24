<?php

class RouletteTest_model_Prototype extends RouletteUnittest_Model {

    public $name = 'Roulette\Model\Prototype';

    protected $skip = array('is','isNot');

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