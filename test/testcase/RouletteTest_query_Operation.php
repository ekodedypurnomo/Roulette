<?php 

class RouletteTest_query_Operation extends RouletteUnittest_Model
{
    public $name = 'Roulette\Query\Operation';

    protected $skip = array('is','isNot');
    
    public function __construct() {
        parent::__construct();
    }
    
    function index() {
        $me = $this;
        $class = $this->name;

        $operation = new $class($class::getFrameworkTunel());

        $me->test('enabledisableOperationLog', method_exists($class, 'enableOperationLog') && method_exists($class, 'disableOperationLog') && method_exists($class, 'operationLogging'), function() use($me, $class)
        {
            $me->test('enabledisableOperationLog, default', $class::operationLogging() === false );
            $me->test('enabledisableOperationLog, enabling', $class::enableLog() === $class && $class::operationLogging() === true );
            $me->test('enabledisableOperationLog, disabling', $class::disableLog() === $class && $class::operationLogging() === false );
        });

        $me->test('setgetTunel', method_exists($class, 'setTunel') && method_exists($class, 'getTunel'), function() use($me, $class)
        {
            $oldTunel = $class::getTunel();

            $tunnel = new \Roulette\Tunel\Codeigniter3;
            $me->test('setgetTunel, set', $class::setTunel($tunnel) === $class);
            $me->test('setgetTunel, set, tunnel', $class::tunnel($tunnel) === $class);
            $me->test('setgetTunel, get', $class::getTunel() === $tunnel);
            $me->test('setgetTunel, get, tunnel', $class::tunnel() === $tunnel);

            // rolling back
            $class::tunnel($oldTunel);
        });

        $me->test('getLog', method_exists($class, 'getLog'), function() use($me, $class)
        {
            $class::enableLog();
            
            $me->test('getLog, default', empty($class::getLog()) == true );
            
            $operation = new \Roulette\Query\Operation($class::getFrameworkTunel());
        
            $class::add($operation);
            $me->test('getLog, add', empty($class::getLog()) == false );
        
            $me->test('getLog, getLastOperation', $class::getLastLog() === $operation);
        });

        // set default tunnel
        $class::useFrameworkTunel();

        // $me->test('query', method_exists($class, 'query'), function() use($me, $class)
        // {
        //     $table = "student";

        //     $query = 'select * from '.$table.'';
        //     $operation = $class::query($query);
        //     $me->test('query, query action', $operation->action === 'query');
        //     $me->test('query, query query', $operation->query === $query);

        //     $randomId = rand(1000, 9999);

        //     $queryInsert = 'insert into '.$table.' (id,name) values("'.$randomId.'", "me")';
        //     $operationInsert = $class::query($queryInsert);
        //     $me->test('query, insert action', $operationInsert->action === 'query');
        //     $me->test('query, insert query', $operationInsert->query === $queryInsert);
        //     $me->test('query, insert affectedRows', $operationInsert->affectedRows == 0); // insert hasno affected

        //     $queryUpdate = 'update '.$table.' SET name="you" where id="'.$randomId.'"';
        //     $operationUpdate = $class::query($queryUpdate);
        //     $me->test('query, update action', $operationUpdate->action === 'query');
        //     $me->test('query, update query', $operationUpdate->query === $queryUpdate);
        //     $me->test('query, update affectedRows', $operationUpdate->affectedRows >= 1); // update has affected

        //     $queryDelete = 'delete from '.$table.' where id="'.$randomId.'"';
        //     $operationDelete = $class::query($queryDelete);
        //     $me->test('query, delete action', $operationDelete->action === 'query');
        //     $me->test('query, delete query', $operationDelete->query === $queryDelete);
        //     $me->test('query, delete affectedRows', $operationDelete->affectedRows >= 1); // delete has affected
        // });

        // $me->test('select', method_exists($class, 'select'), function() use($me, $class)
        // {
        //     $table = 'student';
        //     $fieldName = 'name';
        //     $fieldValue = 'john doe';
            
        //     $operation1 = $class::select(array(
        //         'table'=>$table,
        //         'fields'=>$fieldName
        //     ));

        //     $me->test('select, fields, query', preg_match('/'.$fieldName.'/i', $operation1->query));
        //     $me->test('select, fields, countResult', count($operation1->result[0]) === 1);
        //     $me->test('select, fields, fieldCheck', array_key_exists($fieldName, $operation1->result[0]) === true );

        //     $operation2 = $class::select(array(
        //         'table'=>$table,
        //         'condition'=>array($fieldName=>$fieldValue)
        //     ));
        //     $me->test('select, condition, query', preg_match('/where/i',$operation2->query));

        //     $operation3 = $class::select(array(
        //         'table'=>$table,
        //         'sort'=>array($fieldName=>'desc')
        //     ));
        //     $end_result = end($operation3->result);
        //     $me->test('select, sort, query', preg_match('/order/i',$operation3->query));
            
        //     $operation4 = $class::select(array(
        //         'table'=>$table,
        //         'take'=>2
        //     ));
        //     $me->test('select, limit, query', preg_match('/limit/i',$operation4->query));

        //     $operation5 = $class::select(array(
        //         'table'=>$table,
        //         'take'=>1,
        //         'skip'=>0
        //     ));
        //     $me->test('select, skip, query', preg_match('/offset/i',$operation5->query));
        // });

        // $me->test('selectOne', method_exists($class, 'selectOne'), function() use($me, $class)
        // {
        //     $table = "student";
            
        //     $operation1 = $class::selectOne(array(
        //         'table'=>$table
        //     ));

        //     $me->test('selectOne, fields, countResult', count($operation1->result) === 1);
        // });

        // $me->test('exist', method_exists($class, 'exist'), function() use($me, $class)
        // {
        //     $table = "student";

        //     $operationExist = $class::exist(array(
        //         'table'=>$table
        //     ));

        //     $operationExistBoolean = $class::exist(array(
        //         'table'=>$table
        //     ), false);

        //     $operationValidator = $class::select(array(
        //         'table'=>$table
        //     ));

        //     $me->test('exist, numeric', $operationExist->result === count($operationValidator->result) );
        //     $me->test('exist, boolean', $operationExistBoolean->result === true );
        // });

        // $me->test('insert', method_exists($class, 'insert'), function() use($me, $class)
        // {
        //     $table = "student";
        //     $randomId = md5(microtime(true));

        //     $operationInsert = $class::insert(array(
        //         'table'=>$table,
        //         'data'=>array(
        //             'id'=>$randomId, 
        //             'name'=>$randomId
        //         )                
        //     ));

        //     $operationValidator = $class::selectOne(array(
        //         'table'=>$table,
        //         'condition'=>array(
        //             'id'=>$randomId
        //         )
        //     ));

        //     $operationCleanup = $class::delete(array(
        //         'table'=>$table,
        //         'condition'=>array(
        //             'id'=>$randomId
        //         )
        //     ));

        //     $me->test('insert, existense', count($operationValidator->result) );
        //     $me->test('insert, validity', count($operationValidator->result) && $operationValidator->result[0]->id == $randomId );
        // });

        // $me->test('update', method_exists($class, 'update'), function() use($me, $class)
        // {
        //     $table = "student";
        //     $randomId = md5(microtime(true));
        //     $updateRandomId = md5(microtime(true)."updated");

        //     $operationPrepration = $class::insert(array(
        //         'table'=>$table,
        //         'data'=>array(
        //             'id'=>$randomId, 
        //             'name'=>$randomId
        //         )                
        //     ));

        //     $operationUpdate = $class::update(array(
        //         'table'=>$table,
        //         'condition'=>array(
        //             'id'=>$randomId
        //         ),
        //         'data'=>array(
        //             'name'=>$updateRandomId
        //         )    
        //     ));

        //     $operationValidator = $class::selectOne(array(
        //         'table'=>$table,
        //         'condition'=>array(
        //             'id'=>$randomId
        //         )
        //     ));

        //     $operationCleanup = $class::delete(array(
        //         'table'=>$table,
        //         'condition'=>array(
        //             'id'=>$randomId
        //         )
        //     ));

        //     $me->test('update, existense', count($operationValidator->result) && $operationValidator->result[0]->id == $randomId );
        //     $me->test('update, validity', count($operationValidator->result) && $operationValidator->result[0]->name == $updateRandomId );
        //     $me->test('update, affectedRows', $operationUpdate->affectedRows == 1 );
        // });

        // $me->test('delete', method_exists($class, 'delete'), function() use($me, $class)
        // {
        //     $table = "student";
        //     $randomId = md5(microtime(true));
            
        //     $operationPrepration = $class::insert(array(
        //         'table'=>$table,
        //         'data'=>array(
        //             'id'=>$randomId, 
        //             'name'=>$randomId
        //         )                
        //     ));

        //     $operationDelete = $class::delete(array(
        //         'table'=>$table,
        //         'condition'=>array(
        //             'id'=>$randomId
        //         )
        //     ));

        //     $operationValidator = $class::selectOne(array(
        //         'table'=>$table,
        //         'condition'=>array(
        //             'id'=>$randomId
        //         )
        //     ));

        //     $me->test('delete, existense', !count($operationValidator->result) );
        //     $me->test('delete, affectedRows', $operationDelete->affectedRows == 1 );
        // });

        $me->test('has public property', true, function() use($me, $class)
        {
            $me->test('has public property, option', property_exists($class, 'option'));
            $me->test('has public property, error', property_exists($class, 'error'));
            $me->test('has public property, query', property_exists($class, 'query'));
            $me->test('has public property, affectedRows', property_exists($class, 'affectedRows'));
            $me->test('has public property, result', property_exists($class, 'result'));
            $me->test('has public property, executeTime', property_exists($class, 'executeTime'));
        });

        $me->test('isSuccess', method_exists($class, 'isSuccess'), function() use($me, $class, $operation)
        {
            $me->test('isSuccess, without tunnel', $operation->isSuccess() === false);           
            $operation->execute();
            $me->test('isSuccess, without tunnel execute', $operation->isSuccess() === false);

            $operation->success = true; // force value
            $me->test('isSuccess, successfull', $operation->isSuccess() === true);
        });

        $me->test('getRecords', method_exists($class, 'getRecords'), function() use($me, $class, $operation)
        {
            $me->test('getRecords, value', is_array($operation->getRecords() === true) );
        });
    
        $this->createUnfinishedTask();   
    }
}