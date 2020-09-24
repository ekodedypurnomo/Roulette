<?php

use Roulette\Data\Permission;

class RouletteTest_data_Permission extends RouletteUnittest_Model {

    public $name = 'Roulette\Data\Permission';

    protected $skip = array('is','isNot');

    public function __construct(){
        parent::__construct();
    }

    public function index()
    {
        $me = $this;
        $class = $this->name;

        $me->test('create', method_exists($class, 'create'), function() use($me, $class)
        {
            $obj = $class::create();
            $me->test('create, valid', $class::is($obj));

            $perm = array(
                "select" => true,
                "insert" => true,
                "update" => false,
                "delete" => false
            );
            $obj = $class::createFromHex('c'); // c=rc--
            $me->test('create, createFromHex, valid, value', $obj->getPermission() === $perm);
            $obj = $class::createFromHex(12); // 12=c=rc--
            $me->test('create, createFromHex, valid, numeric', $obj->getPermission() === $perm);
            $obj = $class::createFromHex('g'); // invalid
            $me->test('create, createFromHex, invalid, value', is_array($obj->getPermission()) and count($obj->getPermission() == 4));
            $obj = $class::createFromHex(); // invalid
            $me->test('create, createFromHex, default, value', is_array($obj->getPermission()) and count($obj->getPermission() == 4));


            $perm = array(
                "select" => true,
                "insert" => false,
                "update" => true,
                "delete" => false
            );
            $obj = $class::createFromDec('10'); // c=rcu-
            $me->test('create, createFromDec, valid, string', $obj->getPermission() === $perm);
            $obj = $class::createFromDec(10); // 14=c=rcu-
            $me->test('create, createFromDec, valid, numeric', $obj->getPermission() === $perm);
            $obj = $class::createFromDec('g'); // invalid
            $me->test('create, createFromDec, invalid, value', is_array($obj->getPermission()) and count($obj->getPermission() == 4));
            $obj = $class::createFromDec(); // invalid
            $me->test('create, createFromDec, default, value', is_array($obj->getPermission()) and count($obj->getPermission() == 4));

            $obj = $class::createFromBin('1010'); // c=rcu-
            $me->test('create, createFromBin, valid, string', $obj->getPermission() === $perm);
            $obj = $class::createFromBin(array(1,0,1,0)); // 12=c=rcu-
            $me->test('create, createFromBin, invalid, array', is_array($obj->getPermission()) and count($obj->getPermission() == 4));
            $obj = $class::createFromBin('g'); // invalid
            $me->test('create, createFromBin, invalid, value', is_array($obj->getPermission()) and count($obj->getPermission() == 4));
            $obj = $class::createFromBin(); // invalid
            $me->test('create, createFromBin, default, value', is_array($obj->getPermission()) and count($obj->getPermission() == 4));            
        });

        $me->test('getPermission', method_exists($class, 'getPermission'), function() use($me, $class)
        {
            $obj = $class::create('b');
            $me->test('getPermission, valid, value', is_array($obj->getPermission()) === true);
            $obj = $class::create(array(1,1,1,0));
            $me->test('getPermission, invalid, value array will be default value', is_array($obj->getPermission()));
            $obj = $class::create(1);
            $me->test('getPermission, invalid, value integer will be default value', is_array($obj->getPermission()));
            $obj = $class::create();
            $me->test('getPermission, valid, null will be default value', is_array($obj->getPermission()) === true); //default value
        });

        $me->test('setPermission', method_exists($class, 'setPermission'), function() use($me, $class)
        {
            $obj = $class::create();
            $obj->setPermission(array(false,true,true,false));
            $me->test('setPermission, valid array bolean', $obj->getPermission() == array(
                'select'=>false,
                'insert'=>true,
                'update'=>true,
                'delete'=>false
                ));
            
            $obj->setPermission(array('delete'=>0,'select'=>1,'insert'=>1,'update'=>1));
            $me->test('setPermission, valid array assoc', $obj->getPermission() == array(
                'select'=>true,
                'insert'=>true,
                'update'=>true,
                'delete'=>false
                ));

            $obj->setPermission(array(1,1,1,0));
            $me->test('setPermission, valid array integer', $obj->getPermission() == array(
                'select'=>true,
                'insert'=>true,
                'update'=>true,
                'delete'=>false
                ));
            
            $obj->setPermission(1);
            $me->test('setPermission, valid integer', $obj->getPermission() == array(
                'select'=>false,
                'insert'=>false,
                'update'=>false,
                'delete'=>true
                ));

            $obj->setPermission('f');
            $me->test('setPermission, valid hex', $obj->getPermission() == array(
                'select'=>true,
                'insert'=>true,
                'update'=>true,
                'delete'=>true
                ));
        });

        $me->test('setSelectPermission', method_exists($class, 'setSelectPermission'), function() use($me, $class)
        {
            $obj = $class::create('a');
            $perm = $obj->setSelectPermission(false);
            $me->test('setSelectPermission, invalid, value', $obj->getSelectPermission() === false);
            $perm = $obj->setSelectPermission(true);
            $me->test('setSelectPermission, valid, value', $obj->getSelectPermission() === true);
            $perm = $obj->setSelectPermission('false');
            $me->test('setSelectPermission, invalid, value string', is_bool($obj->getSelectPermission())); // set default by input
            $perm = $obj->setSelectPermission(1);
            $me->test('setSelectPermission, invalid, value integer', is_bool($obj->getSelectPermission())); // set default by input
        });

        $me->test('setInsertPermission', method_exists($class, 'setInsertPermission'), function() use($me, $class)
        {
            $obj = $class::create('a');
            $perm = $obj->setInsertPermission(false);
            $me->test('setInsertPermission, invalid, value', $obj->getInsertPermission() === false);
            $perm = $obj->setInsertPermission(true);
            $me->test('setInsertPermission, valid, value', $obj->getInsertPermission() === true);
            $perm = $obj->setInsertPermission('false');
            $me->test('setInsertPermission, invalid, value string', is_bool($obj->getInsertPermission())); // set default by input
            $perm = $obj->setInsertPermission(1);
            $me->test('setInsertPermission, invalid, value integer', is_bool($obj->getInsertPermission())); // set default by input
        });

        $me->test('setUpdatePermission', method_exists($class, 'setUpdatePermission'), function() use($me, $class)
        {
            $obj = $class::create('a');
            $perm = $obj->setUpdatePermission(false);
            $me->test('setUpdatePermission, invalid, value', $obj->getUpdatePermission() === false);
            $perm = $obj->setUpdatePermission(true);
            $me->test('setUpdatePermission, valid, value', $obj->getUpdatePermission() === true);
            $perm = $obj->setUpdatePermission('false');
            $me->test('setUpdatePermission, invalid, value string', is_bool($obj->getUpdatePermission())); // set default by input
            $perm = $obj->setUpdatePermission(1);
            $me->test('setUpdatePermission, invalid, value integer', is_bool($obj->getUpdatePermission())); // set default by input
        });

        $me->test('setDeletePermission', method_exists($class, 'setDeletePermission'), function() use($me, $class)
        {
            $obj = $class::create('a');
            $perm = $obj->setDeletePermission(false);
            $me->test('setDeletePermission, invalid, value', $obj->getDeletePermission() === false);
            $perm = $obj->setDeletePermission(true);
            $me->test('setDeletePermission, valid, value', $obj->getDeletePermission() === true);
            $perm = $obj->setDeletePermission('false');
            $me->test('setDeletePermission, invalid, value string', is_bool($obj->getDeletePermission())); // set default by input
            $perm = $obj->setDeletePermission(1);
            $me->test('setDeletePermission, invalid, value integer', is_bool($obj->getDeletePermission())); // set default by input
        });

        $me->test('getSelectPermission', method_exists($class, 'getSelectPermission'), function() use($me, $class)
        {
            $obj = $class::create('b');
            $me->test('getSelectPermission, valid, value', $obj->getSelectPermission() === true);
            $obj = $class::create();
            $me->test('getSelectPermission, invalid, null', $obj->getSelectPermission() === false);
        });

        $me->test('getInsertPermission', method_exists($class, 'getInsertPermission'), function() use($me, $class)
        {
            $obj = $class::create('f');
            $me->test('getInsertPermission, valid, value', $obj->getInsertPermission() === true);
            $obj = $class::create();
            $me->test('getInsertPermission, invalid, null', $obj->getInsertPermission() === false);
        });

        $me->test('getUpdatePermission', method_exists($class, 'getUpdatePermission'), function() use($me, $class)
        {
            $obj = $class::create('f');
            $me->test('getUpdatePermission, valid, value', $obj->getUpdatePermission() === true);
            $obj = $class::create();
            $me->test('getUpdatePermission, invalid, null', $obj->getUpdatePermission() === false);
        });

        $me->test('getDeletePermission', method_exists($class, 'getDeletePermission'), function() use($me, $class)
        {
            $obj = $class::create('f');
            $me->test('getDeletePermission, valid, value', $obj->getDeletePermission() === true);
            $obj = $class::create();
            $me->test('getDeletePermission, invalid, null', $obj->getDeletePermission() === false);
        });

        $me->test('toHex', method_exists($class, 'toHex'), function() use($me, $class)
        {
            $obj = $class::create('f');
            $me->test('toHex, valid, value', $obj->toHex() === 'F');
            $obj = $class::create();
            $me->test('toHex, valid, null', $obj->toHex() === '0'); //default value
            $obj = $class::create(8);
            $me->test('toHex, valid, value integer', $obj->toHex() === '8');
        });

        $me->test('toDec', method_exists($class, 'toDec'), function() use($me, $class)
        {
            $obj = $class::create('a');
            $me->test('toDec, valid, value', $obj->toDec() === 10);
            $obj = $class::create();
            $me->test('toDec, valid, null', $obj->toDec() === 0); //default value
            $obj = $class::create(8);
            $me->test('toDec, valid, value integer', $obj->toDec() === 8);
            $obj = $class::create('8');
            $me->test('toDec, valid, value string', $obj->toDec() === 8);
        });

        $me->test('toBin', method_exists($class, 'toBin'), function() use($me, $class)
        {
            $obj = $class::create('a');
            $me->test('toBin, valid, value', $obj->toBin() === '1010');
            $obj = $class::create();
            $me->test('toBin, valid, null', $obj->toBin() === '0'); //default value
            $obj = $class::create(8);
            $me->test('toBin, valid, value integer', $obj->toBin() === '1000');
            $obj = $class::create('8');
            $me->test('toBin, valid, value string', $obj->toBin() === '1000');
        });

        $this->createUnfinishedTask();
    }
}