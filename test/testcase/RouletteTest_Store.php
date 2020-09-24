<?php 

use Roulette\Collection;

class Roulette_data_store_test extends RouletteUnittest_Model {

    public $name = '\Roulette\Collection';

    protected $skip = array('is','isNot');

    public function __construct(){
        parent::__construct();
    }

    function index()
    {
        $this->skip = get_class_methods(Collection::class);
    }

    public function run_test(){
        $students_model =& \Roulette\Helper::require_model('test/require/students_model');
        $obj =& \Roulette\Helper::require_model('test/require/students_collection');

        $report = "<hr/><h3>".$this->title."</h3>";

        $report .= $this->unit->run($this->test_get_model($obj), TRUE,
            'Test function {code}get_model{/code}');

        $report .= $this->unit->run($this->test_get_view($obj), TRUE,
            'Test function {code}get_view{/code}');

        $report .= $this->unit->run($this->test_set_limit($obj), TRUE,
            'Test function {code}set_limit{/code} (hooks: get_limit)');

        $report .= $this->unit->run($this->test_set_start($obj), TRUE,
            'Test function {code}set_start{/code} (hooks: get_start)');

        $report .= $this->unit->run($this->test_reindex($obj), TRUE,
            'Test function {code}reindex{/code} (hooks: remove_all, load_all, remove_at, get_at)');
        
        $report .= $this->unit->run($this->test_get_records($obj), TRUE,
            'Test function {code}get_records{/code} (hooks: remove_all, load_all)');

        $report .= $this->unit->run($this->test_get_at($obj), TRUE,
            'Test function {code}get_at{/code} (hooks: remove_all, load_all)');

        $report .= $this->unit->run($this->test_set_at($obj), TRUE,
            'Test function {code}set_at{/code} (hooks: remove_all, get_at)');

        $report .= $this->unit->run($this->test_get_data($obj), TRUE,
            'Test function {code}get_data{/code} (hooks: remove_all, load_all)');

        $report .= $this->unit->run($this->test_get_count($obj), TRUE,
            'Test function {code}get_count{/code} (hooks: remove_all, load_all)');

        $report .= $this->unit->run($this->test_load_data($obj), TRUE,
            'Test function {code}load_data{/code} (hooks: remove_all)');

        $report .= $this->unit->run($this->test_add($obj), TRUE,
            'Test function {code}add{/code} (hooks: remove_all)');

        $report .= $this->unit->run($this->test_remove($obj), TRUE,
            'Test function {code}remove{/code} (hooks: remove_all, load_all, get_records)');

        $report .= $this->unit->run($this->test_remove_all($obj), TRUE,
            'Test function {code}remove_all{/code} (hooks: load_all, get_records)');

        $report .= $this->unit->run($this->test_remove_at($obj), TRUE,
            'Test function {code}remove_at{/code} (hooks: load_all, get_records)');

        $report .= $this->unit->run($this->test_remove_by($obj), TRUE,
            'Test function {code}remove_by{/code} (hooks: load_all, get_records)');

        $report .= $this->unit->run($this->test_each($obj), TRUE,
            'Test function {code}each{/code} (hooks: remove_all, load_all)');

        $report .= $this->unit->run($this->test_load($obj), TRUE,
            'Test function {code}load{/code} (hooks: remove_all, get_records, get_records_count');

        $report .= $this->unit->run($this->test_load_all($obj), TRUE,
            'Test function {code}load_all{/code} (hooks: remove_all, get_records)');

        $report .= $this->unit->run($this->test_load_by($obj), TRUE,
            'Test function {code}load_by{/code} (hooks: remove_all)');

        $report .= $this->unit->run($this->test_load_one($obj), TRUE,
            'Test function {code}load_one{/code} (hooks: remove_all)');

        $report .= $this->unit->run($this->test_search($obj), TRUE,
            'Test function {code}search{/code} (hooks: remove_all, load_all)');

        $report .= $this->unit->run($this->test_search_by($obj), TRUE,
            'Test function {code}search_by{/code} (hooks: remove_all, load_all)');

        $report .= $this->unit->run($this->test_search_by_id($obj), TRUE,
            'Test function {code}search_by_id{/code} (hooks: remove_all, load_all)');

        $report .= $this->unit->run($this->test_index_of($obj), TRUE,
            'Test function {code}index_of{/code} (hooks: remove_all, load_all, get_records)');

        $report .= $this->unit->run($this->test_index_of_id($obj), TRUE,
            'Test function {code}index_of_id{/code} (hooks: remove_all, load_all, get_records)');

        $report .= $this->unit->run($this->test_sort($obj), TRUE,
            'Test function {code}sort{/code} (hooks: remove_all, load_all)');

        $report .= $this->unit->run($this->test_sort_by($obj), TRUE,
            'Test function {code}sort_by{/code} (hooks: remove_all, load_all, sort)');

        $report .= $this->unit->run($this->test_render($obj), TRUE,
            'Test function {code}render{/code} (hooks: remove_all, load_all, search_by_id)');

        $report .= $this->unit->run($this->test_revert($obj), TRUE,
            'Test function {code}revert{/code} (hooks: remove_all, load_all, search_by_id, render)');

        $report .= $this->unit->run($this->test_sum($obj), TRUE, 
            'Test function {code}sum{/code} (hooks: remove_all, load_all, each)');

        $report .= $this->unit->run($this->test_average($obj), TRUE, 
            'Test function {code}average{/code} (hooks: remove_all, load_all, each)');

        $report .= $this->unit->run($this->test_max($obj), TRUE, 
            'Test function {code}max{/code} (hooks: remove_all, load_all, each)');

        $report .= $this->unit->run($this->test_min($obj), TRUE, 
            'Test function {code}min{/code} (hooks: remove_all, load_all, each)');

        $report .= $this->unit->run($this->test_get_first($obj), TRUE, 
            'Test function {code}get_first{/code} (hooks: remove_all, load_all, get_records)');

        $report .= $this->unit->run($this->test_get_last($obj), TRUE, 
            'Test function {code}get_last{/code} (hooks: remove_all, load_all, get_records)');

        if($output) echo $report;
        return $report;
    }

