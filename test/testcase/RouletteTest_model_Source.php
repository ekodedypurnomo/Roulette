<?php 

class RouletteTest_model_Source extends RouletteUnittest_Model {

    public $name = 'Roulette\Model\Source';

    protected $skip = array('is','isNot');

    public function __construct(){
        parent::__construct();
    }

    public function index(){
        require_once(__DIR__."/uses/student.php");
        $studentC = 'Student';

        require_once(__DIR__."/uses/faculty.php");
        $facultyC = 'Faculty';

        require_once(__DIR__."/uses/studentclass.php");
        $classC = 'StudentClass';

        require_once(__DIR__."/uses/hobby.php");
        $hobbyC = 'Hobby';

        $me = $this;
        $class = $this->name;

        \Roulette\Query\Operation::enableLog();




        // // from model
        // $operation  = \Roulette\Model::load('doe123');

        // // from builder
        // $operation = \Roulette\Query::create('tableName')
        //     ->select('id','name')
        //     ->where()
        //     ->groupBy();

        // // from builder in model
        // $operation = $studentC::query()
        //     ->select('id','name')
        //     ->where()
        //     ->groupBy();

        // from processor
        // $operation  = \Roulette\Query\Operation::createOperation()
        //     ->build(function($qopt)
        //     {
        //         $qopt->setTable('student');
        //         $qopt->select('id','name');
        //     });






        // $q = $studentC::query()
            
        //     ->select('id','name')
            // ->addSelect('gender', 'kelamin')

            // ->where('id', 'doe')
            // ->where('id', '=', 'doe')
            // ->where('id', '>', 1)
            // ->where('id', 'is', 'null')
            // ->where('id', 'is not', 'null')
            // ->where('id', 'between', [1,10])
            // ->where('id', 'in', ['a','b','c'])

            // ->orWhere('name','=','me')
            // ->orWhere('class','=','rpl')
            // ->orWhere(function($query)
            // {
            //     $query
            //     ->where('sex','is not','null')
            //     ->orWhere('sex','IS NULL')
            //     ->orWhere(function($q)
            //     {
            //         $q
            //         ->where('faculty','is',true)
            //         ->andWhere('class','is',false);
            //     })
            //     ;
            // })
            // ->whereNull('name')
            // ->whereNotNull('name')

            // ->orderBy('id')
            // ->orderBy(array('gender'=>'desc'))
            // ->orderBy(array('id','name'))
            // ->orderBy(array('gender')) // test reorder

            // ->groupBy('location')
            // ->having('location','in',['malang','surabaya'])
            // ->having('age','between', [20,50])
            // ;

        // dd($q->getOption()->getWhere(), $w);

        // $select = \Roulette\Query\Operation::createOperation('select')->build(function(){
        // })->getOption();

        // $insert = \Roulette\Query\Operation::createOperation('insert')->build(function(){
        // })->getOption();

        // $update = \Roulette\Query\Operation::createOperation('update')->build(function(){
        // })->getOption();

        // $delete = \Roulette\Query\Operation::createOperation('delete')->build(function(){
        // })->getOption();

        // $other = \Roulette\Query\Operation::createOperation('other')->build(function(){
        // })->getOption();

        // dd($select, $insert, $update, $delete, $other);



        // Roulette\Query::table()->select()->where()->groupBy()->having()->orderBy()->skip()->limit()->get();
        // Roulette\Query::table()->where()->set()->update();
        // Roulette\Query::table()->where()->delete();
        // Roulette\Query::table()->set()->insert();
        // Roulette\Query::table()->truncate();
        // Roulette\Query::table()->drop();

        // $doe = $studentC::load('doe');
        // dump(\Roulette\Query\Operation::getLog());

        // $process = $doe->set('gender','m')->save();

        // dump($doe->getErrorMessages());
        // dump(\Roulette\Query\Operation::getLog());

        // dd(
        //     'Data, Store, Operation :========================================================:', 
        //     $data,
        //     // $store,
        //     \Roulette\Query\Operation::getLog(),
        //     'END DEBUG'
        //     );
        

        $this->createUnfinishedTask();
    }
}