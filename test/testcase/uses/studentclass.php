<?php

use Roulette\Model;

class StudentClass extends Model 
{
    static protected $prototype = null;
}

StudentClass::prototype(array(
    'table'=>'class',
    'primary'=>'id',
    'fields'=> array(
        array('name'=>'id', 'display'=>'ID', 'update'=>false, 'unique'=>true),
        'name'
    ),
    'associations' => array(
    	array(
    		'name'=>'students', 'type'=>'hasMany', 'model'=>'Student', 'field'=>'class'
    	)
    ),
    'properties' => array(
        'table'=>null,
        'fieldCreatedBy'=>'created_by',
        'fieldCreatedOn'=>'created_on',
        'fieldCreatedOn'=>'created_on'
    )
));
