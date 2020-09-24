<?php

class RouletteTest_model_association_HasMany extends RouletteUnittest_Model {

    public $name = 'Roulette\Model\Association\HasMany';

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