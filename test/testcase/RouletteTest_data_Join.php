<?php

class RouletteTest_data_Join extends RouletteUnittest_Model {

    public $name = 'Roulette\Data\Join';

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