<?php

use Roulette\Model;
use Roulette\Actor;

class Hobby extends Model 
{
    static protected $prototype = null;
}

Hobby::prototype(array(
    'table'=>'hobby',
    'primary'=>'id',
    'fields'=> array(
        array('name'=>'id', 'update'=>false, 'insert'=>false),
        array('name'=>'name', 'display'=>'Name', 'default'=>'[hobby]'),
        array('name'=>'student')
    ),
    'policies'=>[
        'read'=>function(Actor $user, Hobby $record){
            dump('verifying');
            if($record)
            {
                return $record->get('student') == $user->getId();
            }
        },
        'list'=>function(Actor $user, $model, Option $queryOption = null){
            echo "executed and return `false`";

            // $queryOption->where('student', $user->getId());
        }
    ]
));
