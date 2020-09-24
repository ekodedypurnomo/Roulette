<?php

class RouletteTest_query_Builder extends RouletteUnittest_Model {

    public $name = 'Roulette\Query\Builder';

    protected $skip = array('is','isNot');

    public function __construct() {
        parent::__construct();
    }

    public function index()
    {
        $me = $this;
        $class = $this->name;
        
        // require_once(__DIR__."/uses/student.php");
        // $studentC = 'Student';

        // require_once(__DIR__."/uses/faculty.php");
        // $facultyC = 'Faculty';

        // require_once(__DIR__."/uses/hobby.php");
        // $hobbyC = 'Hobby';

        // $q = $class::table('students')
        //     ->where('name','=','me')
        //     ->orderBy('name','desc')
        //     ->get();

        $this->createUnfinishedTask();
    }

}