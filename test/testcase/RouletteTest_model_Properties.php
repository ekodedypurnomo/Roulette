<?php

class RouletteTest_model_Properties extends RouletteUnittest_Model {

    public $name = 'Roulette\Model\Properties';

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