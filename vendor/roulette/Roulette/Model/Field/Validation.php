<?php
/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Roulette\Model\Field;

use Roulette\Validation as BaseValidation;
use Roulette\Model\Field\Field;

use Roulette\Mixin\HasField;

/**
 * Cache management for model instance to increase speed of load data 
 *
 * @package \Roulette\Model
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Validation extends BaseValidation
{
    /**
     * The field.
     * 
     * @var string
     */
    protected $field = null;

    function __construct(Field $field, $config = null)
    {
        parent::__construct($config);

        $this->setField($field);
    }

    function getField()
    {
        return $this->field;
    }

    function setField(Field $field)
    {
        $this->field = $field;
        return $this;   
    }

    /**
     * Validate value using its validators
     * 
     * @param  Array $value
     * @return Array
     */
    function validate($value = null)
    {
        $validators = $this->getValidators();
        $isValid = true;
        $validationMessages = array();
        $fieldName = $this->getField()->getName();

        foreach ( $validators as $validator)
        {
            if ( $validator->test($value) !== true )
            {
                $isValid = false;

                # here is the override
                $message = $validator->getMessage(array(
                    'value'=>$value,
                    'field'=>$fieldName
                ));

                $validationMessages[] = $message;
            }
        }

        return $validationMessages;
    }

}