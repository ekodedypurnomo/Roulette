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
    protected ?Field $field = null;

    function __construct(Field $field, mixed $config = null)
    {
        parent::__construct($config);

        $this->setField($field);
    }

    function getField(): ?Field
    {
        return $this->field;
    }

    function setField(Field $field): static
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
    function validate(mixed $value = null): array
    {
        $validators = $this->getValidators();
        $validationMessages = [];
        $fieldName = $this->getField()->getName();

        foreach ($validators as $validator)
        {
            if ($validator->test($value) !== true)
            {
                # here is the override
                $message = $validator->getMessage([
                    'value' => $value,
                    'field' => $fieldName
                ]);

                $validationMessages[] = $message;
            }
        }

        return $validationMessages;
    }
}
