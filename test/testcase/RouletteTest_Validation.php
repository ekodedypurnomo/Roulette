<?php

class RouletteTest_Validation extends RouletteUnittest_Model {

    public $name = 'Roulette\Validation';

    protected $skip = array('is','isNot');
    
    public function __construct() {
        parent::__construct();
    }

    function index() {
        $me = $this;
        $class = $this->name;
        
        $me->test('getMessageTemplates', method_exists($class, 'getMessageTemplates'), function() use($me, $class)
        {
            $obj = new $class(array(
                'validators'=>array()
                ));

            $me->test('getMessageTemplates, array', is_array($obj->getMessageTemplates()) );
            $me->test('getMessageTemplates, string', is_null($obj->getMessageTemplates('nullable')) );
            $me->test('getMessageTemplates, undefined', is_null($obj->getMessageTemplates('undefined')) );
        
            $obj = new $class(array( 
                'validators'=>array(), 
                'messageTemplates'=>array(
                    'nullable'=> 'dont null please'
                    )
                ));

            $me->test('getMessageTemplates, override', $obj->getMessageTemplates('nullable') == 'dont null please' );
        });

        $me->test('addValidator', method_exists($class, 'addValidator') && method_exists($class, 'getValidators'), function() use($me, $class)
        {
            $obj = new $class(array( 
                'validators'=>array() 
            ));

            $me->test('addValidator, before add', count($obj->getValidators()) == 0 );
            
            $obj->addValidator('nullable', true);
            $me->test('addValidator, after add', count($obj->getValidators()) > 0 );

            $obj->addValidator('minvalue', 5);
            $obj->addValidator('custom', function(){});
            $obj->addValidator(function(){return;},'should false');
            $me->test('addValidator, after add2', count($obj->getValidators()) == 4 );
        });

        $me->test('reset', method_exists($class, 'reset') && method_exists($class, 'getValidators'), function() use($me, $class)
        {
            $obj = new $class(array( 'validators'=>array() ));
            
            $me->test('reset, before, insert, before', count($obj->getValidators()) == 0 );
            
            $obj->addValidator('nullable', true);
            $me->test('reset, before, insert, after', count($obj->getValidators()) > 0 );
            
            $obj->reset();
            $me->test('reset, after, validator', count($obj->getValidators()) == 0 );
            $me->test('reset, after, message', count($obj->getMessageTemplates()) == 0 );
        });

        $me->test('validate', method_exists($class, 'validate'), function() use($me, $class)
        {            
            $me->test('validate, above', true, function() use($me, $class)
            {
                $validator = new Roulette\Validator\Above(
                    1, '{value}>{rule}'
                );
                $me->test('validate, above, object, below', $validator->test(-1) == false );
                $me->test('validate, above, object, equal', $validator->test(1) == false );
                $me->test('validate, above, object, above', $validator->test(2) == true );
                $me->test('validate, above, object, invalid', $validator->test('a') == false );
                $me->test('validate, above, object, message', $validator->getMessage() == '>1' );
                $me->test('validate, above, object, message with params', $validator->getMessage(1) == '1>1' );
            });
            
            $me->test('validate, bellow', true, function() use($me, $class)
            {
                $validator = new Roulette\Validator\Below(
                    1, '{value}<{rule}'
                );
                $me->test('validate, below, object, below', $validator->test(-1) == true );
                $me->test('validate, below, object, equal', $validator->test(1) == false );
                $me->test('validate, below, object, above', $validator->test(2) == false );
                $me->test('validate, below, object, invalid', $validator->test('a') == false );
                $me->test('validate, below, object, message', $validator->getMessage() == '<1' );
                $me->test('validate, below, object, message with params', $validator->getMessage(1) == '1<1' );
            });

            // $me->test('validate, boolean', false, function() use($me, $class){});

            $me->test('validate, custom', true, function() use($me, $class)
            {
                $validFn = function($value = null){
                    return !is_null($value);
                };

                $validator = new Roulette\Validator\Custom(
                    $validFn, 
                    '{value} != validation formula'
                );
                $me->test('validate, custom, object, valid', $validator->test('dummy') == true);
                $me->test('validate, custom, object, invalid', $validator->test(null) == false);
                $me->test('validate, custom, object, message with value', $validator->getMessage(1) == '1 != validation formula');
                $me->test('validate, custom, object, message without value', $validator->getMessage() == ' != validation formula');
            });

            // $me->test('validate, double', false, function() use($me, $class){});

            $me->test('validate, exclusion', true, function() use($me, $class)
            {
                $validator = new Roulette\Validator\Exclusion(
                    array('a','b'), '{value}:ex:{rule}'
                );
                $me->test('validate, exclusion, object, in', $validator->test('a') == false );
                $me->test('validate, exclusion, object, ex', $validator->test('e') == true );
                $me->test('validate, exclusion, object, invalid', $validator->test(null) == true );
                $me->test('validate, exclusion, object, message with value', $validator->getMessage(1) == '1:ex:a,b' );
                $me->test('validate, exclusion, object, message without value', $validator->getMessage() == ':ex:a,b' );
            });

            // $me->test('validate, float', false, function() use($me, $class){});

            $me->test('validate, format', true, function() use($me, $class)
            {
                $validator = new Roulette\Validator\Format(
                    '/dummy/', '{value}={rule}'
                );
                $me->test('validate, format, object, informat', $validator->test('dummy') == true );
                $me->test('validate, format, object, exformat', $validator->test('dummies') == false );
                $me->test('validate, format, object, invalid', $validator->test(null) == false );
                $me->test('validate, format, object, message with value', $validator->getMessage() == '=/dummy/' );
                $me->test('validate, format, object, message without value', $validator->getMessage(1) == '1=/dummy/' );
            });

            $me->test('validate, inclusion', true, function() use($me, $class)
            {
                $validator = new Roulette\Validator\Inclusion(
                    array('a','b'), '{value}:in:{rule}'
                );
                $me->test('validate, inclusion, object, in', $validator->test('a') == true );
                $me->test('validate, inclusion, object, ex', $validator->test('e') == false );
                $me->test('validate, inclusion, object, invalid', $validator->test(null) == false );
                $me->test('validate, inclusion, object, message with value', $validator->getMessage(1) == '1:in:a,b' );
                $me->test('validate, inclusion, object, message without value', $validator->getMessage() == ':in:a,b' );
            });

            // $me->test('validate, integer', false, function() use($me, $class){});

            $me->test('validate, maxlength', true, function() use($me, $class)
            {
                $validator = new Roulette\Validator\Maxlength(
                    2, '{value}+{rule}'
                );
                $me->test('validate, maxlength, object, below', $validator->test('a') == true );
                $me->test('validate, maxlength, object, above', $validator->test('ab') == true );
                $me->test('validate, maxlength, object, over', $validator->test('abc') == false );
                $me->test('validate, maxlength, object, invalid', $validator->test(999) == false );
                $me->test('validate, maxlength, object, message', $validator->getMessage() == '+2' );
                $me->test('validate, maxlength, object, message with params', $validator->getMessage(1) == '1+2' );
            });

            $me->test('validate, maxvalue', true, function() use($me, $class)
            {
                $validator = new Roulette\Validator\Maxvalue(
                    1, '{value}<={rule}'
                );
                $me->test('validate, maxvalue, object, below', $validator->test(-1) == true );
                $me->test('validate, maxvalue, object, max', $validator->test(1) == true );
                $me->test('validate, maxvalue, object, above', $validator->test(2) == false );
                $me->test('validate, maxvalue, object, invalid', $validator->test('a') == false );
                $me->test('validate, maxvalue, object, message', $validator->getMessage() == '<=1' );
                $me->test('validate, maxvalue, object, message with params', $validator->getMessage(1) == '1<=1' );
            });

            $me->test('validate, minlength', true, function() use($me, $class)
            {
                $validator = new Roulette\Validator\Minlength(
                    2, '{value}-{rule}'
                );
                $me->test('validate, minlength, object, below', $validator->test('ab') == true );
                $me->test('validate, minlength, object, above', $validator->test('abc') == true );
                $me->test('validate, minlength, object, over', $validator->test('a') == false );
                $me->test('validate, minlength, object, invalid', $validator->test(9) == false );
                $me->test('validate, minlength, object, message', $validator->getMessage() == '-2' );
                $me->test('validate, minlength, object, message with params', $validator->getMessage(1) == '1-2' );
            });

            $me->test('validate, minvalue', true, function() use($me, $class)
            {
                $validator = new Roulette\Validator\Minvalue(
                    1, '{value}>={rule}'
                );
                $me->test('validate, minvalue, object, below', $validator->test(-1) == false );
                $me->test('validate, minvalue, object, min', $validator->test(1) == true );
                $me->test('validate, minvalue, object, above', $validator->test(2) == true );
                $me->test('validate, minvalue, object, invalid', $validator->test('a') == false );
                $me->test('validate, minvalue, object, message', $validator->getMessage() == '>=1' );
                $me->test('validate, minvalue, object, message with params', $validator->getMessage(1) == '1>=1' );
            });

            $me->test('validate, nullable', true, function() use($me, $class)
            {
                $validator = new Roulette\Validator\Nullable(
                    false, 'value:{value}'
                );
                $me->test('validate, nullable, object, empty', $validator->test() === false );
                $me->test('validate, nullable, object, null', $validator->test(null) === false );
                $me->test('validate, nullable, object, notnull', $validator->test(1) === true );
                $me->test('validate, nullable, object, message', $validator->getMessage() == 'value:' );
                $me->test('validate, nullable, object, message with params', $validator->getMessage(1) == 'value:1' );

                $validator = new Roulette\Validator\Nullable(true);
                $me->test('validate, nullable, object, nullable, empty', $validator->test() === true );
                $me->test('validate, nullable, object, nullable, null', $validator->test(null) === true );
                $me->test('validate, nullable, object, nullable, notnull', $validator->test(1) === true );
            });
        });

        $this->createUnfinishedTask();
    }

}