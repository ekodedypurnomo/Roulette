<?php

class RouletteTest_query_option_Option extends RouletteUnittest_Model {

    public $name = 'Roulette\Query\Option\Option';

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