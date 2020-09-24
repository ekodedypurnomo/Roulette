<?php

class RouletteTest_model_ViewOption extends RouletteUnittest_Model {

    public $name = 'Roulette\Model\ViewOption';

    protected $skip = array('is','isNot');

    public function __construct() {
        parent::__construct();
    }

    public function index()
    {
        $me = $this;
        $class = $this->name;

        require_once(__DIR__."/uses/student.php");
        $studentC = 'Student';

        require_once(__DIR__."/uses/faculty.php");
        $facultyC = 'Faculty';

        require_once(__DIR__."/uses/studentclass.php");
        $classC = 'StudentClass';

        require_once(__DIR__."/uses/hobby.php");
        $hobbyC = 'Hobby';
        
        // $doe = $studentC::load('doe');
        // 
        // $store = $studentC::view('withFaculty')->find(['id'=>'doe']);
        // $doe = $store->getAt(0);
        // 
        // $data = $store->getData([
        //         // 'fields' => ['id'=>'KODE'],
        //         // 'render' => true,
        //         // 'display' => 'as',
        //         // 'inline' => true,
        //         // 'autoLoad' => true,
        //         'relations'  => [
        //             //'faculty',
        //             'faculty'   =>['inline'=>'facultyRecord'],
        //             //'faculty' =>['inline'=>true, display=>'classRecord'],
        //             'class' =>['merge'=>true, 'fields'=>['id'=>'class_id']],
        //             'class' =>['merge'=>true, 'mergeMask'=>'class_{field}'],
        //             'class' =>['merge'=>'class_{field}'],
        //             'class'     =>['display'=>'classRecord', 'inline'=>true, 'autoLoad'=>true,
        //                 'relations' => [
        //                     'students' => [
        //                         'display'=>'studentsRecords','inline'=>true,'autoLoad'=>true
        //                     ]
        //                 ]
        //             ],
        //             'hobbies'   =>['display'   =>'hobbiesRecords', 'fields'=>['id','name'=>'NAMA'], 'inline'=>true, 'autoLoad' => true]
        //         ]
        //     ]);
        //     
        //     dd($doe->getData());
        //     

        // \Roulette\Query\Operation::enableLog();
        // $source = $studentC::source('full');
        // $source = $studentC::source();

        // $doe = $source->load('doe');
        // $data = $doe->getData([
        //     'relations'  => [
        //         'faculty'=>['inline'=>'facultyRecord'],
        //         // 'faculty',
        //         'class' =>['merge'=>'class_{field}'],
        //         // 'class' =>['id'],
        //         'hobbies' =>['fields'=>['id','name'=>'hobby'], 'autoLoad' => true]
        //     ]
        // ]);

        // dd( 'doe,data', $doe, $data, \Roulette\Query\Operation::getLog() );

        // $me->test('compile', method_exists($class, 'compile'), function() use($me, $class)
        // {
        //     $obj = new $class;
        //     $replacer = array('name'=>'john','gender'=>'male');
            
        //     $result_should = 'name : '.$replacer['name'].' gender : '.$replacer['gender'];
        //     $me->test('compile, string', $class::compile('name : {name} gender : {gender}')->apply($replacer) == $result_should);

        //     $me->test('compile, array', $class::compile(array('name : {name}',' ','gender : {gender}'))->apply($replacer) == $result_should );
        // });

        $this->createUnfinishedTask();
    }

}