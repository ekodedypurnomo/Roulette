<?php

class Roulette_test extends Roulette_unittest_model {
	public $title = 'Test Class: Roulette';

	public $unittest = null;

	public $testFucntion = array(

	);
 	
 	function __construct() {
 		parent::__construct();
 	}

 	function getTester(){
 		if(!$this->unittest) $this->unittest = new SimpleSubtester($this->title);
 		return $this->unittest;
 	}

	function index(){
		$this->test();
	}

	function notTested(){}

	function getResult(){
		return $this->gettester()->result();
	}

	function test(){
		$tester = $this->getTester();
		
		$tester->test('configure', function($tester){
			$test = array();

			$r = new Roulette();
			$test[] = empty($r->confi1);

			$test[] = $r->configure() === $r;

			$r->configure(array(
				'config1'=>'someValue'
			));
			$test[] = $r->config1 === 'someValue';

			$r->configure(array(
				'config1'=>'someValue_edited'
			));
			$test[] = $r->config1 === 'someValue_edited';

			$r->configure(array(
				'_config2'=>'someValue'
			));
			$test[] = empty($r->_confi2);

			return !in_array(false, $test);
		});

		$tester->test(null,function($tester){
			$test = array();

			$r = new Roulette();
			$test[] = $r->config('config_one') === null;

			$r->config('config_one', 'one');
			$test[] = $r->config('config_one') === 'one';

			$r->config('config_one', 'two');
			$test[] = $r->config('config_one') === 'two';

			return $test;
		});

		$tester->test(null,function($tester){
			$test = array();

			$r = new Roulette();
			$r->setConfig('foo', 'foo');
			$test[] = is_string($r->getConfig('foo'));
			$test[] = $r->getConfig('foo') === 'foo';

			$r->setConfig('_foo', 'foo');
			$test[] = is_null($r->getConfig('_foo'));

			
			$r->setConfig('foo', 'foo');
			$r->setConfig('bar', 'bar');
	     	$configs = $r->getConfig(array(
	            'foo', 'bar'
	        ));
	        $test[] = $configs['foo'] === 'foo';
	        $test[] = $configs['bar'] === 'bar';

			return $test;
		});
		$tester->test(null,function($tester){
			$test = array();

			$r = new Roulette();
			$r->setConfig('foo', 'foo');
			$test[] = $r->getConfig('foo') === 'foo';

			$r->setConfig('_foo', 'foo');
			$test[] = is_null($r->getConfig('_foo'));

			$r->setConfig(array(
				'foo'=>'foo',
				'bar'=>'bar',
			));
			$test[] = $r->getConfig('foo') === 'foo';
			$test[] = $r->getConfig('bar') === 'bar';

			return $test;
		});
		$tester->test(null,function($tester){});
	}

	function test_config() {
		
	}

	function test_getConfig() {
		
	}

	function test_setConfig() {
		
	}

	function test_getConnection() {
		$test = array();

		$r = new Roulette();
		$test[] = is_null($r->getConnection()) === true;

		$r->setConnection((object)array());
		$test[] = is_null($r->getConnection()) === false;

		return $test;
	}

	function test_setConnection() {
		$test = array();

		$r = new Roulette();
		$test[] = is_null($r->getConnection()) === true;

		$r->setConnection((object)array());
		$test[] = is_null($r->getConnection()) === false;

		$r->setConnection();
		$test[] = is_null($r->getConnection()) === true;

		return $test;
	}

	function test_getTable() {
		$test = array();
		$r = new Roulette();

		$test[] = is_null($r->getTable()) === true;

		$r->setTable('foo');
		$test[] = $r->getTable() === 'foo';

		$r->setTable();
		$test[] = $r->getTable() === 'roulette'; // fallback into the class name itself

		return $test;
	}

	function test_setTable() {
		$test = array();
		$r = new Roulette();

		$test[] = is_null($r->getTable()) === true;

		$r->setTable('foo');
		$test[] = is_string($r->getTable());
		$test[] = $r->getTable() === 'foo';

		$r->setTable(112);
		$test[] = is_string($r->getTable());

		$r->setTable((object) array());
		$test[] = is_string($r->getTable());

		return $test;
	}

