<?php 

class RouletteTest_model_field_Field extends RouletteUnittest_Model {

    public $name = 'Roulette\Model\Field\Field';
    protected $skip = array('is','isNot');

    public function __construct() {
        parent::__construct();
    }

    function index() {
        $me = $this;
        $class = $this->name;

        $me->test('property', true, function() use($me, $class)
        {
            $config = array(
                'name' => 'fieldName',
                'private' => true,
                'default' => true,
                'reader' => true,
                'writer' => true,
                'converter' => true,
                'renderer' => true,
                'insert' => true,
                'update' => true,
                'delete' => true,
                'select' => true,
                'readOnly' => true,
                'validation' => true,
            );

            $field = new $class($config);

            $me->test('property, name', property_exists($class, 'name'));
            $me->test('property, source', property_exists($class, 'source'));
            $me->test('property, display', property_exists($class, 'display'));
            $me->test('property, private', property_exists($class, 'private'));
            $me->test('property, default', property_exists($class, 'default'));
            $me->test('property, reader', property_exists($class, 'reader'));
            $me->test('property, writer', property_exists($class, 'writer'));
            $me->test('property, converter', property_exists($class, 'converter'));
            $me->test('property, renderer', property_exists($class, 'renderer'));
            $me->test('property, operation', property_exists($class, 'operation'));
            $me->test('property, readOnly', property_exists($class, 'readOnly'));
            $me->test('property, validation', property_exists($class, 'validation'));
        });

        $me->test('read', method_exists($class, 'read'), function() use($me, $class)
        {
            $var = (string) rand();
            $field = new $class(array('name'=>'field', 'reader'=>function($value){
                return 'read:'.$value;
            }));
            $me->test('read, callable', is_callable($field->getConfig('reader')) === true );
            $me->test('read, callable, after', $field->read($var) === 'read:'.$var );

            $field->setConfig('reader', null);
            $me->test('read, not callable', is_callable($field->getConfig('reader')) === false );
            $me->test('read, not callable, after', $field->read($var) == $var );
        });

        $me->test('write', method_exists($class, 'write'), function() use($me, $class)
        {
            $var = (string) rand();
            $field = new $class(array('name'=>'field', 'writer'=>function($value){
                return 'write:'.$value;
            }));
            $me->test('write, callable', is_callable($field->getConfig('writer')) === true );
            $me->test('write, callable, after', $field->write($var) === 'write:'.$var );

            $field->setConfig('writer', null);
            $me->test('write, not callable', is_callable($field->getConfig('writer')) === false );
            $me->test('write, not callable, after', $field->write($var) == $var );
        });

        $me->test('convert', method_exists($class, 'convert'), function() use($me, $class)
        {
            $var = (string) rand();
            $field = new $class(array('name'=>'field', 'converter'=>function($value){
                return 'convert:'.$value;
            }));
            $me->test('convert, callable', is_callable($field->getConfig('converter')) === true );
            $me->test('convert, callable, after', $field->convert($var) === 'convert:'.$var );

            $field->setConfig('converter', null);
            $me->test('convert, not callable', is_callable($field->getConfig('converter')) === false );
            $me->test('convert, not callable, after', $field->convert($var) == $var );
        });

        $me->test('render', method_exists($class, 'render'), function() use($me, $class)
        {
            $var = (string) rand();
            $field = new $class(array('name'=>'field', 'renderer'=>function($value){
                return 'render:'.$value;
            }));
            $me->test('render, callable', is_callable($field->getConfig('renderer')) === true );
            $me->test('render, callable, after', $field->render($var) === 'render:'.$var );

            $field->setConfig('renderer', null);
            $me->test('render, not callable', is_callable($field->getConfig('renderer')) === false );
            $me->test('render, not callable, after', $field->render($var) == $var );
        });

        $me->test('getValidation', method_exists($class, 'getValidation'), function() use($me, $class)
        {
            $field = new $class(array('name'=>'field', 'validation'=>array('nullable'=>true)));
            $me->test('getValidation, exist', Roulette\Validation::is($field->getValidation()) );
        });

        $me->test('validate', method_exists($class, 'validate'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'=>'field', 
                'validation'=>array('nullable'=>false)
            ));
            $me->test('validate, onvalidate', !empty($field->validate(null)) );
        });

        $me->test('getName', method_exists($class, 'getName'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'=>'field'
            ));
            $me->test('getName, default', !empty($field->getName()));
        });

        $me->test('setName', method_exists($class, 'setName'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'=>'field'
            ));
            $me->test('setName, default', $field->setName('rak'));
        });

        $me->test('getSource', method_exists($class, 'getSource'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'  =>'field',
                'source'=>'hobby'
            ));
            $me->test('getSource, default', !empty($field->getSource()));
        });

        $me->test('setSource', method_exists($class, 'setSource'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'  =>'field',
                'source'=>'hobby'
            ));
            $me->test('setSource, default', $field->setSource('source'));
        });

        $me->test('getDisplay', method_exists($class, 'getDisplay'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'  =>'field',
                'source'=>'hobby',
                'display'=>'hobby'
            ));
            $me->test('getDisplay, default', !empty($field->getDisplay()));
        });

        $me->test('setDisplay', method_exists($class, 'setDisplay'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'  =>'field',
                'source'=>'hobby',
                'display'=>'hobby'
            ));
            $me->test('setDisplay, default', $field->setDisplay('display'));
        });

        $me->test('getDefault', method_exists($class, 'getDefault'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'  =>'field',
                'source'=>'hobby',
                'display'=>'hobby',
                'default'=>'default-h'
            ));
            $me->test('getDefault, default', !empty($field->getDefault()));
        });

        $me->test('setDefault', method_exists($class, 'setDefault'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'  =>'field',
                'source'=>'hobby',
                'display'=>'hobby',
                'default'=>'default-h'
            ));
            $me->test('setDefault, default', $field->setDefault('default'));
        });

        $me->test('isReadOnly', method_exists($class, 'isReadOnly'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'  =>'field',
                'readOnly'=> false
            ));
            $me->test('isReadOnly, default', $field->isReadOnly() == false);
        });

        $me->test('setToReadOnly', method_exists($class, 'setToReadOnly'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'=>'field',
                'readOnly'=> false
            ));
            $me->test('setToReadOnly, default', $field->setToReadOnly(true) == true);
        });

        $me->test('isPrivate', method_exists($class, 'isPrivate'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'=>'field',
                'readOnly'=>false,
                'private'=>false
            ));
            $me->test('isPrivate, default', $field->isPrivate() == false);
        });

        $me->test('setToPrivate', method_exists($class, 'setToPrivate'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'=>'field',
                'readOnly'=> false
            ));
            $me->test('setToPrivate, default', $field->setToPrivate(true) == true);
        });

        $me->test('isPublic', method_exists($class, 'isPublic'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'=>'field',
                'private'=>false
            ));
            $me->test('isPublic, default', $field->isPublic() == true);
        });

        $me->test('setToPublic', method_exists($class, 'setToPublic'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'=>'field',
                'readOnly'=> false
            ));
            $me->test('setToPublic, default', $field->setToPublic(true) == true);
        });

        $me->test('getOperation', method_exists($class, 'getOperation'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'=>'field'
            ));
            $me->test('getOperation, default', $field->getOperation());
        });

        $me->test('setOperation', method_exists($class, 'setOperation'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'=>'field'
            ));
            $op = $field->setOperation('cr');
            $me->test('setOperation, string', $field->getOperation() == 'cr');
        });

        $me->test('isSelectable', method_exists($class, 'isSelectable'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'=>'field'
            ));
            $me->test('isSelectable, default', $field->isSelectable());
        });

        $me->test('setSelectable', method_exists($class, 'setSelectable'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'=>'field'
            ));
            $me->test('setSelectable, default', $field->setSelectable());
        });

        $me->test('isInsertable', method_exists($class, 'isInsertable'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'=>'field'
            ));
            $me->test('isInsertable, default', $field->isInsertable());
        });

        $me->test('setInsertable', method_exists($class, 'setInsertable'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'=>'field'
            ));
            $me->test('setInsertable, default', $field->setInsertable());
        });

        $me->test('isUpdatable', method_exists($class, 'isUpdatable'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'=>'field'
            ));
            $me->test('isUpdatable, default', $field->isUpdatable());
        });

        $me->test('setUpdatable', method_exists($class, 'setUpdatable'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'=>'field'
            ));
            $me->test('setUpdatable, default', $field->setUpdatable());
        });

        $me->test('isDeletable', method_exists($class, 'isDeletable'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'=>'field'
            ));
            $me->test('isDeletable, default', $field->isDeletable());
        });

        $me->test('setDeletable', method_exists($class, 'setDeletable'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'=>'field'
            ));
            $me->test('setDeletable, default', $field->setDeletable());
        });

        $me->test('isUseRenderer', method_exists($class, 'isUseRenderer'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'=>'field',
                'renderer'=> function(){
                    return 'gender';
                }
            ));
            $me->test('isUseRenderer, default', $field->isUseRenderer());
        });

        $me->test('isUseConverter', method_exists($class, 'isUseConverter'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'=>'field',
                'converter'=>true
            ));
            $me->test('isUseConverter, default', $field->isUseConverter());
        });

        $me->test('isUseReader', method_exists($class, 'isUseReader'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'=>'field',
                'reader'=> true
            ));
            $me->test('isUseReader, default', $field->isUseReader());
        });

        $me->test('isUseWriter', method_exists($class, 'isUseWriter'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'=>'field',
                'writer'=> true
            ));
            $me->test('isUseWriter, default', $field->isUseWriter());
        });

        $me->test('isUseValidation', method_exists($class, 'isUseValidation'), function() use($me, $class)
        {
            $field = new $class(array(
                'name'=>'field',
                'validation'=>array('nullable'=>true)
            ));
            $me->test('isUseValidation, default', $field->isUseValidation());
        });
        
        $this->createUnfinishedTask();
    }

}