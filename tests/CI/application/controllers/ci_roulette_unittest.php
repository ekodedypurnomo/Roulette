<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CI_roulette_unittest extends CI_Controller{

    function __construct(){
        parent::__construct();
        $this->load->database();
        $this->load->library('Roulette');
    }
    function index(){
        require('../Roulette_unittest.php');
        $tester = new Roulette_unittest;
        $tester->connection = $this->db;
        $tester->test();
        $tester->printResult();
    }
}

// class Roulette_unittest_x extends CI_Controller {

//     protected $test_model_dir = 'test/';
//     protected $test_model = array(
//         'roulette_test',
//         'rouletteproxy_test',
//     );
//     protected $template_report = '
//         <table style="width:100%; font-size:small; margin:10px 0; border-collapse:collapse; border:1px solid #CCC;">
//             {rows}
//                 <tr>
//                     <td style="text-align: left; border-bottom:1px solid #CCC;">{result}</td>
//                 </tr>
//             {/rows}
//         </table>
//     ';

//     public function __construct(){
//         parent::__construct();
//         $this->load->library('parser');
//         $this->load->library('unit_test');
//         $this->load->library('roulette');
//         $this->load->model('roulette_unittest_model');
//         foreach ($this->test_model as $key => $model) {
//             $this->load->model($this->test_model_dir.$model);
//         }
//     }

//     public function index(){
//         $this->test();
//     }

//     public function test( $test = null ){
//         $this->unit->set_template($this->template_report);
//         $this->unit->set_test_items(array('test_name','result')); //'file' 'notes'

//         // init
//         $classes = array();
//         $classes_count = count($this->test_model);
//         $passed = $failed = $total = 0;
        
//         $str = '';
//         $start_time = round(microtime(true)*1000);
//         $test_model = empty($test) ? $this->test_model : array($test.'_test');

//         foreach ($test_model as $key => $model) {
//             $classes[] = $this->{$model}->title;
//         }
//         foreach ($test_model as $key => $model) {
//             $str .= $this->{$model}->test(false);
//         }
//         foreach ($this->unit->result() as $key => $value) {
//             $total++;
//             if( strtolower($value['Result']) == 'passed' ){
//                 $passed++; 
//             }else{
//                 $failed++;
//             }
//         }
//         $end_time = round(microtime(true)*1000);
//         $long_time = ($end_time - $start_time)/1000;

//         $str = "<h3>
//             Roulette Unit Testing<br/>
//             <span style='font-style:italic; font-size:13px; color:gray'>
//                 completed in ".$long_time." second
//                 (result: 
//                 <!--span style='color: #000000;'>".$classes_count." classes</span-->".$classes_count." classes,
//                 <!--span style='color: #000000;'>".$total." test</span-->".$total." test,
//                 <!--span style='color: #0C0;'>".$passed." passed</span-->".$passed." passed, 
//                 <!--span style='color: #E00;'>".$failed." failed</span-->".$failed." failed
//                 )
//             </span>
//             <br/>
//             <span style='font-style:italic; font-size:13px; color:gray'>Class tested:<br/>".implode(',<br/> ', $classes)."</span>
//             </h3>".$str;
//         $str = preg_replace('/\{code\}(.*?)\{\/code\}/si', '<span style="font-family: monospace; font-size: larger;">${1}</span>', $str);
        
//         echo $str;
//     }

// }