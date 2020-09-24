<?php

use Roulette\Model;

class Faculty extends Model 
{
    static protected $prototype = null;
}

Faculty::prototype(array(
    'table'=>'faculty',
    'primary'=>'id',
    'fields'=> array(
        array('name'=>'id', 'display'=>'ID', 'update'=>false, 'unique'=>true, 
            'validation'=>array(
                'maxlength'=>10,
                'notnull'=>true
            ),
            'renderer'=>"self::ucwords"
        ),
        array('name'=>'name', 'display'=>'Name', 'default'=>'[nama]',
            'validation'=>array(
                'notnull'=>true,
            ),
            'preparer'=>function($value){
                return strtolower($value);
            },
            'renderer'=>get_class()."::ucwords"
        ),
        array('name'=>'desc', 'display'=>'Faculty')
    )
));
