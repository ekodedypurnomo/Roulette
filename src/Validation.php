<?php

declare(strict_types=1);

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
 * Validator registry and factory. Resolves string validator names to ValidatorAbstract instances.
 *
 * The built-in registry maps short names (e.g. `'email'`, `'minlength'`) to their
 * concrete Validator classes. Field declarations reference these names; the framework
 * resolves them here at validation time.
 *
 * Add a custom validator type globally:
 *   Validation::addValidator('phone', App\Validator\Phone::class);
 *
 * Or attach a one-off validator to a specific field:
 *   $field->addValidator('custom', function($value) { return strlen($value) > 3; });
 *
 * Built-in validator names: above, below, boolean, custom, date, datetime, double,
 * email, exclusion, float, format, inclusion, integer, isfalse, istrue, maxlength,
 * maxvalue, minlength, minvalue, notblank, nullable, numeric, string, time, unique,
 * url, uuid.
 *
 * @package \Roulette
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Validation extends Base
{
    // use util\Observable;
    use Configurable;

    static protected array $builtinValidators = [
        'above'     => \Roulette\Validator\Above::class,
        'below'     => \Roulette\Validator\Below::class,
        'boolean'   => \Roulette\Validator\Boolean::class,
        'custom'    => \Roulette\Validator\Custom::class,
        'datetime'  => \Roulette\Validator\DateTime::class,
        'date'      => \Roulette\Validator\Date::class,
        'double'    => \Roulette\Validator\Double::class,
        'email'     => \Roulette\Validator\Email::class,
        'exclusion' => \Roulette\Validator\Exclusion::class,
        'float'     => \Roulette\Validator\FloatType::class,
        'format'    => \Roulette\Validator\Format::class,
        'inclusion' => \Roulette\Validator\Inclusion::class,
        'integer'   => \Roulette\Validator\Integer::class,
        'isfalse'   => \Roulette\Validator\IsFalse::class,
        'istrue'    => \Roulette\Validator\IsTrue::class,
        'maxlength' => \Roulette\Validator\Maxlength::class,
        'maxvalue'  => \Roulette\Validator\Maxvalue::class,
        'minlength' => \Roulette\Validator\Minlength::class,
        'minvalue'  => \Roulette\Validator\Minvalue::class,
        'notblank'  => \Roulette\Validator\NotBlank::class,
        'nullable'  => \Roulette\Validator\Nullable::class,
        'numeric'   => \Roulette\Validator\Numeric::class,
        'string'    => \Roulette\Validator\StringType::class,
        'time'      => \Roulette\Validator\Time::class,
        'unique'    => \Roulette\Validator\Unique::class,
        'url'       => \Roulette\Validator\Url::class,
        'uuid'      => \Roulette\Validator\Uuid::class,
    ];

    /**
     * Array of validators
     * @var array
     */
    protected array $validators = [];

    /**
     * Default value for validator message
     * @var array
     */
    protected array $messageTemplates = [];

    /**
     * @ignore
     */
    function __construct(mixed $config = null)
    {
        if (is_callable($config))
        {
            $config = ['validators' => ['custom' => $config]];
        }

        $this->configure($config);

        // backup affected from config
        // then purge any artifact affected from configure
        $validators = is_array($this->validators) ? $this->validators : [];
        $this->validators = [];

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
    }

    /**
     * Get all validator
     * @return array
     */
    function getValidators(): array
    {
        if (!is_array($this->validators))
        {
            $this->validators = [];
        }
        return $this->validators;
    }

    /**
     * Add new validator
     *
     * @param mixed $validator
     * @param mixed $rule
     * @param string|null $message
     * @return static
     */
    function addValidator(mixed $validator = null, mixed $rule = null, ?string $message = null): static
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

        $validator = strtolower((string) $validator);

        if (!array_key_exists($validator, static::$builtinValidators)) return $this;

        $validatorClass = static::$builtinValidators[$validator];

        if (array_key_exists($validator, $this->messageTemplates)) $message = $this->messageTemplates[$validator];

        $this->validators[] = new $validatorClass($rule, $message);

        return $this;
    }

    /**
     * Reset validators and message template
     * @return static
     */
    function reset(): static
    {
        $this->validators = [];
        $this->messageTemplates = [];
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
     * @param string|null $key
     * @return array|string|null
     */
    function getMessageTemplates(?string $key = null): array|string|null
    {
        if (is_null($key))
        {
            return $this->messageTemplates;
        }

        if (array_key_exists($key, $this->messageTemplates))
        {
            return $this->messageTemplates[$key];
        }

        return null;
    }

    /**
     * Validate value using its validators.
     * This function is overrided by \Roulette\Model\Field\Validation::validate for compitibilty
     *
     * @param  mixed $value
     * @return array
     */
    function validate(mixed $value = null): array
    {
        $validators = $this->getValidators();
        $validationMessages = [];

        foreach ($validators as $validator)
        {
            if ($validator->test($value) !== true)
            {
                $validationMessages[] = $validator->getMessage(['value' => $value]);
            }
        }

        return $validationMessages;
    }
}