	function test_getPrimary() {
		$test = array();
		$r = new Roulette();

		$test[] = is_null($r->getPrimary()) === true;

		$r->setPrimary('foo');
		$test[] = $r->getPrimary() !== 'foo'; // field foo is not defined, so it must be invalid

		$r->addField('foo');
		$r->setPrimary('foo');
		$test[] = $r->getPrimary() === 'foo'; // now its exists

		return $test;
	}

	function test_setPrimary() {
		$test = array();
		$r = new Roulette();

		$test[] = is_null($r->getPrimary()) === true;

		$r->setPrimary('foo');
		$test[] = $r->getPrimary() !== 'foo'; // field foo is not defined, so it must be invalid

		$r->addField('foo');
		$r->setPrimary('foo');
		$test[] = $r->getPrimary() === 'foo'; // now its exists

		return $test;
	}

	function test_hasPrimary() {
		$test = array();
		$r = new Roulette(array(
			'fields' => array('id','name'),
		));

		$test[] = $r->hasPrimary() === false;

		$r->setPrimary('');
		$test[] = $r->hasPrimary() === false;

		$r->setPrimary(1);
		$test[] = $r->hasPrimary() === false;

		$r->setPrimary(0);
		$test[] = $r->hasPrimary() === false;

		$r->setPrimary(null);
		$test[] = $r->hasPrimary() === false;

		$r->setPrimary(true);
		$test[] = $r->hasPrimary() === false;

		$r->setPrimary(false);
		$test[] = $r->hasPrimary() === false;

		$r->setPrimary(array());
		$test[] = $r->hasPrimary() === false;

		$r->setPrimary((object) array());
		$test[] = $r->hasPrimary() === false;

		$r->setPrimary('id');
		$test[] = $r->hasPrimary() === true;

		$r->setPrimary(null);
		$test[] = $r->hasPrimary() === false;

		return $test;
	}

	function test_getFields() {
		$test = array();
		$r = new Roulette();

		$test[] = is_array($r->getFields()) === true;

		$f = $r->getFields();
		$test[] = empty($f) === true;
		
		$r->addField('id');
		$f = $r->getFields();
		$test[] = empty($f) === false;

		return $test;
	}

	function test_getField() {
		$test = array();
		$r = new Roulette(array(
			'fields' => array('id'),
		));

		$test[] = is_null($r->getField()) === true;

		$f = $r->addField('foo');
		$test[] = is_array($f);
		$test[] = count($f) === 1;

		$test[] = is_array($r->getField('id')) === false;

		$test[] = is_object($r->getField('id')) === true;

		return $test;
	}

	function test_addField() {
		$test = array();
		$r = new Roulette();

		$test[] = is_string($r->addField('foo')) === false;

		$test[] = is_array($r->addField(array('foo'))) === true;

		$test[] = is_object($r->addField(array('foo'))) === false;

		$test[] = is_null($r->addField()) === false;

		$test[] = is_array($r->addField((object) array('foo'))) === true;

		$test[] = is_array($r->addField('age', array('foo'))) === true;

		return $test;
	}

	function test_removeField() {
		$test = array();
		$r = new Roulette(array(
			'table' => 'foo',
			'fields' => array(
				'id', 'name', 'age', 'phone', 'email',
			),
		));

		$test[] = is_null($r->removeField()) === false;

		$test[] = is_string($r->removeField('foo')) === false;

		$test[] = is_string($r->removeField()) === false;

		$test[] = is_array($r->removeField('name')) === true;

		$test[] = is_object($r->removeField(true)) === false;

		$test[] = is_array($r->removeField('name', array('age'))) === true;

		return $test;
	}

	function test_hasField() {
		$test = array();
		$r = new Roulette(array(
			'fields' => array('id', 'name', 'age'),
		));

		$test[] = $r->hasField($r->getField('id')) === true;

		$test[] = $r->hasField('name') === true;

		$test[] = $r->hasField('hello') === false;

		$test[] = $r->hasField() === false;

		return $test;
	}

