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
    protected ?Model $record = null;

    /**
     * Will display the details of this field
     * @var \Roulette\Model\Field\Field
     */
    protected ?Field $field = null;

    /**
     * Temporary storage prior to DB, the value is the same as the original
     * @var mixed
     */
    protected mixed $raw = null;

    /**
     * Featuring the original value of the DB
     * @var mixed
     */
    protected mixed $original = null;

    /**
     * Will show a record of the field
     * @var mixed
     */
    protected mixed $display = null;

    /**
     * Valid will check whether the field is true
     * @var bool
     */
    protected bool $valid = true;

    /**
     * Will display the error message for the field value
     * @var array|null
     */
    protected ?array $error = null;

    /**
     * default config into field/Value
     *
     * @param Model $record
     * @param Field $field
     * @param mixed $value
     * @param bool $isOriginal
     */
    function __construct(Model $record, Field $field, mixed $value = null, bool $isOriginal = true)
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
    }

    function __toString(): string
    {
        return (string) $this->getValue();
    }

    /**
     * Take the field
     * @return \Roulette\Model\Field\Field|null
     */
    function getField(): ?Field
    {
    	return $this->field;
    }

    /**
     * Take the Record
     * @return \Roulette\Model|null
     */
    function getRecord(): ?Model
    {
    	return $this->record;
    }

    /**
     * Take a Record from the fields by value on params
     *
     * @param  mixed $value
     * @param  bool $useDefault
     * @return mixed
     */
    protected function getReadValue(mixed $value = null, bool $useDefault = true): mixed
    {
        $field = $this->getField();
        $record = $this->getRecord();

        // help apply default, if only useDefault is true and value is null
        if ($useDefault && is_null($value))
        {
            $value = $field->getDefault();
        }

        // apply `reader`
        $value = $field->read($value, $record); // include `record` in parameter
        return $value;
    }

    /**
     * Take a record by writer on DB
     * @return mixed
     */
    function getWriteValue(): mixed
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
     * @param mixed $value
     * @param bool $revert
     * @param bool $read
     * @param bool $useDefault
     * @return static
     */
    function setOriginal(mixed $value = null, bool $revert = false, bool $read = true, bool $useDefault = true): static
    {
        $field = $this->getField();

        // apply `reader`
        if ($read)
        {
            $value = $this->getReadValue($value, $useDefault); // include `record` in parameter
        }
        else
        {
            if ($useDefault && is_null($value))
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
     * @return mixed
     */
    function getOriginal(): mixed
    {
        return $this->original;
    }

    /**
     * Set a value to a Raw in field
     *
     * @param mixed $value
     * @param bool $commit
     * @param bool $convert
     * @return static
     */
    function setRaw(mixed $value = null, bool $commit = false, bool $convert = true): static
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
     * @param mixed $value
     * @param bool $commit
     * @param bool $convert
     * @return static
     */
    function setValue(mixed $value = null, bool $commit = false, bool $convert = true): static
    {
        return $this->setRaw($value, $commit, $convert);
    }

    /**
     * Taking the value of this field
     *
     * @param  string $section default section is display, you can choose the section ['display', 'raw', original]
     * @return mixed
     */
    function getRaw(): mixed
    {
        return $this->raw;
    }

    /**
     * Take a Record from field
     * @return mixed
     */
    function getDisplay(): mixed
    {
        return $this->display;
    }

    /**
     * Taking the value of the field, specified by the parameter, if true for display or false for raw data
     * @param  bool $render
     * @return mixed
     */
    function getValue(bool $render = true): mixed
    {
        return ($render) ? $this->getDisplay() : $this->getRaw();
    }

    /**
     * Ascertain whether the field has been changed
     * @return bool
     */
    function isModified(): bool
    {
    	return $this->original != $this->raw;
    }

    /**
     * Retrieve messages from an application error
     * @return array
     */
    function getError(): array
    {
    	if (!is_array($this->error))
        {
            $this->error = [];
        }
    	return $this->error;
    }

    /**
     * Ascertain whether the field is passed the validation test
     * @param  bool $validate
     * @return bool
     */
    function isValid(bool $validate = false): bool
    {
        if ($validate) $this->validate();

        return $this->valid;
    }

    /**
     * Provide validation of the data entered and check whether the same original data
     * @return static
     */
    function validate(): static
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
     * @return static
     */
    function commit(): static
    {
        $this->original = $this->raw;
        return $this;
    }

    /**
     * Returns the value of the temporary storage to its original value
     * @return static
     */
    function revert(): static
    {
     	$this->raw = $this->original;
    	$this->render(); // re render
    	return $this;
    }

    /**
     * Rollback raw value to original
     * @return static
     */
    function rollback(): static
    {
        return $this->revert();
    }

    /**
     * Convert the data from the DB, so that is displayed is the manipulation of data from the server
     * @return static
     */
    function render(): static
    {
    	$field = $this->getField();
        $record = $this->getRecord();
    	$raw = $this->raw;

    	$this->display = $field->render($raw, $record);

    	return $this;
    }
}
