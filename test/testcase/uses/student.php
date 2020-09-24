<?php 
use Roulette\Actor;
use Roulette\Model;
use Roulette\Association;

$student_fields = [
    'id' => [
        'display'=>'ID', 
        'update'=>false,
        'unique'=>true, 
        'validation'=>[
            'nullable'=>false
        ]
    ],
    'name' => [
        'display'=>'Name', 
        'default'=>'johndoe',
        'renderer'=>function($value, $rec)
        {
            return $value;
        },
        'validation'=>[
            'nullable'=>false,
            'maxlength'=>20,
            'minlength'=>4,
            function($value, $validation)
            {
                if($value == 'rudi'){
                    $validation->add_message('can not use "{value}" in {field}');
                    return false;
                }
                if($value == 'nina'){
                    $validation->add_message('"{value}" Already registered');
                    return false;
                }
            }
        ],
        'converter'=>function($value)
        {
            return strtolower($value);
        }
    ],
    'gender' => [
        'source'=>'sex',
        'default'=>0,
        'reader'=>function($v){
            if( $v == 0 ){
                return 'f';
            }else if($v == 1){
                return 'm';
            }
        },
        'writer'=> function($v){
            if(is_string($v)) $v = strtolower($v);
            if( in_array($v, [0, '0', 'f', 'F', 'female']) ){
                return 0;
            }else{
                return 1;
            }
        },
        'converter'=>function($v)
        {
            if(is_string($v)) $v = strtolower($v);
            if( in_array($v, [0, '0', 'f', 'F', 'female']) ){
                return 'm';
            }else{
                return 'f';
            }
        },
        'renderer'=>function($v)
        {
            if( $v == 'm' ){
                return 'male';
            }elseif($v == 'f'){
                return 'female';
            }
        }
    ],
    'faculty' => [
        'display'=>'Faculty', 
        'validation'=>[
            'maxlength'=>50
        ], 
        'renderer'=>function($v, $rec){
            if($v instanceof Model)
            {
                return "faculty is (".$v->get('id').") ".$v->get('nama');
            }
            else if(is_string($v))
            {
                return 'faculty of '.$rec->getId().' is '.$v;
            }
            else
            {
                return 'sadly has no faculty';
            }
        }
    ],
    'class',
    'password' => [ 
        'private'=>true, 
        'renderer'=>function($v){
            return '[encrypted]';
        }
    ],
    'age' => [ 
        'renderer'=>function($v){
            if(!empty($v))
            {
                return $v.' years old';
            }
            return "not born yet";
        }
    ],
    'address'
];

// class Student extends Actor
// {
//     static protected $prototype = null;
// }

// Student::prototype([
//     'table'=>'student',
//     'rights'=>'FA3', // ['F','A',3] or ['1111','1100','1010']
//     'primary'=>'id',
//     'autoId'=>true,
//     'fields'=>$student_fields,
//     'associations'=>[
//         'faculty' => [
//             'type'  =>Association::HASONE,
//             'model' =>'Faculty',
//             'field' =>'faculty'
//         ],
//         'class' => [
//             'type'  =>Association::HASONE,
//             'model' =>'StudentClass',
//             'field' =>'class',
//         ],
//         'hobbies' => [
//             'type'  =>Association::HASMANY,
//             'model' =>'Hobby',
//             'field' =>'student',
//         ],
//     ],
//     'sources'=>[
//         'full'=>[
//             'table'=>'v_student',
//             'joins'=>[
//                 ['association'=>'faculty', 'identifier'=>'/^(faculty_)/', 'resolver'=>['/^(faculty_)/', '']],
//                 ['association'=>'class', 'identifier'=>'/^(class_)/', 'resolver'=>['/^(class_)/', '']]
//             ]
//         ],
//         'withFaculty'=>[
//             'table'=>'v_student',
//             'joins'=>[
//                 ['association'=>'faculty', 'identifier'=>'/^(faculty_)/', 'resolver'=>['/^(faculty_)/', '']]
//             ]
//         ],
//         'withClass'=>[
//             'table'=>'v_student',
//             'joins'=>[
//                 ['association'=>'class', 'identifier'=>'/^(class_)/', 'resolver'=>['/^(faculty_)/', '']]
//             ]
//         ],
//         'dev'=>[
//             'table'=>'v_student',
//             'joins'=>[
//                 ['association'=>'faculty', 'identifier'=>'/^(faculty_)/'],
//                 ['association'=>'faculty', 'identifier'=>function($field, $value, $rawData, $join){}],
//                 ['association'=>'faculty', 'resolver'=>['/^(faculty_)/', 'string replacer']],
//                 ['association'=>'faculty', 'resolver'=>function($field, $value, $rawData, $join){}],
//                 ['association'=>'faculty', 'resolver'=>'sometext_{field}_sometext'],
//             ]
//         ],
//     ],
//     'views'=>[
//         'name',
//         'all' =>'*',
//         'basic' => ['id','name'],
//         'withFaculty' => [
//             'autoLoad'=> true, 'inline'=> 'faculty'
//         ]
//     ]
// ]);