	function test_getFieldsBy() {
		$test = array();

		$r = new Roulette(array(
	     	'fields'=>array(
				array( 'name'=>'id', 'unique'=>true ),
				'name',
				'gender',
				array( 'name'=>'age', 'private'=>true ),
				'phone',
				array( 'name'=>'email', 'unique'=>true),
				'homepage'
			)
		));

		$fields = $r->getFieldsBy('name', 'id');
 		$test[] = is_array($fields);
 		$test[] = $fields[0]->name === 'id';

 		$fields = $r->getFieldsBy('unique', true);
		$test[] = count($fields) === 2;
		$test[] = $fields[0]->name === 'id';
		$test[] = $fields[1]->name === 'email';

		// $tester->subtest('testing', function($sub){
		// 	$subtest->test('blah', true);
		// 	$subtest->test('as', 12);
		// });

 		// test first match
		$field = $r->getFieldsBy('unique', true, true);
		return $test;
	}

	function test_getFieldsAttribute() {
		$test = array();
		return $test;
	}

	function test_getAssociations() {
		$test = array();
		$r = new Roulette();

		$test[] = is_null($r->getAssociations()) === false;

		$test[] = is_array($r->getAssociations('foo')) === true;

		$test[] = is_array($r->getAssociations('name', array('foo'))) === true;

		$test[] = is_string($r->getAssociations('foo')) === false;

		$test[] = is_object($r->getAssociations(true)) === false;

		return $test;
	}

	function test_addAssociation() {
		$test = array();
		$r = new Roulette();

		$test[] = is_array($r->addAssociation('foo')) === true;

		$test[] = is_null($r->addAssociation()) === false;

		$test[] = is_string($r->addAssociation('foo')) === false;

		return $test;
	}

	function test_getAssociation() {
		$test = array();
		$r = new Roulette();

		$test[] = is_null($r->addAssociation()) === false;

		$test[] = is_string($r->addAssociation('foo')) === false;

		$test[] = is_object($r->addAssociation()) === false;

		$test[] = is_integer($r->addAssociation(1)) === false;

		return $test;
	}

	function test_removeAssociation() {
		$test = array();
		return $test;
	}

	function test_hasAssociation() {
		$test = array();
		$r = new Roulette();

		$test[] = is_null($r->hasAssociation()) === false;

		$test[] = is_array($r->hasAssociation('foo')) === false;

		$test[] = is_array($r->hasAssociation(array('foo'))) === false;

		$test[] = is_string($r->hasAssociation('foo')) === false;

		// $test[] = $r->hasAssociation('assocManager') === true;

		// $test[] = $r->hasAssociation($r->getAssociation('assocManager')) === true;

		$test[] = false;

		return $test;
	}

	function test_associate() {
		$test = array();
		return $test;
	}

	function test_setId() {
		$test = array();
		$r = new Roulette(array(
			'primary' => 'id',
			'fields' => array('id', 'name', 'age'),
		));

		$r->setId(123);
		$test[] = $r->set($r->getPrimary(), 123);
		$test[] = $r->getId();

		return $test;
	}

	function test_getId() {
		$test = array();
		$r = new Roulette(array(
			'primary' => 'id',
			'fields' => array('id', 'name', 'age'),
		));

		// $test[] = is_array($r->getId()) === false;

		// $test[] = is_bool($r->getId(true)) === false;

		// $test[] = is_string($r->getId('foo')) === false;

		$r->setId(0);
		$test[] = $r->getId() === 0;

		$test[] = is_numeric($r->getId()) === true;

		return $test;
	}

	function test_hasId() {
		$test = array();
		$r = new Roulette(array(
			'primary' => 'id',
			'fields' => array('id', 'name', 'age'),
		));

		$r->setId(null);
		$test[] = $r->hasId() === false;

		$r->setId('');
		$test[] = $r->hasId() === false;

		$r->setId(0);
		$test[] = $r->hasId() === true;

		$r->setId(true);
		$test[] = $r->hasId() === false;

		$r->setId(array('1'));
		$test[] = $r->hasId() === false;

		$test[] = false;

		return $test;
	}

