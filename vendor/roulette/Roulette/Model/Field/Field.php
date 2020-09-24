<?php
/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Roulette\Model\Field Field is part of the model, which is used to declare a field of that model
 */
namespace Roulette\Model\Field;

use Roulette\Base;
use Roulette\Collection;
use Roulette\Model\Field\Validation as FieldValidation;
use Roulette\Data\Permission;

use Roulette\Mixin\Configurable;
use Roulette\Mixin\HasModel;

/**
 *  Field is part of the model, which is used to declare a field of that model
 *  
 * @package Roulette\Model
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Field extends Base
{
    use Configurable {
        setConfig as public;
        getConfig as public;
    }
    use HasModel;

    /**
     * The name of field by which for references.
     * 
     * @var string
     */
    protected $name = null;

    /**
     * Name of field from database to access.
     * 
     * @var array
     */
    protected $source = null;

    /**
     * String to output for message and another user text purpose.
     * 
     * @var String
     */
    protected $display = null;

    /**
     * Accessibility for getData in model, private is `true` will be ignored for append on it.
     * 
     * @var boolean
     */
    protected $private = false;

    /**
     * Readonly is `true` will be effect on field as readonly
     * 
     * @var boolean
     */
    protected $readOnly = false;

    /**
     * Default value for first initializing data in the Model.
     * 
     * @var String
     */
    protected $default = null;

    /**
     * writer will enter the field to DB
     * 
     * @var array
     */
    protected $writer = null;

    /**
     * reader will read Field from DB field
     * 
     * @var array
     */
    protected $reader = null;

    /**
     * Default value converter is null, can be filled with an array
     * 
     * @var array
     */
    protected $converter = null;

    /**
     * Default value renderer is null. renderer can be use for field ex: gender->render to Male or boolean
     * 
     * @var null
     */
    protected $renderer = null;

    /**
     * Field will be vatidate from DB is same on record
     * 
     * @var null
     */
    protected $validation = null;

    protected $operation = 'f';

    protected $unique = false;

    protected $uniqueValidator = null;

    /**
     * __construct for function creates a new object field.
     * @param object|string|array $config field configuration
     */
    function __construct($config = null)
    {
        if (is_string($config)) $config = array('name'=>$config);

        $configs = Collection::create($config);

        # set default value
        $configs->setIfNot(array(
            'source' => $configs->get('name'),
            'display'=> $configs->get('name')
        ));

        $this->configure($configs->getAll(), array(
            'except'=>array('permission','operation','select','insert','update','delete') // need to config it manualy later
        ));

        # configure validation
        $validation = $configs->get('validation');
        if (! ($validation instanceof FieldValidation) ) 
        {
            $this->validation = new FieldValidation($this, array(
                'validators' => $validation
            ));
        }

        # configure operation
        $opPerm = $this->getOperation();
        if ($configs->has('permission')) $this->setOperation($configs->get('permission'));
        if ($configs->has('operation')) $this->setOperation($configs->get('operation'));
        if ($configs->has('select')) $this->setSelectable($configs->get('select'));
        if ($configs->has('insert')) $this->setInsertable($configs->get('insert'));
        if ($configs->has('update')) $this->setUpdatable($configs->get('update'));
        if ($configs->has('delete')) $this->setDeletable($configs->get('delete'));

        return $this;
    }

    /**
     * Method allows a class to decide how it will react when it is treated like a string.
     * Converting objects without __toString() method to string would cause E_RECOVERABLE_ERROR
     * 
     * @return string [any string on name]
     */
    function __toString()
    {
        return $this->name;
    }

    /**
     * Take specified Name from field
     * 
     * @return String just take the data string
     */
    function getName()
    {
        return $this->name;
    }

    function setName($name = null, $applyToSource = false, $applyToDisplay = false)
    {
        $this->name = $name;

        if($applyToSource) $this->setSource($name);
        if($applyToDisplay) $this->setDisplay($name);

        return $this;
    }

    /**
     * Take specified Source from Field
     * 
     * @return String     
     */
    function getSource()
    {
        return $this->source;
    }

    function setSource($source = null)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * Take specified Display from Field
     * 
     * @return String
     */
    function getDisplay()
    {
        return $this->display;
    }

    function setDisplay($display = null)
    {
        $this->display = $display;
        return $this;
    }

    /**
     * Take specified Default value from Field
     * 
     * @return String
     */
    function getDefault()
    {
        return $this->default;
    }

    function setDefault($default = null)
    {
        $this->default = $default;
        return $this;
    }

    /**
     * Field can only view if field isReadOnly is true
     * 
     * @return boolean [true / false]
     */
    function isReadOnly()
    {
        return (bool) $this->readOnly;
    }

    function setToReadOnly()
    {
        $args = func_get_args();
        
        $value = count($args) ? !!$args[0] : true;

        $this->public = $value;
        return $this;
    }

    /**
     * See what the field is Private or not
     * 
     * @return boolean
     */
    function isPrivate()
    {
        return (bool) $this->private;
    }

    function setToPrivate()
    {
        $args = func_get_args();
        
        $value = count($args) ? !!$args[0] : true;

        $this->private = $value;
        return $this;
    }

    /**
     * See what the field is Public or not
     * 
     * @return boolean
     */
    function isPublic()
    {
        return !$this->isPrivate();
    }

    function setToPublic()
    {
        $args = func_get_args();
        
        $value = count($args) ? !!$args[0] : true;

        $this->public = $value;
        return $this;
    }

    function getOperation()
    {
        if( !($this->operation instanceof Permission))
        {
            $this->operation = new Permission($this->operation);
        }
        return $this->operation;
    }

    function setOperation($operation = null)
    {
        # parse operation config
        $siud = array();

        // in case array: array('insert','select') or array('insert'=>false, 'select'=>true) etc
        if (is_array($operation))
        {   
            $acceptableOperation = array('select'=>'s','insert'=>'i','update'=>'u','delete'=>'d');
            foreach ($operation as $key => $value)
            {
                if (is_bool($value) and $value == true)
                {
                    if (array_key_exists($key, $acceptableOperation))
                    {
                        $siud[] = $acceptableOperation[$key];
                    }
                }
                else if (is_string($value))
                {
                    if (array_key_exists($key, $acceptableOperation))
                    {
                        $siud[] = $acceptableOperation[$value];
                    }
                }
            }
        }
        // in case string: "siud","sid","sd" etc
        else if (is_string($operation))
        {
            $siud = array_unique(str_split(strtolower($operation)));
        }
        # only allow for s,i,u,d keys
        $this->getOperation()->setPermission(array(
            in_array('s', $siud),
            in_array('i', $siud),
            in_array('u', $siud),
            in_array('d', $siud)
        ));
        return $this;
    }

    /**
     * View the status field, can be select or not
     * 
     * @return Boolean
     */
    function isSelectable()
    {
        return $this->getOperation()->getSelectPermission();
    }

    function setSelectable($value = true)
    {
        $this->getOperation()->setSelectPermission($value);
        return $this;
    }

    /**
     * View the status field, can be insert or not
     * 
     * @return Boolean
     */
    function isInsertable()
    {
        return $this->getOperation()->getInsertPermission();
    }

    function setInsertable($value = true)
    {
        $this->getOperation()->setInsertPermission($value);
        return $this;
    }

    /**
     * View the status field, can be update or not
     * 
     * @return Boolean
     */
    function isUpdatable()
    {
        return $this->getOperation()->getUpdatePermission();
    }

    function setUpdatable($value = true)
    {
        $this->getOperation()->setUpdatePermission($value);
        return $this;
    }

    /**
     * View the status field, can be delete or not
     * 
     * @return Boolean
     */
    function isDeletable()
    {
        return $this->getOperation()->getDeletePermission();
    }

    function setDeletable($value = true)
    {
        $this->getOperation()->setDeletePermission($value);
        return $this;
    }

    /**
     * Use Renderer for specified fields
     * 
     * @return Array
     */
    function isUseRenderer()
    {
        return !empty($this->renderer);
    }

    /**
     * Use Converter for specified fields
     * 
     * @return Array
     */
    function isUseConverter()
    {
        return !empty($this->converter);
    }

    /**
     * Use Reader for specified fields
     * 
     * @return Array
     */
    function isUseReader()
    {
        return !empty($this->reader);
    }

    /**
     * Use Writer for specified fields
     * 
     * @return Array
     */
    function isUseWriter()
    {
        return !empty($this->writer);
    }

    /**
     * Use FieldValidation for specified fields
     * 
     * @return Array
     */
    function isUseValidation()
    {
        return !empty($this->validation);
    }

    /**
     * Read this field by params value
     * 
     * @param  String $value value of this field
     * @return Object return a value
     */
    function read($value = null)
    {
        if ($this->isUseReader() and is_callable($this->reader)) {
            $value = call_user_func_array($this->reader, func_get_args());
        }
        return $value;
    }

    /**
     * write description
     * 
     * @param  String $value
     * @return Object return a value
     */
    function write($value = null)
    {
        if ($this->isUseWriter() and is_callable($this->writer)) {
            $value = call_user_func_array($this->writer, func_get_args());
        }
        return $value;
    }

    /**
     * Convert fields to be displayed
     * Example : 
     *      'convert'=>function($value){
     *          return htmlspecialchars_decode($value);
     *      }
     *      
     * @param  array $value 
     * @return array
     */
    function convert($value = null)
    {
        if ($this->isUseConverter() and is_callable($this->converter)) {
            $value = call_user_func_array($this->converter, func_get_args());
        }
        return $value;
    }

    /**
     * Render will reverse field from its original value
     * 
     * @param  array $value
     * @return array
     */
    function render($value = null)
    {
        if ($this->isUseRenderer() and is_callable($this->renderer)) {
            $value = call_user_func_array($this->renderer, func_get_args());
        }
        return $value;
    }

    /**
     * Validate fields that will be processed
     * 
     * @param  array $value
     * @return array
     */
    function validate($value = null)
    {
        if ($this->isUseValidation() and ($this->validation instanceof FieldValidation))
        {
            // validation is valid if return an empty value
            return call_user_func_array(array($this->getValidation(), 'validate'), func_get_args());
        }
    }

    /**
     * Take the validation to be processed
     * 
     * @return Array
     */
    function getValidation()
    {
        if (!($this->validation instanceof FieldValidation))
        {
            $this->validation = new FieldValidation();
        }

        return $this->validation;
    }
}