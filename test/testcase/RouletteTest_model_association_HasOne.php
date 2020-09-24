<?php
use Roulette\Model;

class RouletteTest_model_association_HasONe extends RouletteUnittest_Model {

    public $name = 'Roulette\Model\Association\HasOne';

    protected $skip = array('is','isNot');

    public function __construct(){
        parent::__construct();
    }

    function index(){
        require_once(__DIR__."/uses/student.php");
        $studentC = 'Student';

        require_once(__DIR__."/uses/faculty.php");
        $facultyC = 'Faculty';

        require_once(__DIR__."/uses/hobby.php");
        $hobbyC = 'Hobby';

        $me = $this;
        $class = $this->name;

        $obj = new $class;

        $me->test('property', true, function() use($me, $class){
            $me->test('property, name', property_exists($class, 'name'));
            $me->test('property, type', property_exists($class, 'type'));
            $me->test('property, field', property_exists($class, 'field'));
            $me->test('property, model', property_exists($class, 'model'));
        });

        $me->test('getName', method_exists($class, 'getName'), function() use($me, $class){
            $obj = new $class;
            $test = is_null($obj->getName()) === true;
            $me->test('getName, is null', $test);

            $obj = new $class(array(
                'name'=>'assoc'
            ));
            $test = $obj->getName() === 'assoc';
            $me->test('getName, exist name value', $test);
        });

        $me->test('getType', method_exists($class, 'getType'), function() use($me, $class){
            $obj = new $class;
            $test = $obj->getType() === 'hasMany';
            $me->test('getType, hasMany', $test);

            $obj = new $class(array(
                'type' => 'hasOne'
            ));
            $test = $obj->getType() === 'hasOne';
            $me->test('getType, hasOne', $test);

            $obj = new $class(array(
                'type' => 'has'
            ));
            $test = $obj->getType() === 'hasMany';
            $me->test('getType, has', $test);
        });

        $me->test('getModel', method_exists($class, 'getModel'), function() use($me, $class, $studentC, $facultyC, $hobbyC){
            $obj = new $class;
            $test = is_null($obj->getModel()) === true;
            $me->test('getModel, is null Model', $test);

            $obj = new $class(array(
                'model' => 'Student'
            ));
            $test = $obj->getModel() === $studentC;
            $me->test('getModel, exist model', $test);
        });

        $me->test('getField', method_exists($class, 'getField'), function() use($me, $class){
            $obj = new $class;
            $test = is_null($obj->getField()) === true;
            $me->test('getField, is null Field', $test);

            $obj = new $class(array(
                'field' => 'Student'
            ));
            $test = $obj->getField() === 'Student';
            $me->test('getField, exist Model', $test);
        });

        $me->test('associate', method_exists($class, 'associate'), function() use($me, $class, $studentC, $facultyC, $hobbyC){
            $obj = $studentC::load('johndoe'); # use johndoe instead of call another person
        
            $assoc = $obj->associate('faculty');

            $me->test('associate, hasOne, instance', is_a($assoc, 'Roulette\Association\Relation'));
            $me->test('associate, hasOne, resource', is_a($assoc->getResource(), '\Faculty') );

            $assoc = $obj->associate('hobbies');
            $me->test('associate, hasMany, instance', is_a($assoc, '\Roulette\Association\Relation') );
            $me->test('associate, hasMany, resource', is_a($assoc->getResource(), '\Roulette\Model\Store') );
        });

        $this->createUnfinishedTask();
    } 
}