<?php

class RouletteTest_model_field_Validation extends RouletteUnittest_Model {

    public $name = 'Roulette\Model\Field\Validation';

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