<?php

class RouletteTest_Actor extends RouletteUnittest_Model {

    public $name = 'Roulette\Actor';

    protected $skip = true;
    protected $should = array('can');

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