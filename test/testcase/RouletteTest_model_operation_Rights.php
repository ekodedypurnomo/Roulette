<?php

class RouletteTest_model_operation_Rights extends RouletteUnittest_Model {

    public $name = 'Roulette\Model\Operation\Rights';

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