	function test_isModified() {
		$test = array();
		return $test;
	}

	function test_isRecord() {
		$test = array();
		$r = new Roulette(array(
			'primary' => 'id',
			'fields' => array('id', 'name', 'age'),
		));

		$test[] = $r->isRecord() === false;

		//$r->load(123); // ----> inFatal error: Call to undefined method Roulette::mapFieldIn() in E:\Workspace\Web\VHost\product\roulette\.lab\application\libraries\Roulette\Roulette.php on line 2234
		$test[] = $r->isRecord() === true;

		$test[] = false;

		return $test;
	}

	function test_setIsRecord() {
		$test = array();
		return $test;
	}

	function test_getError() {
		$test = array();
		return $test;
	}

	function test_isObservable() {
		$test = array();
		return $test;
	}

	function test_setObservable() {
		$test = array();
		return $test;
	}

	function test_set() {
		$test = array();
		$r = new Roulette();

		$test[] = is_array($r->set(array('id' => 123, 'name' => 'foo'))) === true;

		$test[] = is_null($r->set()) === false;

		$test[] = is_string($r->set('foo')) === false;

		$test[] = is_integer($r->set(1)) === false;

		return $test;
	}

	function test_get() {
		$test = array();
		return $test;
	}

	function test_getData() {
		$test = array();
		return $test;
	}

	function test_getModified() {
		$test = array();
		return $test;
	}

	function test_save() {
		$test = array();
		return $test;
	}

	function test_destroy() {
		$test = array();
		return $test;
	}

	function test_getEvents() {
		$test = array();
		return $test;
	}

	function test_hasEvent() {
		$test = array();
		return $test;
	}

	function test_addEvent() {
		$test = array();
		return $test;
	}

	function test_enableEvent() {
		$test = array();
		return $test;
	}

	function test_disableEvent() {
		$test = array();
		return $test;
	}

	function test_eventEnabeld() {
		$test = array();
		return $test;
	}

	function test_eventDisabled() {
		$test = array();
		return $test;
	}

	function test_getListener() {
		$test = array();
		return $test;
	}

	function test_hasListener() {
		$test = array();
		return $test;
	}

	function test_addListener() {
		$test = array();
		return $test;
	}

	function test_trigger() {
		$test = array();
		return $test;
	}

	function test_usingConverter() {
		$test = array();
		return $test;
	}

	function test_convert() {
		$test = array();
		return $test;
	}

	function test_renewId() {
		$test = array();
		return $test;
	}

	function test_generateId() {
		$test = array();
		return $test;
	}

	function test_load() {
		$test = array();
		return $test;
	}

	function test_create() {
		$test = array();
		return $test;
	}

	function test_remove() {
		$test = array();
		return $test;
	}

	function test_find() {
		$test = array();
		return $test;
	}

	function test_findBy() {
		$test = array();
		return $test;
	}

	function test_findOne() {
		$test = array();
		return $test;
	}

	function test_findAll() {
		$test = array();
		return $test;
	}

	function test_exists() {
		$test = array();
		return $test;
	}

	function test_getFieldBy(){
		$test = array();
		return $test;
	}

	function test_eventEnabled(){
		$test = array();
		return $test;
	}

	function test_getListeners(){
		$test = array();
		return $test;
	}

	function test_hasListeners(){
		$test = array();
		return $test;
	}

	function test_fireEvent(){
		$test = array();
		return $test;
	}

	function test_revert(){
		$test = array();
		return $test;
	}

	function test_createInstance(){
		$test = array();
		return $test;
	}

	function test_getInstance(){
		$test = array();
		return $test;
	}

	function test_createRecord(){
		$test = array();
		return $test;
	}

	function test_loadRecord(){
		$test = array();
		return $test;
	}

	function test_removeRecord(){
		$test = array();
		return $test;
	}

	function test_findRecords(){
		$test = array();
		return $test;
	}

	function test_findOneRecord(){
		$test = array();
		return $test;
	}

	function test_findAllRecords(){
		$test = array();
		return $test;
	}

	function test_existsRecords(){
		$test = array();
		return $test;
	}

}