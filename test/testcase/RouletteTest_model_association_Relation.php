<?php
use Roulette\Model;

class RouletteTest_model_association_Relation extends RouletteUnittest_Model {

    public $name = 'Roulette\Model\Association\Relation';

    protected $skip = array('is','isNot');

    public function __construct(){
        parent::__construct();
    }

    function index()
    {
        require_once(__DIR__."/uses/student.php");
        $studentC = 'Student';

        require_once(__DIR__."/uses/faculty.php");
        $facultyC = 'Faculty';

        require_once(__DIR__."/uses/hobby.php");
        $hobbyC = 'Hobby';

        $me = $this;
        $class = $this->name;
        $AssocC = "Roulette\Model\Association\HasOne";
        $ModdelC = "Roulette\Model";

        $me->test('property', true, function() use($me, $class){
            $me->test('property, association', property_exists($class, 'association'));
            $me->test('property, resource', property_exists($class, 'resource'));
            $me->test('property, associated', property_exists($class, 'associated'));
        });

        $me->test('getAssociation', method_exists($class, 'getAssociation'), function() use($me, $class, $AssocC, $ModdelC){
            $assoc = new $AssocC;
            $value = new $class($assoc, new $ModdelC);
            $me->test('getAssociation, after set', $value->getAssociation() === $assoc);
        });

        $me->test('getRecord', method_exists($class, 'getRecord'), function() use($me, $class, $AssocC, $ModdelC){
            $model = new $ModdelC;
            $value = new $class(new $AssocC, $model);
            $me->test('getRecord, after set', $value->getRecord() === $model);
        });

        $me->test('isAssociated', method_exists($class, 'isAssociated'), function() use($me, $class, $studentC){
            $var = $studentC::load('johndoe');
            $assoc = $var->associate('faculty');

            $me->test('isAssociated, johndoe has association', $assoc->isAssociated(true) === true);
        });

        $me->test('getResource', method_exists($class, 'getResource'), function() use($me, $class, $AssocC, $ModdelC, $studentC, $hobbyC){
            $var = $studentC::load('johndoe');
            
            $assoc = $var->associate('faculty');
            $me->test('getResource, hasOne', is_a($assoc->getResource(), '\Faculty') );

            $assoc = $var->associate('hobbies');
            $me->test('getResource, hasMany', is_a($assoc->getResource(), '\Roulette\Model\Store'), function() use($me, $assoc)
            {
                $me->test('getResource, hasMany, unempty', !!$assoc->getResource()->getCount());
            });
        });

        $this->createUnfinishedTask();
    }
}