    function test_get_model($obj){
        $test[] = is_a($obj->get_model(), '\Roulette\Model') );
        
        return ( $a );
    }

    function test_get_view($obj){
        $test[] = is_array($obj->get_view('lite')) === true );
        $test[] = $obj->get_view('xlite') === null );
        
        return ( $a and $b );
    }

    function test_set_limit($obj){
        $test[] = $obj->get_limit() == null );
        $obj->set_limit(array());
        $test[] = $obj->get_limit() == null );
        $obj->set_limit(1000);
        $test[] = $obj->get_limit() === 1000 );
        $obj->set_limit('2000');
        $test[] = $obj->get_limit() === 2000 );
        $obj->set_limit('2x00');
        $test[] = $obj->get_limit() === 2 );
        
        return ( $a and $b and $c and $d and $e );
    }

    function test_set_start($obj){
        $test[] = $obj->get_start() === 0 );
        $obj->set_start(array());
        $test[] = $obj->get_start() === 0 );
        $obj->set_start(1000);
        $test[] = $obj->get_start() === 1000 );
        $obj->set_start('2000');
        $test[] = $obj->get_start() === 2000 );
        $obj->set_start('2x00');
        $test[] = $obj->get_start() === 2 );
        
        return ( $a and $b and $c and $d and $e );
    }

    function test_reindex($obj)
    {
        $obj->remove_all();
        $obj->load_all();
        $test[] = is_null($obj->get_at(0)) === false);
        $obj->remove_at(0);
        $test[] = is_null($obj->get_at(0)) === true);
        return ( $a and $b );
    }

    function test_get_records($obj){
        $obj->remove_all();
        $test[] = is_array($obj->get_records()) and count($obj->get_records()) == 0);
        $obj->load_all();
        $test[] = is_array($obj->get_records()) and count($obj->get_records()) > 0);
        $var_record = $obj->get_records();
        $test[] = is_a($var_record[0], '\Roulette\Model') === true);
        
        return ( $a and $b and $c ); 
    }

    function test_get_at($obj){
        $obj->remove_all();
        $obj->load_all();
        $records =& $obj->get_records();
        $at = $obj->get_at(1);
        $test[] = $records[1] === $at );
        return ( $a );
    }

    function test_set_at($obj){
        $obj->remove_all();
        $record = $obj->get_model()->create();
        $obj->set_at(1, $record);
        $test[] = $obj->get_at(1) === $record );
        return ( $a );
    }

    function test_get_data($obj){
        $obj->remove_all();
        $test[] = is_array($obj->get_data()) and count($obj->get_data()) == 0);
        $obj->load_all();
        $test[] = is_array($obj->get_data()) and count($obj->get_data()) > 0);
        $test[] = \Roulette\Collection::isAssoc($obj->get_data()) === false);
        
        return ( $a and $b and $c );
    }

    function test_get_count($obj){
        $obj->remove_all();
        $test[] = $obj->get_count() == 0);
        $obj->load_all();
        $test[] = $obj->get_count() > 0);
        $obj->remove_all();
        $test[] = $obj->get_count() == 0);
        
        return ( $a and $b and $c );
    }

    function test_load_data($obj){
        $obj->remove_all();

        $obj->load_data(array($obj->get_model()->create()));
        $test[] = $obj->get_count() >= 1 );

        $obj->load_data(array(array()));
        $test[] = $obj->get_count() >= 1 );

        $obj->load_data("select * from students where id='81131000' ");
        $test[] = $obj->get_count() > 0 );
        
        $records = $obj->get_records();
        $records_loaded = $obj->get_model()->load('81131000');
        $test[] = $records[0]->get_data() === $records_loaded->get_data() );

        $obj->load_data(array($obj->get_model()->create()), true);
        $obj->load_data(array($obj->get_model()->create()), false);
        $test[] = $obj->get_count() == 2 );
        
        return ( $a and $b and $c and $d and $e );
    }

    function test_add($obj){
        $obj->remove_all();
        $recs_before = $obj->get_records();
        $r = $obj->get_model()->load('8113100020');
        $obj->add($r);
        $recs_after = $obj->get_records();
        $test[] = count($recs_after) > count($recs_before) );
        
        return ( $a );
    }

    function test_remove($obj){
        $obj->load_all();
        $recs = $obj->get_records(); 
        $r1 = $recs[0];
        $obj->remove($r1);
        $recs = $obj->get_records(); ;
        $test[] = $r1 !== null );
        $test[] = empty($recs[0]) === true );
        return ( $a and $b and $a === true);
    }

    function test_remove_all($obj){
        $obj->remove_all();
        $test[] = is_array($obj->get_records()) and count($obj->get_records()) == 0);
        $obj->load_all();
        $test[] = is_array($obj->get_records()) and count($obj->get_records()) > 0);
        $obj->remove_all();
        $test[] = is_array($obj->get_records()) and count($obj->get_records()) == 0);
        
        return ( $a and $b and $c );
    }

    function test_remove_at($obj){
        $obj->load_all();
        $test[] = is_null($obj->get_at(0)) === false);
        $obj->remove_at(0);    
        $test[] = is_null($obj->get_at(0)) === true);    
        
        return ( $a and $b );
    }

    function test_remove_by($obj){
        $obj->load_all();
        $count_1 = $obj->get_count();
        $search_by = $obj->search(array('faculty'=>'TI'));
        $obj->remove_by('faculty', 'TI');
        $count_2 = $obj->get_count();

        $test[] = $count_2 === ($count_1-count($search_by)) );

        return ( $a and $a === true );
    }

    function test_each($obj){
        $obj->remove_all();
        $obj->load_all();
        $each = array();
        $obj->each(function($record, $i, $records) use(&$each){
            $each[$i] = $record;
        });
        $test[] = is_array($each) and count($each) > 0 );
        $test[] = is_a($each[0], '\Roulette\Model') );
        
        return ( $a and $b );
    }

    function test_load($obj){
        $obj->remove_all();
        $test[] = is_array($obj->get_records()) and count($obj->get_records()) == 0);
        $obj->load();
        $count1 = count($obj->get_records());
        $test[] = is_array($obj->get_records()) and count($obj->get_records()) > 0);
        
        $obj->remove_all();
        $obj->load(array(
            $obj->get_model()->get_primary() => '8113100020'
        ));
        $count2 = count($obj->get_records());
        $test[] = $count1 > $count2 and $count2 == 1);
        return ( $a and $b and $c );
    }

    function test_load_all($obj){
        $obj->remove_all();
        $test[] = is_array($obj->get_records()) and count($obj->get_records()) == 0);
        $obj->load_all();
        $test[] = is_array($obj->get_records()) and count($obj->get_records()) > 0);
        return ( $a and $b );
    }

    function test_load_by($obj){
        $obj->remove_all();
        $test[] = is_array($obj->get_records()) and count($obj->get_records()) == 0);
        $obj->load_by('id', '8113100020');
        $test[] = is_array($obj->get_records()) and count($obj->get_records()) > 0);

        return ( $a and $b );
    }

    function test_load_one($obj){
        $obj->remove_all();
        $test[] = is_array($obj->get_records()) and count($obj->get_records()) == 0);
        $obj->load_one(array(
            'faculty'=>'TI'
        ));
        $test[] = is_array($obj->get_records()) and count($obj->get_records()) === 1);
        return ( $a and $b );
    }

    function test_search($obj){
        $obj->remove_all();
        $obj->load();
        $result = $obj->search(array(
            'faculty'=>'TI'
        ));
        $test[] = count($result) == 2 );
        return ( $a );
    }

    function test_search_by($obj){
        $obj->remove_all();
        $obj->load();
        $result1 = $obj->search_by('faculty', 'TI');
        $result2 = $obj->search_by(array('faculty'=>'TI'));
        $test[] = count($result1) == 2 );
        $test[] = count($result2) != 2 );
        return ( $a and $b );
    }

    function test_search_by_id($obj){
        $obj->remove_all();
        $obj->load();
        $result = $obj->search_by_id('8113100020');
        $test[] = count($result) == 1 );
        return ( $a );
    }

    function test_index_of($obj){
        $obj->remove_all();
        $obj->load_all();
        $records = $obj->get_records();
        $test[] = $obj->index_of($records[1]) === 1 );
        $test[] = $obj->index_of($records[0]) === 0 );
        return ( $a and $b );
    }

    function test_index_of_id($obj){
        $obj->remove_all();
        $obj->load_all();
        $records = $obj->get_records();
        $test[] = $obj->index_of_id($records[1]->get_id()) === 1 );
        $test[] = $obj->index_of_id($records[0]->get_id()) === 0 );
        return ( $a and $b );
    }

    function test_sort($obj){
        $obj->remove_all();
        $obj->load_all();

        $obj->sort('faculty');
        $records_a = $obj->get_records();

        $obj->sort(array('name', 'id'=>'desc'));
        $records_b = $obj->get_records();
        
        $test[] = $records_a !== $records_b);

        return ( $a === true );
    }

    function test_sort_by($obj){
        $obj->remove_all();
        $obj->load_all();

        $obj->sort('name');
        $records_a = $obj->get_records();

        $obj->sort_by('name');
        $records_b = $obj->get_records();
        
        $test[] = $records_a == $records_b);

        return ( $a === true );
    }

    function test_render($obj){
        $obj->remove_all();
        $obj->load_all();
        $obj->render();
        $r1 = $obj->search_by_id('8113100020');
        $R1 = $obj->get_model()->load('8113100020');
        $R1->render();
        $test[] = $r1->get('password') === $R1->get('password') and $R1->get('password') == '[encrypted]');
        return ( $a );
    }

    function test_revert($obj){
        $obj->remove_all();
        $obj->load_all();
        $obj->render();
        $obj->revert();
        $r1 = $obj->search_by_id('8113100020');
        $R1 = $obj->get_model()->load('8113100020');
        $test[] = $r1->get('password') === $R1->get('password') and $r1->get('password') != '[encrypted]');
        return ( $a );
    }

    function test_sum($obj){
        $obj->remove_all();
        $sum = 0;
        $obj->each(function($record, $i, $records) use(&$sum){
            $sum += $record->get('age');
        });
        $test[] = $sum == $obj->sum('age') );
        return ($a and $a === true);
    }

    function test_average($obj){
        $obj->remove_all();
        $obj->load_all();
        $sum = 0;
        $obj->each(function($record, $i, $records) use(&$sum){
            $sum += $record->get('age');
        });
        $avg = $sum / $obj->get_count();
        $test[] = $avg == $obj->average('age') );
        return ($a and $a === true);
    }

    function test_max($obj){
        $obj->remove_all();
        $obj->load_all();
        $max = null;
        $obj->each(function($record, $i, $records) use(&$max){
            if( $record->get('age') >= $max ) $max = $record->get('age');
        });
        $test[] = $max == $obj->max('age') );
        return ($a and $a === true);
    }

    function test_min($obj){
        $obj->remove_all();
        $obj->load_all();
        $min = null;
        $obj->each(function($record, $i, $records) use(&$min){
            if( $record->get('age') <= $min ) $min = $record->get('age');
        });
        $test[] = $min == $obj->min('age') );
        return ($a and $a === true);
    }

    function test_get_first($obj){
        $obj->remove_all();
        $obj->load_all();
        $records = $obj->get_records();
        $first = $records[0];
        $test[] = $obj->get_first() === $first);
        return ( $a and $a === true );
    }

    function test_get_last($obj){
        $obj->remove_all();
        $obj->load_all();
        $records = $obj->get_records();
        $last = $records[$obj->get_count() - 1];
        $test[] = $obj->get_last() === $last);
        return ( $a and $a === true );
    }

}