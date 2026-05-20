<?php
/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Roulette\Data;

use Roulette\Base;
use Roulette\Model\Field\Field;
use Roulette\Model;

/**
 * \Rouletet\data\Value represent a single value of the field, 
 * its not so simply for a value, but it has several purpose of a field value.
 * There are two options for set the value:
 * - setOriginal:
 *     Apply for original value, means value is real value from database,
 *     value (will `default` value if is `null`) will be processed by `read`.
 * - setValue:
 *     Apply for user value, means value are set by user/program and may be different from real value in database,
 *     for example user load a record and in field `gender` has a value `male`, 
 *     then user set a new value for it as `female`,
 *     so value in setValue will be different with setOriginal because use doesnt save the record yet.
 *
 * Value lifecycle      
 *             
 *      +--<--{reader()}--<--{default()}--<-----------------{<=} ++++++++++++
 *      |                                                        | DATABASE |
 *      +-->--{writer()}-->---------------------------------{=>} ++++++++++++
 *      |
 *      |
 *      +-->-----[original]-->------------------------------{=>} isModified()
 *      |            |
 *      |       +-->--+-->--+
 *      |       |           |
 *      |   {commit}    {revert}
 *      |       |           |
 *      |       +--<--+--<--+
 *      |            |     
 *      +--<->---[raw]-->--{renderer()}-->--[display()]-->--{=>} get()
 *                   |
 *                   +--<--{validation}--<--{converter}--<--{<=} set()
 *
 *      Points:
 *      - Read data from database
 *          1. If value from database are not defined (or column inexist) then `default` value will be applied 
 *          2. Value will be processed by `reader`
 *          3. Then Value saved into `raw`
 *          4. Then Value copied into `original` within `commit` is true (mean value is valid from database)
 *          5. `Render` will be called and the result saved into `display`
 *      - Getter
 *          1. By default will return the `display` value, or `raw` instead if `render=false`
 *      - Setter
 *          1. Passed value will be converted by the `converter`
 *          2. Then (converted) value value goes into `validation`
 *          3. Then (converted) value will be saved into raw, though the value is valid or not
 *      - Any changes
 *          1. Modified status is equality of raw (set by user/program) and original (value based on database)
 *          2. `Commit` will force original value to be equal with raw, so will affect modified is false
 *
 * @package \Roulette\Model
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Value extends Base
{

    /**
     * Will display on the table where the field was taken
     * @var \Roulette\Model
     */
    protected $record = null;

    /**
     * Will display the details of this field
     * @var \Roulette\Model\Field
     */
    protected $field = null;
    
    /**
     * Temporary storage prior to DB, the value is the same as the original
     * @var String
     */
    protected $raw = null;
    
    /**
     * Featuring the original value of the DB
     * @var String
     */
    protected $original = null;
    
    /**
     * Will show a record of the field
     * @var String
     */
    protected $display = null;

    /**
     * Valid will check whether the field is true
     * @var boolean
     */
    protected $valid = true;

    /**
     * Will display the error message for the field value
     * @var Array
     */
    protected $error = null;
    
    /**
     * default config into field/Value
     * 
     * @param Model $record
     * @param Field $field 
     * @param array $value
     * @param boolean $isOriginal
     */
    function __construct(Model $record, Field $field, $value = null, $isOriginal = true)
    {
    	$this->field = $field;
    	$this->record = $record;

        if ($isOriginal)
        {
            $this->setOriginal($value)->revert(); // need apply into raw
        }
        else
        {
            $this->setValue($value);
        }

    	return $this;
    }

    function __toString()
    {
        return $this->getValue();
    }

    /**
     * Take the field
     * @return array
     */
    function getField()
    {
    	return $this->field;
    }

    /**
     * Take the Record
     * @return Object
     */
    function getRecord()
    {
    	return $this->record;
    }

    /**
     * Take a Record from the fields by value on params
     * 
     * @param  array  $value
     * @param  boolean $useDefault
     * @return field/Value $value
     */
    protected function getReadValue($value = null, $useDefault = true)
    {
        $field = $this->getField();
        $record = $this->getRecord();

        // help apply default, ifonly useDefault is true and value is null
        if ($useDefault and is_null($value))
        {
            $value = $field->getDefault();
        }
     
        // apply `reader`
        $value = $field->read($value, $record); // include `record` in parameter
        return $value;
    }

    /**
     * Take a record by writer on DB
     * @return field/Value $raw
     */
    function getWriteValue()
    {
        $raw = $this->raw;
        $field = $this->getField();
        $record = $this->getRecord();

        // apply `writer` and pass $record into param
        $raw = $field->write($raw, $record);
        return $raw;
    }

    /**
     * Shortcut to set the real value from database.
     * 
     * @param array  $value     
     * @param boolean $revert    
     * @param boolean $read      
     * @param boolean $useDefault
     */
    function setOriginal($value = null, $revert = false, $read = true, $useDefault = true)
    {
        $field = $this->getField();

        // apply `reader`
        if ($read)
        {
            $value = $this->getReadValue($value, $useDefault); // include `record` in parameter
        }
        else
        {
            if ($useDefault and is_null($value) )
            {
                $value = $field->getDefault();
            }
        }

        $this->original = $value;

        // revert raw into original if needed
        if ($revert) $this->revert();

        return $this;
    }

    /**
     * Taking the original value of the DB, before update
     * @return String
     */
    function getOriginal()
    {
        return $this->original;
    }
    
    /**
     * Set a value to a Raw in field 
     * 
     * @param array  $value
     * @param boolean $commit
     * @param boolean $convert
     */
    function setRaw($value = null, $commit = false, $convert = true)
    {
    	$field = $this->getField();

        // convert for: any value thought it from database or manual set will be converter 
    	if ($convert)
    	{
    		$value = $field->convert($value, $this->getRecord());
    	}

        // apply real value to raw
        $this->raw = $value;
        
        // remove from modified status
        if ($commit) $this->commit();
        
        // set the rendered or display value
        $this->render();
        
        return $this;
    }

    /**
     * Shortcut to setRaw, can use this function
     */
    function setValue()
    {
        return call_user_func_array(array($this, 'setRaw'), func_get_args());
    }

    /**
     * Taking the value of this field
     * 
     * @param  string $section default section is display, you can choose the section ['display', 'raw', original]
     * @return Array
     */
    function getRaw()
    {
        return $this->raw;
    }

    /**
     * Take a Record from field
     * @return String
     */
    function getDisplay()
    {
        return $this->display;
    }

    /**
     * Taking the value of the field, specified by the parameter, if true for display or false for raw data
     * @param  [type] $render [description]
     * @return array
     */
    function getValue($render = true)
    {
        return ($render) ? $this->getDisplay() : $this->getRaw();
    }
    
    /**
     * Ascertain whether the field has been changed
     * @return boolean
     */
    function isModified()
    {
    	return $this->original != $this->raw;
    }

    /**
     * Retrieve messages from an application error
     * @return boolean
     */
    function getError()
    {
    	if (!is_array($this->error))
        {
            $this->error = array();
        }
    	return $this->error;
    }

    /**
     * Ascertain whether the field is passed the validation test
     * @param  boolean $validate
     * @return boolean
     */
    function isValid($validate = false)
    {
        if ($validate) $this->validate();

        return $this->valid;
    }

    /**
     * Provide validation of the data entered and check whether the same original data
     * @return Array
     */
    function validate()
    {
    	$value = $this->raw;
        $record = $this->getRecord();
        $field = $this->getField();

    	$this->error = $field->validate($value, $record);
    	$this->valid = empty($this->error); // validation is valid if has no error
    	
        return $this;
    }

    /**
     * Choosing the new data from the old data and insert it into DB
     * @return String
     */
    function commit()
    {
        $this->original = $this->raw;
        return $this;
    }

    /**
     * Returns the value of the temporary storage to its original value
     * @return String
     */
    function revert()
    {
     	$this->raw = $this->original;
    	$this->render(); // re render
    	return $this;
    }

    /**
     * [rollback description]
     * @return \Roulette\Data\Value
     */
    function rollback()
    {
        return call_user_func_array(array($this, 'revert'), func_get_args());
    }

    /**
     * Convert the data from the DB, so that is displayed is the manipulation of data from the server
     * @return Array
     */
    function render()
    {
    	$field = $this->getField();
        $record = $this->getRecord();
    	$raw = $this->raw;

    	$this->display = $field->render($raw, $record);

    	return $this;
    }
}