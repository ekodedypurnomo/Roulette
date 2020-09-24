<?php
use Roulette\Model\Field\Field;
use Roulette\Collection;

class RouletteTest_model_Fields extends RouletteUnittest_Model {

    public $name = 'Roulette\Model\Fields';

    protected $skip = array('is','isNot');
    
    public function __construct() {
        parent::__construct();
    }

    public function index()
    {
        require_once(__DIR__.'/uses/student.php');

        $this->skip = get_class_methods(Collection::class);

        $me = $this;
        $class = $this->name;
        // $obj = new $class($student_fields);
        $obj = Student::create()->getFields();

        # only check override functions

        $me->test('getAll', method_exists($class, 'getAll'), function() use($me, $class, $obj)
        {
            $fields = $obj->getAll();
            $me->test('getAll, instance', is_array($fields) );
            $me->test('getAll, check field', count($fields) == $obj->getCount() );
        });

        $me->test('get', method_exists($class, 'get'), function() use($me, $class, $obj)
        {
            $comparedFieldName = 'id';
            $field = $obj->get($comparedFieldName);
            // dd(is_a($field, '\Roulette\Model\Field'));
            // $me->test('get, instance', is_a($field, '\Roulette\Model\Field') == true );
            $me->test('get, validate field', (!is_null($field)) and $field->getName() == $comparedFieldName );
        });

        $me->test('set', method_exists($class, 'set') and method_exists($class, 'removeOn'), function() use($me, $class, $obj)
        {
            $f = new \Roulette\Model\Field\Field(array('name'=>'field'));
            
            $obj->set($f->getName(), $f);
            $me->test('set, instance, after set', $obj->get($f->getName()) == $f );
            $obj->removeOn($f->getName());
            $me->test('set, instance, after remove', is_null($obj->get($f->getName())) == true);
            
            $obj->set('f', array('name'=>'f'));
            $me->test('set, array, after set', is_object($obj->get('f')));
            $obj->removeOn('f');
            $me->test('set, array, after remove', is_null($obj->get('f')) == true);
        });

        $me->test('add', method_exists($class, 'add'), function() use($me, $class, $obj)
        {
            $f1 = new \Roulette\Model\Field\Field(array('name'=>'field1'));
            $f2 = new \Roulette\Model\Field\Field(array('name'=>'field2'));
            $oldCount = count($obj->getAll());

            $obj->add($f1);
            $me->test('add, after add as object', $obj->get($f1->getName()) == $f1 );
            $obj->add($f1, $f2);
            $me->test('add, after add as array arguments', $obj->get($f2->getName()) == $f2 );

            $me->test('add, validate count', count($obj->getAll()) == $oldCount + 2 );
           
            // revert changes
            $obj->remove($f1);
            $obj->remove($f2);
        });

        $me->test('getAttribute', method_exists($class, 'getAttribute'), function() use($me, $class, $obj)
        {
            $a = $obj->getAttribute('name');
            $me->test('getAttribute, return', is_array($a) );
            $me->test('getAttribute, count', count($a) == $obj->getCount() );
            $me->test('getAttribute, item', $a['gender'] == 'gender' );
        
            $b = $obj->getAttribute('source');
            $me->test('getAttribute, other item', $b['gender'] == 'sex' );
        });

        $me->test('getName', method_exists($class, 'getName'), function() use($me, $class, $obj)
        {
            $fieldsCount = count($obj->getAll());
            $fields = $obj->getName();
            $me->test('getName, return', is_array($fields) and ($fieldsCount == count($fields)) );
            $me->test('getName, items', is_string(reset($fields)) );
            
            $fieldsCompare = $obj->getAttribute('name');
            $me->test('getName, with other', $fields == $fieldsCompare );
        });

        $me->test('getSource', method_exists($class, 'getSource'), function() use($me, $class, $obj)
        {
            $fieldsCount = count($obj->getAll());
            $fields = $obj->getSource();
            $me->test('getSource, return', is_array($fields) and $fieldsCount == count($fields) );
            $me->test('getSource, items', is_string(reset($fields)) );

            $fieldsCompare = $obj->getAttribute('source');
            $me->test('getSource, with other', $fields == $fieldsCompare );
        });
        
        $me->test('getDisplay', method_exists($class, 'getDisplay'), function() use($me, $class, $obj)
        {
            $fieldsCount = count($obj->getAll());
            $fields = $obj->getDisplay();
            $me->test('getDisplay, return', is_array($fields) and $fieldsCount == count($fields) );
            $me->test('getDisplay, items', is_string(reset($fields)) );

            $fieldsCompare = $obj->getAttribute('display');
            $me->test('getDisplay, with other', $fields == $fieldsCompare );
        });

        $me->test('filterPrivate', method_exists($class, 'filterPrivate'), function() use($me, $class, $obj)
        {
            $fields = $obj->filterPrivate()->getAll();
            $field = reset($fields);
            $me->test('filterPrivate, return', is_array($fields) === true );
            $me->test('filterPrivate, items', in_array('password', $fields) === true );
        });

        $me->test('filterPublic', method_exists($class, 'filterPublic'), function() use($me, $class, $obj)
        {
            $fields = $obj->filterPublic()->getAll();
            $field = reset($fields);
            $me->test('filterPublic, return', is_array($fields) === true );
            $me->test('filterPublic, items', in_array('id', $fields) === true );
        });

        $me->test('filterSelectable', method_exists($class, 'filterSelectable'), function() use($me, $class, $obj)
        {
            $fields = $obj->filterSelectable()->getAll();
            $field = reset($fields);
            $me->test('filterSelectable, return', is_array($fields) === true );
            $me->test('filterSelectable, items', in_array('name', $fields) === true );
        });

        $me->test('filterInsertable', method_exists($class, 'filterInsertable'), function() use($me, $class, $obj)
        {
            $fields = $obj->filterInsertable()->getAll();
            $field = reset($fields);
            $me->test('filterInsertable, return', is_array($fields) === true );
            $me->test('filterInsertable, items', in_array('name', $fields) === true );
        });

        $me->test('filterUpdatable', method_exists($class, 'filterUpdatable'), function() use($me, $class, $obj)
        {
            $fields = $obj->filterUpdatable()->getAll();
            $field = reset($fields);
            $me->test('filterUpdatable, return', is_array($fields) === true );
            $me->test('filterUpdatable, items', in_array('name', $fields) === true );
        });

        $me->test('filterDeletable', method_exists($class, 'filterDeletable'), function() use($me, $class, $obj)
        {
            $fields = $obj->filterDeletable()->getAll();
            $field = reset($fields);
            $me->test('filterDeletable, return', is_array($fields) === true );
            $me->test('filterDeletable, items', in_array('name', $fields) === true );
        });

        $this->createUnfinishedTask();
    }
}