<?php 

class RouletteTest_data_Value extends RouletteUnittest_Model
{
    public $name = 'Roulette\Data\Value';

    protected $skip = array('is','isNot');

    public function __construct() {
        parent::__construct();
    }

    function index() {
        $me = $this;
        $class = $this->name;
        $modelC = 'Roulette\Model';
        $fieldC = 'Roulette\Model\Field\Field';

        $me->test('property', true, function() use($me, $class, $modelC, $fieldC)
        {
            $me->test('property, field', property_exists($class, 'field'));

            $me->test('property, record', property_exists($class, 'record'));

            $me->test('property, original', property_exists($class, 'original'));

            $me->test('property, raw', property_exists($class, 'raw'));

            $me->test('property, display', property_exists($class, 'display'));
            
            $me->test('property, error', property_exists($class, 'error'));
        });

        $me->test('getField', method_exists($class, 'getField'), function() use($me, $class, $modelC, $fieldC)
        {
            $field = new $fieldC;
            $value = new $class(new $modelC, $field);
            $me->test('getField, after set', $value->getField() === $field );
        });

        $me->test('getRecord', method_exists($class, 'getRecord'), function() use($me, $class, $modelC, $fieldC)
        {
            $record = new $modelC;
            $value = new $class($record, new $fieldC);

            $me->test('getRecord, after set', $value->getRecord() === $record );
        });

        $me->test('setgetOriginal', method_exists($class, 'setOriginal') && method_exists($class, 'getOriginal'), function() use($me, $class, $modelC, $fieldC)
        {
            $var = (string) rand();
            $field = new $fieldC(array(
                'name'=>'field', 
                'reader'=>function($value){
                    return 'g:'.$value;
                },
                'writer'=>function($value){
                    return 'w:'.$value;
                }
            ));
            
            // test before anything
            $fieldValue = new $class(new $modelC, $field);
            $me->test('setgetOriginal, before, modified', $fieldValue->isModified() == false);
            $me->test('setgetOriginal, before, raw', $fieldValue->getValue(false) == 'g:');
            $me->test('setgetOriginal, before, display', $fieldValue->getValue() == 'g:');

            // test default
            $fieldValue = new $class(new $modelC, $field);
            $fieldValue->setOriginal(); // default $revert = false, $read = true, $useDefault = true
            $me->test('setgetOriginal, after, default, modified', $fieldValue->isModified() == false);
            $me->test('setgetOriginal, after, default, original', $fieldValue->getOriginal() == 'g:');
            $me->test('setgetOriginal, after, default, raw', $fieldValue->getValue(false) == 'g:');

            // test default valued
            $fieldValue = new $class(new $modelC, $field);
            $fieldValue->setOriginal($var); // default $revert = false, $read = true, $useDefault = true
            $me->test('setgetOriginal, after, default valued, modified', $fieldValue->isModified() == true);
            $me->test('setgetOriginal, after, default valued, original', $fieldValue->getOriginal() == 'g:'.$var);
            $me->test('setgetOriginal, after, default valued, raw', $fieldValue->getValue(false) == 'g:');

            // test norevert noread nodefault
            $fieldValue = new $class(new $modelC, $field);
            $fieldValue->setOriginal($var, true, true);
            $me->test('setgetOriginal, after, norevert noread nodefault, modified', $fieldValue->isModified() == false);
            $me->test('setgetOriginal, after, norevert noread nodefault, original', $fieldValue->getOriginal() == 'g:'.$var);
            $me->test('setgetOriginal, after, norevert noread nodefault, raw', $fieldValue->getValue(false) == 'g:'.$var);

            // test norevert read nodefault
            $fieldValue = new $class(new $modelC, $field);
            $fieldValue->setOriginal($var, true, true);
            $me->test('setgetOriginal, after, norevert read nodefault, modified', $fieldValue->isModified() == false);
            $me->test('setgetOriginal, after, norevert read nodefault, original', $fieldValue->getOriginal() == 'g:'.$var);
            $me->test('setgetOriginal, after, norevert read nodefault, raw', $fieldValue->getValue(false) == 'g:'.$var);

            // test norevert noread default
            $fieldValue = new $class(new $modelC, $field);
            $fieldValue->setOriginal($var, true, true);
            $me->test('setgetOriginal, after, norevert noread default, modified', $fieldValue->isModified() == false);
            $me->test('setgetOriginal, after, norevert noread default, original', $fieldValue->getOriginal() == 'g:'.$var);
            $me->test('setgetOriginal, after, norevert noread default, raw', $fieldValue->getValue(false) == 'g:'.$var);

            // test revert noread nodefault
            $fieldValue = new $class(new $modelC, $field);
            $fieldValue->setOriginal($var, true, true);
            $me->test('setgetOriginal, after, revert noread nodefault, modified', $fieldValue->isModified() == false);
            $me->test('setgetOriginal, after, revert noread nodefault, original', $fieldValue->getOriginal() == 'g:'.$var);
            $me->test('setgetOriginal, after, revert noread nodefault, raw', $fieldValue->getValue(false) == 'g:'.$var);

            // test revert read default
            $fieldValue = new $class(new $modelC, $field);
            $fieldValue->setOriginal($var, true, true);
            $me->test('setgetOriginal, after, revert read default, modified', $fieldValue->isModified() == false);
            $me->test('setgetOriginal, after, revert read default, original', $fieldValue->getOriginal() == 'g:'.$var);
            $me->test('setgetOriginal, after, revert read default, raw', $fieldValue->getValue(false) == 'g:'.$var);

            // now test if field have a default value
            $field->setDefault(0);
            $fieldValue->setOriginal(null, true);
            $me->test('setgetOriginal, after, default value, null, original', $fieldValue->getOriginal() == 'g:'.$field->getDefault());
            $me->test('setgetOriginal, after, default value, null, raw', $fieldValue->getValue(false) == 'g:'.$field->getDefault());
            
            $fieldValue->setOriginal($var, true);
            $me->test('setgetOriginal, after, default value, notnull, original', $fieldValue->getOriginal() == 'g:'.$var);
            $me->test('setgetOriginal, after, default value, notnull, raw', $fieldValue->getValue(false) == 'g:'.$var);
        });

        $me->test('setgetValue', method_exists($class, 'setValue') && method_exists($class, 'getValue'), function() use($me, $class, $modelC, $fieldC)
        {
            $var = (string) rand();
            $field = new $fieldC(array(
                'name'=>'field', 
                'converter'=>function($value){
                    return 'c:'.$value;
                },
                'renderer'=>function($value){
                    return 'r:'.$value;
                }
            ));
        
            // test before anything
            $fieldValue = new $class(new $modelC, $field);
            $me->test('setgetValue, before, modified', $fieldValue->isModified() == false);
            $me->test('setgetValue, before, raw', $fieldValue->getValue(false) == null);
            $me->test('setgetValue, before, display', $fieldValue->getValue() == 'r:');

            // test default
            $fieldValue = new $class(new $modelC, $field);
            $fieldValue->setValue(); // default is commit=false convert=true
            $me->test('setgetValue, after, default, modified', $fieldValue->isModified() == true);
            $me->test('setgetValue, after, default, raw', $fieldValue->getValue(false) == 'c:');
            $me->test('setgetValue, after, default, display', $fieldValue->getValue() == 'r:c:');
        
            // test default valued
            $fieldValue = new $class(new $modelC, $field);
            $fieldValue->setValue($var); // default is commit=false convert=true
            $me->test('setgetValue, after, default valued, modified', $fieldValue->isModified() == true);
            $me->test('setgetValue, after, default valued, raw', $fieldValue->getValue(false) == 'c:'.$var);
            $me->test('setgetValue, after, default valued, display', $fieldValue->getValue() == 'r:c:'.$var);

            // test nocommit noconvert
            $fieldValue = new $class(new $modelC, $field);
            $fieldValue->setValue($var, false, false);
            $me->test('setgetValue, after, nocommit noconvert, modified', $fieldValue->isModified() == true);
            $me->test('setgetValue, after, nocommit noconvert, raw', $fieldValue->getValue(false) == $var);
            $me->test('setgetValue, after, nocommit noconvert, display', $fieldValue->getValue() == 'r:'.$var);

            // test nocommit convert
            $fieldValue->setValue($var, false, true);
            $me->test('setgetValue, after, nocommit convert, modified', $fieldValue->isModified() == true);
            $me->test('setgetValue, after, nocommit convert, raw', $fieldValue->getValue(false) == 'c:'.$var);
            $me->test('setgetValue, after, nocommit convert, display', $fieldValue->getValue() == 'r:c:'.$var);

            // test commit noconvert
            $fieldValue = new $class(new $modelC, $field);
            $fieldValue->setValue($var, true, false);
            $me->test('setgetValue, after, commit noconvert, modified', $fieldValue->isModified() == false);
            $me->test('setgetValue, after, commit noconvert, original', $fieldValue->getOriginal() == $var);
            $me->test('setgetValue, after, commit noconvert, raw', $fieldValue->getValue(false) == $var);
            $me->test('setgetValue, after, commit noconvert, display', $fieldValue->getValue() == 'r:'.$var);

            // test commit convert
            $fieldValue = new $class(new $modelC, $field);
            $fieldValue->setValue($var, true, true);
            $me->test('setgetValue, after, commit convert, modified', $fieldValue->isModified() == false);
            $me->test('setgetValue, after, commit convert, original', $fieldValue->getOriginal() == 'c:'.$var);
            $me->test('setgetValue, after, commit convert, raw', $fieldValue->getValue(false) == 'c:'.$var);
            $me->test('setgetValue, after, commit convert, display', $fieldValue->getValue() == 'r:c:'.$var);
        });

        $me->test('isModified', method_exists($class, 'isModified'), function() use($me, $class, $modelC, $fieldC)
        {
            $var = (string) rand();
            $field = new $fieldC(array(
                'name'=>'field', 
                'converter'=>function($value){
                    return 'c:'.$value;
                },
                'renderer'=>function($value){
                    return 'r:'.$value;
                },
                'reader'=>function($value){
                    return 'g:'.$value;
                },
                'writer'=>function($value){
                    return 'w:'.$value;
                }
            ));
            $fieldValue = new $class(new $modelC, $field);
            
            $me->test('isModified, before', $fieldValue->isModified() == false);

            $fieldValue->setValue($var, false);
            $me->test('isModified, after setValue', $fieldValue->isModified() == true);

            $fieldValue->commit();
            $me->test('isModified, after commit', $fieldValue->isModified() == false);
            
            $fieldValue->setOriginal($var, false);
            $me->test('isModified, after setOriginal', $fieldValue->isModified() == true);

            $fieldValue->revert();
            $me->test('isModified, after', $fieldValue->isModified() == false);
        });

        $me->test('validate', method_exists($class, 'validate') && method_exists($class, 'isValid') && method_exists($class, 'getError'), function() use($me, $class, $modelC, $fieldC)
        {
            $var = (string) rand(0,99);
            $field = new $fieldC(array(
                'name' => 'field', 
                // detail test of each validation is on validation_test class, here is just for test the anatomy 
                'validation' => array('nullable'=>false)
            ));
            $fieldValue = new $class(new $modelC, $field);

            // validate is trigger manually, so we need to trigger validate instead
            $me->test('validate, before, isValid', $fieldValue->isValid() == true);
            $me->test('validate, before, message', empty($fieldValue->getError()));

            $fieldValue->validate();
            $me->test('validate, after, isValid', $fieldValue->isValid() == false);
            $me->test('validate, after, message', !empty($fieldValue->getError()));

            $fieldValue->setValue($var)->validate();
            $me->test('validate, valid, isValid', $fieldValue->isValid() == true);
            $me->test('validate, valid, message', empty($fieldValue->getError()));

            $var = (string) rand(10,99);
            $field = new $fieldC(array(
                'name' => 'field', 
                'validation' => array('maxvalue'=>9, 'above'=>99) // make value tobe error
            ));
            $fieldValue = new $class(new $modelC, $field);
            $me->test('validate, errorMessages, before', empty($fieldValue->getError()));
            $fieldValue->setValue($var)->validate();
            $me->test('validate, errorMessages, count', count($fieldValue->getError()) > 0);
        });

        $me->test('commit', method_exists($class, 'commit'), function() use($me, $class, $modelC, $fieldC)
        {
            $var = (string) rand();
            $field = new $fieldC(array(
                'name'=>'field', 
                'converter'=>function($value){
                    return 'c:'.$value;
                },
                'renderer'=>function($value){
                    return 'r:'.$value;
                },
                'reader'=>function($value){
                    return 'g:'.$value;
                },
                'writer'=>function($value){
                    return 'w:'.$value;
                }
            ));
            $fieldValue = new $class(new $modelC, $field);
            
            // commit will affect on modified and disparity of original value and raw value 
            $me->test('commit, before set, modified', $fieldValue->isModified() == false);
            $me->test('commit, before set, value', $fieldValue->getOriginal() == $fieldValue->getRaw());

            $fieldValue->setValue($var);
            $me->test('commit, after set, modified', $fieldValue->isModified() == true);
            $me->test('commit, after set, value', $fieldValue->getOriginal() != $fieldValue->getRaw());

            $fieldValue->commit();
            $me->test('commit, after commit, modified', $fieldValue->isModified() == false);
            $me->test('commit, after commit, value', $fieldValue->getOriginal() == $fieldValue->getRaw());
        });

        $me->test('revert', method_exists($class, 'revert'), function() use($me, $class, $modelC, $fieldC)
        {
            $var = (string) rand();
            $field = new $fieldC(array(
                'name'=>'field', 
                'converter'=>function($value){
                    return 'c:'.$value;
                },
                'renderer'=>function($value){
                    return 'r:'.$value;
                },
                'reader'=>function($value){
                    return 'g:'.$value;
                },
                'writer'=>function($value){
                    return 'w:'.$value;
                }
            ));
            $fieldValue = new $class(new $modelC, $field);
            
            // revert is opposite of commit
            $me->test('revert, before set, modified', $fieldValue->isModified() == false);
            $me->test('revert, before set, value', $fieldValue->getOriginal() == $fieldValue->getRaw());

            $fieldValue->setOriginal($var);
            $me->test('revert, after set, modified', $fieldValue->isModified() == true);
            $me->test('revert, after set, value', $fieldValue->getOriginal() != $fieldValue->getRaw());

            $fieldValue->revert();
            $me->test('revert, after revert, modified', $fieldValue->isModified() == false);
            $me->test('revert, after revert, value', $fieldValue->getOriginal() == $fieldValue->getRaw());
        });

        $me->test('render', method_exists($class, 'render'), function() use($me, $class, $modelC, $fieldC)
        {
            $var = (string) rand();
            $field = new $fieldC(array(
                'name'=>'field', 
                'converter'=>function($value){
                    return 'c:'.$value;
                },
                'renderer'=>function($value){
                    return 'r:'.$value;
                },
                'reader'=>function($value){
                    return 'g:'.$value;
                },
                'writer'=>function($value){
                    return 'w:'.$value;
                }
            ));
            $fieldValue = new $class(new $modelC, $field);
            
            // render only affect on display value, and it trigger automatically
            $me->test('render, before set, validity', $fieldValue->getDisplay() != $fieldValue->getRaw());
            $me->test('render, before set, value', $fieldValue->getDisplay() == 'r:g:');

            $fieldValue->setValue($var);
            $me->test('render, after set, validity', $fieldValue->getDisplay() != $fieldValue->getRaw());
            $me->test('render, after set, value', $fieldValue->getDisplay() == 'r:c:'.$var);
        });

        $me->test('getWriteValue', method_exists($class, 'getWriteValue'), function() use($me, $class, $modelC, $fieldC)
        {
            $var = (string) rand();
            $field = new $fieldC(array(
                'name'=>'field', 
                'converter'=>function($value){
                    return 'c:'.$value;
                },
                'renderer'=>function($value){
                    return 'r:'.$value;
                },
                'reader'=>function($value){
                    return 'g:'.$value;
                },
                'writer'=>function($value){
                    return 'w:'.$value;
                }
            ));
            $fieldValue = new $class(new $modelC, $field);
            $me->test('getWriteValue, get', $fieldValue->getWriteValue());
        });

        $me->test('setOriginal', method_exists($class, 'setOriginal'), function() use($me, $class, $modelC, $fieldC)
        {
            $var = (string) rand();
            $field = new $fieldC(array(
                'name'=>'field', 
                'converter'=>function($value){
                    return 'c:'.$value;
                },
                'renderer'=>function($value){
                    return 'r:'.$value;
                },
                'reader'=>function($value){
                    return 'g:'.$value;
                },
                'writer'=>function($value){
                    return 'w:'.$value;
                }
            ));
            $fieldValue = new $class(new $modelC, $field);
            $me->test('setOriginal, default', $fieldValue->setOriginal());
            $me->test('setOriginal, set value', $fieldValue->setOriginal('g:R'));
        });

        $me->test('getOriginal', method_exists($class, 'getOriginal'), function() use($me, $class, $modelC, $fieldC)
        {
            $var = (string) rand();
            $field = new $fieldC(array(
                'name'=>'field', 
                'converter'=>function($value){
                    return 'c:'.$value;
                },
                'renderer'=>function($value){
                    return 'r:'.$value;
                },
                'reader'=>function($value){
                    return 'g:'.$value;
                },
                'writer'=>function($value){
                    return 'w:'.$value;
                }
            ));
            $fieldValue = new $class(new $modelC, $field);
            $me->test('getOriginal, default', $fieldValue->getOriginal());
        });

        $me->test('setRaw', method_exists($class, 'setRaw'), function() use($me, $class, $modelC, $fieldC)
        {
            $var = (string) rand();
            $field = new $fieldC(array(
                'name'=>'field', 
                'converter'=>function($value){
                    return 'c:'.$value;
                },
                'renderer'=>function($value){
                    return 'r:'.$value;
                },
                'reader'=>function($value){
                    return 'g:'.$value;
                },
                'writer'=>function($value){
                    return 'w:'.$value;
                }
            ));
            $fieldValue = new $class(new $modelC, $field);
            $me->test('setRaw, default', $fieldValue->setRaw('T:'));
        });

        $me->test('setValue', method_exists($class, 'setValue'), function() use($me, $class, $modelC, $fieldC)
        {
            $var = (string) rand();
            $field = new $fieldC(array(
                'name'=>'field', 
                'converter'=>function($value){
                    return 'c:'.$value;
                },
                'renderer'=>function($value){
                    return 'r:'.$value;
                },
                'reader'=>function($value){
                    return 'g:'.$value;
                },
                'writer'=>function($value){
                    return 'w:'.$value;
                }
            ));
            $fieldValue = new $class(new $modelC, $field);
            $me->test('setValue, default', $fieldValue->setValue('T:'));
        });

        $me->test('getRaw', method_exists($class, 'getRaw'), function() use($me, $class, $modelC, $fieldC)
        {
            $var = (string) rand();
            $field = new $fieldC(array(
                'name'=>'field', 
                'converter'=>function($value){
                    return 'c:'.$value;
                },
                'renderer'=>function($value){
                    return 'r:'.$value;
                },
                'reader'=>function($value){
                    return 'g:'.$value;
                },
                'writer'=>function($value){
                    return 'w:'.$value;
                }
            ));
            $fieldValue = new $class(new $modelC, $field);
            $me->test('getRaw, default', $fieldValue->getRaw());
        });

        $me->test('getDisplay', method_exists($class, 'getDisplay'), function() use($me, $class, $modelC, $fieldC)
        {
            $var = (string) rand();
            $field = new $fieldC(array(
                'name'=>'field',
                'display'=>'Hobby'
            ));
            $me->test('getDisplay, default', $field->getDisplay());
        });

        $me->test('getValue', method_exists($class, 'getValue'), function() use($me, $class, $modelC, $fieldC)
        {
            $var = (string) rand();
            $field = new $fieldC(array(
                'name'=>'field',
                'display'=>'Hobby',
                'converter'=>function($value){
                    return 'c:'.$value;
                },
                'renderer'=>function($value){
                    return 'r:'.$value;
                },
                'reader'=>function($value){
                    return 'g:'.$value;
                },
                'writer'=>function($value){
                    return 'w:'.$value;
                }
            ));
            $fieldValue = new $class(new $modelC, $field);
            $me->test('getValue, default', $fieldValue->getValue());
        });

        $me->test('getError', method_exists($class, 'getError'), function() use($me, $class, $modelC, $fieldC)
        {
            $var = (string) rand();
            $field = new $fieldC(array(
                'name'=>'field',
                'display'=>'Hobby',
                'validation' => array('nullable'=>false),
                'error' => array('Error Message')
            ));
            $fieldValue = new $class(new $modelC, $field);
            $fieldValue->validate();
            $me->test('getError, message', !empty($fieldValue->getError()));
        });

        $me->test('isValid', method_exists($class, 'isValid'), function() use($me, $class, $modelC, $fieldC)
        {
            $var = (string) rand();
            $field = new $fieldC(array(
                'name'=>'field',
                'display'=>'Hobby',
                'validation' => array('nullable'=>false),
                'error' => array('Error Message')
            ));
            $fieldValue = new $class(new $modelC, $field);
            $fieldValue->validate();
            $me->test('isValid, valid', $fieldValue->isValid() == false);
        });

        $me->test('rollback', method_exists($class, 'rollback'), function() use($me, $class, $modelC, $fieldC)
        {
            $var = (string) rand();
            $field = new $fieldC(array(
                'name'=>'field', 
                'converter'=>function($value){
                    return 'c:'.$value;
                },
                'renderer'=>function($value){
                    return 'r:'.$value;
                },
                'reader'=>function($value){
                    return 'g:'.$value;
                },
                'writer'=>function($value){
                    return 'w:'.$value;
                }
            ));
            $fieldValue = new $class(new $modelC, $field);

            $fieldValue->rollback();
            $me->test('rollback, after rollback, modified', $fieldValue->isModified() == false);
            $me->test('rollback, after rollback, value', $fieldValue->getOriginal() == $fieldValue->getRaw());
        });

        $this->createUnfinishedTask();
    }
}