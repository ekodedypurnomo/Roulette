<?php
/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Roulette;

use Roulette\Base;
use Roulette\Validator\ValidatorAbstract;

use Roulette\Mixin\Configurable;

/**
 * Validation is the appearance of the message or perform other functions after action
 * 
 * @package \Roulette
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Validation extends Base
{
    // use util\Observable;
    use Configurable;
    
    static protected $builtinValidators = array
    (
        'above'     => \Roulette\Validator\Above::class,
        'below'     => \Roulette\Validator\Below::class,
        'blank'     => \Roulette\Validator\Blank::class,
        'boolean'   => \Roulette\Validator\Boolean::class,
        'custom'    => \Roulette\Validator\Custom::class,
        'datetime'  => \Roulette\Validator\DateTime::class,
        'date'      => \Roulette\Validator\Date::class,
        'double'    => \Roulette\Validator\Double::class,
        'email'     => \Roulette\Validator\Email::class,
        'exclusion' => \Roulette\Validator\Exclusion::class,
        'float'     => \Roulette\Validator\Float::class,
        'format'    => \Roulette\Validator\Format::class,
        'inclusion' => \Roulette\Validator\Inclusion::class,
        'integer'   => \Roulette\Validator\Integer::class,
        'isfalse'   => \Roulette\Validator\IsFalse::class,
        'istrue'    => \Roulette\Validator\IsTrue::class,
        'locale'    => \Roulette\Validator\Locale::class,
        'maxlength' => \Roulette\Validator\Maxlength::class,
        'maxvalue'  => \Roulette\Validator\Maxvalue::class,
        'minlength' => \Roulette\Validator\Minlength::class,
        'minvalue'  => \Roulette\Validator\Minvalue::class,
        'notblank'  => \Roulette\Validator\Notblank::class,
        'nullable'  => \Roulette\Validator\Nullable::class,
        'numeric'   => \Roulette\Validator\Numeric::class,
        'string'    => \Roulette\Validator\String::class,
        'time'      => \Roulette\Validator\Time::class,
        'url'       => \Roulette\Validator\Url::class,
        'uuid'      => \Roulette\Validator\Uuid::class
    );

    /**
     * Array of validators
     * @var array
     */
    protected $validators = array();
    
    /**
     * Default value for validator message
     * @var array
     */
    protected $messageTemplates = array();

    /**
     *@ignore
     */
    function __construct($config = null)
    {
        if ( is_callable($config) )
        {
            $config = array('validators'=>array(
                'custom'=>$config
            ));
        }
        
        $this->configure($config);

        // backup affected from config
        // then purge any artifact affected from configure
        $validators = is_array($this->validators) ? $this->validators : array();
        $this->validators = array();
        
        // now append each
        foreach ($validators as $validator => $rule)
        {
            if ($rule instanceof ValidatorAbstract)
            {
                $this->addValidator($rule);
            }
            else
            {
                $this->addValidator($validator, $rule);
            }
        }
        
        return $this;
    }

    /**
     * Get all validator
     * @return Array
     */
    function getValidators()
    {
        if (!is_array($this->validators))
        {
            $this->validators = array();
        }
        return $this->validators;
    }

    /**
     * Add new validator
     * 
     * @param Array  $validator
     * @param Array  $rule
     * @param String $message
     */
    function addValidator($validator = null, $rule = null, $message = null)
    {
        # by default accept the instance of Validator
        if ($validator instanceof ValidatorAbstract)
        {
            $this->validators[] = $validator;
            return $this;
        }

        # otherwise will create by builtin validators
        # accept only from builtin validators
        if (is_callable($validator))
        {
            $message = $rule;
            $rule = $validator;
            $validator = 'custom';
        }

        $validator = strtolower($validator);
        
        if ( ! array_key_exists($validator, static::$builtinValidators)) return $this;
        
        $validatorClass = static::$builtinValidators[$validator];

        if (array_key_exists($validator, $this->messageTemplates)) $message = $this->messageTemplates[$validator];

        $this->validators[] = new $validatorClass($rule, $message);

        return $this;
    }

    /**
     * Reset validors and message template
     * @return array
     */
    function reset()
    {
        $this->validators = array();
        $this->messageTemplates = array();
        return $this;
    }

    /**
     * Take specified validator message
     * 
     *     Example :
     *     $message = array(
     *         'validators'=>array(),
     *         'messageTemplates'=>array(
     *              'null'=>'dont null please',
     *              'maxvalue'=>'please input below {rule}'
     *         )
     *     );
     *
     *     $msg = \Roulette\Validation::getValidatorMessage('null') == 'dont null please';
     *     
     * @return Array
     */
    function getMessageTemplates()
    {
        $args = func_get_args();
        
        # return all template if no filter
        if (empty($args))
        {
            return $this->messageTemplates;
        }
        # return selected template
        else
        {
            $key = $args[0];
            if (array_key_exists($key, $this->messageTemplates))
            {
                return $this->messageTemplates[$key];
            }
        }
    }

    /**
     * Validate value using its validators.
     * This function is overrided by \Roulette\Model\Field\Validation::validate for compitibilty
     * 
     * @param  Array $value
     * @return Array
     */
    function validate($value = null)
    {
        $validators = $this->getValidators();
        $isValid = true;
        $validationMessages = array();

        foreach ( $validators as $validator)
        {
            if ( $validator->test($value) !== true )
            {
                $isValid = false;
                $validationMessages[] = $validator->getMessage(array( // pass data into template
                    'value'=>$value
                ));
            }
        }

        return $validationMessages;
    }

}