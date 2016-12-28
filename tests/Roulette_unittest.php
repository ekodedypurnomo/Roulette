<?php

class Roulette_unittest
{
    public $testerdir = 'tester'.DIRECTORY_SEPARATOR;
    public $testclass = array(
        'Roulette_test',
        'RouletteProxy_test'
    );

    public $connection = null;

    protected $tested = array();
    protected $result = null;

    function __construct(){
        $ds = DIRECTORY_SEPARATOR;
        $back = '..'.$ds;
        require(__DIR__.$ds.$back.$back.$back.'SimpleTester'.$ds.'SimpleTester.php');
    }

    function test(){
        $this->tested = array();
        foreach ($this->testclass as $i => $class) {
            require(__DIR__.DIRECTORY_SEPARATOR.$this->testerdir.$class.'.php');
            $this->tested[$class] = $tester = new $class;
            $tester->connection = $this->connection;
            $tester->test();
        }
    }

    function result(){
        return $this->result;
    }

    function printResult(){
        // echo 'as';
    }
}

class Roulette_unittest_model{
    public $title = '';
    public $tester = null;

    function __construct(){}

    function getTester(){}
    function test(){}
    function notTested(){}
    function getResult(){}
}

// class Roulette_unittest_model {

//     public $tested_class = null;
// 	public $function_prefix = 'test_'; // function name prefix to get the result
// 	public $title = 'Roulette_Unittest_Model'; // default title for each class test
//     public $strict = true; // if test function return empty it will be marked as false result

//     public function __construct(){
//         parent::__construct();
//     }

//     public function index(){
//         return $this->test();
//     }

//     public function test($print_output = false){
//     	$me = $this;
    	 
//     	$report = "<hr/><h3>".$this->title."</h3>";

//     	$test_functions = array_filter(get_class_methods($this), function($fn) use($me){
//     		return strpos($fn, $me->function_prefix) === 0;
//     	});
//     	foreach ($test_functions as $idx => $fn) {
//     		$test_result = $this->$fn();

//             if(empty($test_result)){
//                 $test_result = array(false);
//             }

//             if( (! is_array($test_result)) || (is_array($test_result) and array_keys($test_result) === range(0, count($test_result) - 1)) ){
//     			$test_result = array(
//     				'test'=>'Test function {code}'.str_replace($this->function_prefix, '', $fn).'{/code}',
//     				'result'=>$test_result
//     			);
//     		}

//             if(is_array($test_result['result'])){
//                 $test_result_passed = array_filter($test_result['result'], function($result){
//                     return $result === true;
//                 });
//                 $test_result['result'] = ($test_result['result'] === $test_result_passed);
//                 if($this->strict === true and empty($test_result['result']) ){
//                     $test_result['result'] = false;
//                 }
//             }

//     		$report .= $this->unit->run($test_result['result'], TRUE, $test_result['test']);
//     	}
//     	if($print_output === true){
//     		echo $report;
//     	}
//     	return $report;
//     }

// }