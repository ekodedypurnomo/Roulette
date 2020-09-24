<?php
use Roulette\Model;

class RouletteTest_Model extends RouletteUnittest_Model {

    public $name = 'Roulette\Model';

    protected $skip = array('is','isNot');
    
    public function __construct() {
        parent::__construct();
    }

    public function index()
    {
        require_once(__DIR__."/uses/student.php");
        $studentC = 'Student';

        require_once(__DIR__."/uses/faculty.php");
        $facultyC = 'Faculty';

        require_once(__DIR__."/uses/hobby.php");
        $hobbyC = 'Hobby';

        $me = $this;
        $class = $this->name;



        // \Roulette\Query\Operation::enableLog();
        // $user = $studentC::load('doe');
        // $proto = $user::prototype();
        // $record = $hobbyC::create();
        // $record->set('student',$user->getId());

        // $pass = $user->can('read', $record);

        // $qop = $hobbyC::query('select');
        // $pass = $user->can('list', $hobbyC);

        // dd($user, $pass);

        // $queryOption = $hobbyC::query()
        //     ->where('name','like','a')
        //     ->where('name','like','a')
        //     ->get();

        // $q = Illuminate\Support\Facades\DB::table('users')
        //     ->where('name', '=', 'John')
        //     ->orWhere(function ($query) {
        //         $query->where('votes', '>', 100)
        //               ->where('title', '<>', 'Admin');
        //     });
        //     ->get();
        // dd($user, \Roulette\Query\Operation::getLog());


        // $condition = new Roulette\Query\Condition(array(
        //     'hook'=>'or',//done
        //     'field'=>'name',
        //     'operator'=>'IS NOT',
        //     'value'=> null
        //     ));
        // dd($condition);

        // $qop
        // ->table($table)
        // ->orWhere('name','=','me')
        // // ->orWhere('class','=','rpl')
        // ->orWhere(function($query)
        // {
        //     $query
        //     ->where('sex','is not','null')
        //     ->orWhere('sex','IS NULL')
        //     ->orWhere(function($q)
        //     {
        //         $q
        //         ->where('faculty','is',true)
        //         ->andWhere('class','is',false)
        //         ->orWhereBetween('class',[10,50])
        //         ->whereIn('class',[10,50,100])
        //         ;
        //     })
        //     ;
        // });


        /* model */
        $me->test('getPrimary', method_exists($class, 'getPrimary'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
        {
            $me->test('setPrimary', method_exists($class, 'setPrimary'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
            {
                $me->test('setgetPrimary, default', $studentC::getPrimary() === 'id' );
            
                $studentC::setPrimary('name');
                $me->test('setgetPrimary, notnull', $studentC::getPrimary() === 'name' );

                $studentC::setPrimary();
                $me->test('setgetPrimary, null', $studentC::getPrimary() === null );
                
                // revert changes
                $studentC::setPrimary('id');
            });
        });

        $me->test('getFields', method_exists($class, 'getFields'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
        {
            $fields = $studentC::getFields();
            $me->test('getFields, instance', is_a($fields, '\Roulette\Collection') );
            $me->test('getFields, check field', is_a($fields->get($studentC::getPrimary()), '\Roulette\Model\Field\Field') == true );
        });

        $me->test('getPrototype', method_exists($class, 'getPrototype'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
        {
            $me->test('prototype', method_exists($class, 'prototype'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
            {
                $me->test('init', method_exists($class, 'init'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
                {
                    $prototype = $studentC::getPrototype();
                    $me->test('getPrototype, instance', is_a($prototype, '\Roulette\Model\Prototype') );
                });
            });
        });

        $me->test('getField', method_exists($class, 'getField'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
        {
            $f = $studentC::getField($studentC::getPrimary());
            $me->test('getField, instance', is_a($f, '\Roulette\Model\Field\Field') == true );
            $me->test('getField, validate field', !is_null($f) && $f->getName() == $studentC::getPrimary() );
        });

        $me->test('setField', method_exists($class, 'setField'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
        {
            $me->test('removeField', method_exists($class, 'removeField'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
            {
                $f = new \Roulette\Model\Field\Field(array('name'=>'field'));
            
                $studentC::setField($f->getName(), $f);
                $me->test('setField, instance, after set', $studentC::getField($f->getName()) == $f );
                $studentC::removeField($f->getName());
                $me->test('setField, instance, after remove', is_null($studentC::getField($f->getName())) == true);
                
                $studentC::setField('f', array('name'=>'f'));
                $me->test('setField, array, after set', is_object($studentC::getField('f')) and is_a($studentC::getField('f'), '\Roulette\Model\Field\Field') );
                $studentC::removeField('f');
                $me->test('setField, array, after remove', is_null($studentC::getField('f')) == true);
            });
        });

        $me->test('addField', method_exists($class, 'addField'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
        {
            $f1 = new \Roulette\Model\Field\Field(array('name'=>'field1'));
            $f2 = new \Roulette\Model\Field\Field(array('name'=>'field2'));
            $oldCount = $studentC::getFields()->getCount();

            $studentC::addField($f1);
            $me->test('addField, after add as object', $studentC::getField($f1->getName()) == $f1 );
            $studentC::addField($f1, $f2);
            $me->test('addField, after add as array arguments', $studentC::getField($f2->getName()) == $f2 );

            $me->test('addField, validate count', $studentC::getFields()->getCount() == $oldCount + 2 );
           
            // revert changes 
            $studentC::removeField($f1);
            $studentC::removeField($f2);
        });

        $me->test('getAssociation', method_exists($class, 'getAssociation'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
        {
            $assoc = $studentC::getAssociation('faculty');
            $me->test('getAssociation, instance', is_a($assoc, '\Roulette\Association\HasOne') );
        });

        $me->test('getAssociations', method_exists($class, 'getAssociations'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
        {
            $assocs = $studentC::getAssociations();
            $me->test('getAssociations, instance', is_a($assocs, '\Roulette\Collection'), function() use($me, $assocs){
                $me->test('getAssociations, instance, count', $assocs->getCount() == 3 );
            });
            $me->test('getAssociations, hasOne', is_a($assocs->get('faculty'), '\Roulette\Association\HasOne') );
            $me->test('getAssociations, hasMany', is_a($assocs->get('hobbies'), '\Roulette\Association\HasMany') );
        });
        
        /* record */
        $me->test('create', method_exists($class, 'create'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
        {
            $obj = $studentC::create();
            $me->test('create, (autoId) without params, instance', $studentC::is($obj) );
            $me->test('create, (autoId) without params, value', $obj->hasId() === true ); // autoId is enabled
            
            $obj = $studentC::create(array('id'=>'id'));
            $me->test('create, (autoId) with params, instance', $studentC::is($obj) );
            $me->test('create, (autoId) with params, value', $obj->hasId() );

            $obj = $hobbyC::create();
            $me->test('create, without params, value', $obj->hasId() === false );
            
            $obj = $hobbyC::create(array('id'=>'id'));
            $me->test('create, with params, value', $obj->hasId());
        });

        $me->test('get', method_exists($class, 'get'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
        {
            $me->test('set', method_exists($class, 'set'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
            {
                $obj = $studentC::create(array());

                $me->test('setget, null', $obj->get('address') === null );
                
                $obj->set('age', '17');
                $me->test('setget, valued', $obj->get('age') == '17 years old' ); // converted

                $obj->set(array(
                    'name'=>'john'
                ));
                $me->test('setget, array of set', $obj->get('name') == 'john' );

                $me->test('setget, array of get', $obj->get(array('id','name')) == array('id'=>null,'name'=>'john') );
            });
        });
        
        $me->test('isModified', method_exists($class, 'isModified'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
        {
            $me->test('getModified', method_exists($class, 'getModified'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
            {
                $me->test('commit', method_exists($class, 'commit'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
                {
                    $obj = $studentC::create()->commit(); // need to commit for remove convert affect
            
                    $mod = $obj->getModified();
                    $me->test('getModified, empty', empty($mod) );
                    $me->test('getModified, empty, isModified', $obj->isModified() === false );

                    $obj->set('address', 'here');
                    $mod = $obj->getModified();
                    $me->test('getModified, not empty', empty($mod) !== true );
                    $me->test('getModified, not empty, isModified', $obj->isModified() );

                    $obj->commit();
                    $mod = $obj->getModified();
                    $me->test('getModified, after commit', empty($mod) );
                    $me->test('getModified, after commit, isModified', $obj->isModified() === false );
                }); 
            });
        });

        $me->test('revert', method_exists($class, 'revert'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
        {
            $obj = $studentC::create(array('address'=>'someplace'));

            $me->test('revert, beforerevert', $obj->get('address') == 'someplace' );
            $obj->revert();
            $me->test('revert, afterrevert', $obj->get('address') == null );

            $obj->set('address','someplace');
            $obj->commit();
            $me->test('revert, withcommit beforerevert', $obj->get('address') == 'someplace' );
            $obj->revert();
            $me->test('revert, withcommit afterrevert', $obj->get('address') == 'someplace' );

        });

        $me->test('getData', method_exists($class, 'getData'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
        {
            $obj = $studentC::create(array());
            $fieldsCount = $studentC::getFields()->getCount();
            $data = $obj->getData();
            
            $me->test('getData, validate count', $fieldsCount >= count($data) ); // password is hidden form getData
        });

        $me->test('getValue', method_exists($class, 'getValue'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
        {
            $obj = $studentC::create();

            $value = $obj->getValue('id');
            $me->test('getValue, after set', is_a($value, '\Roulette\Data\Value') );
        });

        $me->test('getId', method_exists($class, 'getId'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
        {
            $me->test('setId', method_exists($class, 'setId'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
            {
                $obj = $hobbyC::create();
                $me->test('setgetId, null', $obj->getId() === null ); // autoId is disabled

                $obj = $studentC::create();
                $me->test('setgetId, (autoId) null', $obj->getId() !== null ); // autoId is enabled

                $obj->setId('id');
                $me->test('setgetId, after set', $obj->getId() == 'id' );

                $obj->setId();
                $me->test('setgetId, after set null', $obj->getId() == null );
            });

            $me->test('renewId', method_exists($class, 'renewId'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
            {
                $obj = $hobbyC::create();
                $me->test('setgetId, before', $obj->getId() === null ); // autoId is disabled
                $me->test('setgetId, after', $obj->renewId()->getId() !== null ); // autoId is disabled

                $obj = $studentC::create();
                $objId1 = $obj->getId();
                $objId2 = $obj->renewId()->getId();
                $me->test('setgetId, (autoId)', $objId1 !== $objId2 ); // autoId is enabled
            }); 
        });

        $me->test('hasId', method_exists($class, 'hasId'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
        {
            $obj = $studentC::create();
            $me->test('hasId, (autoId) has', $obj->hasId() == true ); // autoId is enabled

            $obj->setId(12);
            $me->test('hasId, (autoId) hasnot', $obj->hasId() );

            $obj = $hobbyC::create();
            $me->test('hasId, has', $obj->hasId() == false ); // autoId is disabled

            $obj->setId(12);
            $me->test('hasId, hasnot', $obj->hasId() );
        });
        
        $me->test('load', method_exists($class, 'load'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
        {
            $obj = $studentC::load('johndoe');
            $me->test('load, instance', is_a($obj, $studentC) );
            $me->test('load, alive', $obj->isAlive() );
        });

        $me->test('reload', method_exists($class, 'reload'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
        {
            $obj = $studentC::load('johndoe');

            $var_beforeset = $obj->get('address');

            $obj->set('address',rand());
            $var_afterset = $obj->get('address');

            $me->test('reload, norevert, before reload', $var_beforeset !== $var_afterset );

            $obj->reload(false);            
            $var_afterreload = $obj->get('address');

            $me->test('reload, norevert, after reload', $var_afterset === $var_afterreload );

            $obj->reload();
            $var_afterreload_revert = $obj->get('address');
            
            $me->test('reload, revert, after reload', $var_beforeset === $var_afterreload_revert ); // no changes allowed

            $obj = $studentC::create(array());
            $obj->reload();

            $me->test('reload, blank object', $obj->isAlive() === false );

        });

        $me->test('isValid', method_exists($class, 'isValid'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
        {
            $me->test('validate', method_exists($class, 'validate'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
            {
                $obj = $studentC::create();
                $errors = $obj->getErrorMessages();
                $me->test('validate, beforevalidate', $obj->isValid() === true ); // by default is valid
                $me->test('validate, beforevalidate message', is_array($errors) and empty($errors) );
                
                $obj->validate();
                $errors = $obj->getErrorMessages();
                $me->test('validate, invalid, aftervalidate', $obj->isValid() === false ); // id should not null
                $me->test('validate, invalid, aftervalidate message', is_array($errors) and !empty($errors) );

                $obj->renewId();
                $obj->set('name', 'tempvalidate');
                $obj->validate();
                $errors = $obj->getErrorMessages();
                
                $me->test('validate, valid, aftervalidate', $obj->isValid() ); // id should not null
                $me->test('validate, valid, aftervalidate message', is_array($errors) and empty($errors) );
            });
        });

        $me->test('isAlive', method_exists($class, 'isAlive'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
        {
            $obj = $studentC::create(array('id'=>'johndoe'));

            $me->test('isAlive, johndoe, without recheck', $obj->isAlive() == false ); # record by create instead of load
            $me->test('isAlive, johndoe, within recheck', $obj->isAlive(true) ); # assume if johndoe is exist in database

            # prefent data corrupt as long in a test mode
            $obj->revert();

            $obj = $studentC::create(array('id'=>'ghost'));
            
            $me->test('isAlive, ghost, within recheck', $obj->isAlive(true) == false ); // ghost should not exist on database
        });

        $me->test('save', method_exists($class, 'save'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
        {
            // preparation for the object
            // clean current saved record
            $studentC::load('tempsave', function($record){
                $record->destroy();
            });

            // begin tests
            // save with invalid data, and should be still unalive 
            $obj = $studentC::create();

            $success = $obj->save();

            $me->test('save, invalid, failure', $success === false );
            $me->test('save, invalid, failure message', array_key_exists('id', $obj->getErrorMessages(true)) );
            $me->test('save, invalid, alive', $obj->isAlive() === false );
            $me->test('save, invalid, modified', $obj->isModified() ); # by default modified is true
            
            // no append valid data
            $obj->set(array(
                'id'=>'tempsave',
                'name'=>'John Doe'
            ));

            $success = $obj->save();
            
            $me->test('save, insert, success', $success );
            $me->test('save, insert, success data', $obj->get('name') == 'john doe' ); # student use converter
            $me->test('save, insert, alive', $obj->isAlive() == true ); # now should be alive
            $me->test('save, insert, modified', $obj->isModified() == false ); # should not modified
            
            $obj->set(array(
                'id'=>'tempsave',
                'name'=>'tempsave'
            ));

            $success = $obj->save();
            
            $me->test('save, update, success', $success );
            $me->test('save, update, success data', $obj->get('name') == 'tempsave' );
            $me->test('save, update, alive', $obj->isAlive() == true ); # now should be alive
            $me->test('save, update, modified', $obj->isModified() == false ); # should not modified
        });

        $me->test('destroy', method_exists($class, 'destroy'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
        {
            $obj = $studentC::load('tempdelete');
            if(! $obj){
                $obj = $studentC::create(array(
                    'id'=>'tempdelete', 'name'=>'tempdelete'
                ));
                \Roulette\Query\Operation::enableLog();
                $obj->save();
            }
            
            $a = $obj->destroy();
            $me->test('destroy, success', $a );
        });

        $me->test('find', method_exists($class, 'find'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
        {
            $obj = $hobbyC::find();

            $me->test('find, instance', is_a($obj, \Roulette\Model\Store::class), function() use($me, $class, $studentC, $facultyC, $hobbyC, $obj)
            {
                $me->test('find, count', $obj->getCount() > 0);
            });
        });

        $me->test('associate', method_exists($class, 'associate'), function() use($me, $class, $studentC, $facultyC, $hobbyC)
        {
            $obj = $studentC::load('testassoc');
        
            // hasOne
            $assoc = $obj->associate('faculty');
            $me->test('associate, hasOne, instance', is_a($assoc, '\Roulette\Association\Relation'));
            $me->test('associate, hasOne, resource', is_a($assoc->getResource(), '\Faculty'), function() use($me, $assoc)
            {
                $me->test('associate, hasOne, resource value', !empty($assoc->getResource()->getId()) );       
            });
            
            // hasMany
            $assoc = $obj->associate('hobbies');            
            $me->test('associate, hasMany, instance', is_a($assoc, '\Roulette\Association\Relation') );
            $me->test('associate, hasMany, resource', is_a($assoc->getResource(), '\Roulette\Model\Store'), function() use($me, $assoc)
            {
                $me->test('associate, hasMany, resource value', !empty($assoc->getResource()->getFirst()->getId()) );       
            });

            // fake
            $assoc = $obj->associate('fake');
            $me->test('associate, invalid, instance', is_null($assoc) );

            $me->test('lookup', method_exists($class, 'lookup'), function() use($me, $class, $studentC, $facultyC, $hobbyC, $obj)
            {
                // alias
                $assoc = $obj->associate('faculty')->getResource();
                $lookup = $obj->lookup('faculty');
                $me->test('associate, lookup class', get_class($assoc) == get_class($lookup) );
                $me->test('associate, lookup instance', $assoc === $lookup ); # model are use cache by default
            });            
        });

        $this->createUnfinishedTask();
    }
}