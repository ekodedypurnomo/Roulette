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
    protected ?string $name = null;

    /**
     * Name of field from database to access.
     *
     * @var array
     */
    protected ?string $source = null;

    /**
     * String to output for message and another user text purpose.
     *
     * @var String
     */
    protected ?string $display = null;

    /**
     * Accessibility for getData in model, private is `true` will be ignored for append on it.
     *
     * @var boolean
     */
    protected bool $private = false;

    /**
     * Readonly is `true` will be effect on field as readonly
     *
     * @var boolean
     */
    protected bool $readOnly = false;

    /**
     * Default value for first initializing data in the Model.
     *
     * @var String
     */
    protected mixed $default = null;

    /**
     * writer will enter the field to DB
     *
     * @var array
     */
    protected mixed $writer = null;

    /**
     * reader will read Field from DB field
     *
     * @var array
     */
    protected mixed $reader = null;

    /**
     * Default value converter is null, can be filled with an array
     *
     * @var array
     */
    protected mixed $converter = null;

    /**
     * Default value renderer is null. renderer can be use for field ex: gender->render to Male or boolean
     *
     * @var null
     */
    protected mixed $renderer = null;

    /**
     * Field will be vatidate from DB is same on record
     *
     * @var null
     */
    protected mixed $validation = null;

    protected mixed $operation = 'f';

    protected bool $unique = false;

    protected mixed $uniqueValidator = null;

    /**
     * __construct for function creates a new object field.
     * @param object|string|array $config field configuration
     */
    function __construct(mixed $config = null)
    {
        if (is_string($config)) $config = ['name' => $config];

        $configs = Collection::create($config);

        # set default value
        $configs->setIfNot([
            'source'  => $configs->get('name'),
            'display' => $configs->get('name')
        ]);

        $this->configure($configs->getAll(), [
            'except' => ['permission', 'operation', 'select', 'insert', 'update', 'delete'] // need to config it manualy later
        ]);

        # configure validation
        $validation = $configs->get('validation');
        if (!($validation instanceof FieldValidation))
        {
            $this->validation = new FieldValidation($this, [
                'validators' => $validation ?? []
            ]);
        }

        # configure operation
        $opPerm = $this->getOperation();
        if ($configs->has('permission')) $this->setOperation($configs->get('permission'));
        if ($configs->has('operation')) $this->setOperation($configs->get('operation'));
        if ($configs->has('select')) $this->setSelectable($configs->get('select'));
        if ($configs->has('insert')) $this->setInsertable($configs->get('insert'));
        if ($configs->has('update')) $this->setUpdatable($configs->get('update'));
        if ($configs->has('delete')) $this->setDeletable($configs->get('delete'));
    }

    /**
     * Method allows a class to decide how it will react when it is treated like a string.
     * Converting objects without __toString() method to string would cause E_RECOVERABLE_ERROR
     *
     * @return string [any string on name]
     */
    function __toString(): string
    {
        return (string) $this->name;
    }

    /**
     * Take specified Name from field
     *
     * @return String just take the data string
     */
    function getName(): ?string
    {
        return $this->name;
    }

    function setName(mixed $name = null, bool $applyToSource = false, bool $applyToDisplay = false): static
    {
        $this->name = $name;

        if ($applyToSource) $this->setSource($name);
        if ($applyToDisplay) $this->setDisplay($name);

        return $this;
    }

    /**
     * Take specified Source from Field
     *
     * @return String
     */
    function getSource(): ?string
    {
        return $this->source;
    }

    function setSource(mixed $source = null): static
    {
        $this->source = $source;
        return $this;
    }

    /**
     * Take specified Display from Field
     *
     * @return String
     */
    function getDisplay(): ?string
    {
        return $this->display;
    }

    function setDisplay(mixed $display = null): static
    {
        $this->display = $display;
        return $this;
    }

    /**
     * Take specified Default value from Field
     *
     * @return String
     */
    function getDefault(): mixed
    {
        return $this->default;
    }

    function setDefault(mixed $default = null): static
    {
        $this->default = $default;
        return $this;
    }

    /**
     * Field can only view if field isReadOnly is true
     *
     * @return boolean [true / false]
     */
    function isReadOnly(): bool
    {
        return (bool) $this->readOnly;
    }

    function setToReadOnly(bool $value = true): static
    {
        $this->public = $value;
        return $this;
    }

    /**
     * See what the field is Private or not
     *
     * @return boolean
     */
    function isPrivate(): bool
    {
        return (bool) $this->private;
    }

    function setToPrivate(bool $value = true): static
    {
        $this->private = $value;
        return $this;
    }

    /**
     * See what the field is Public or not
     *
     * @return boolean
     */
    function isPublic(): bool
    {
        return !$this->isPrivate();
    }

    function setToPublic(bool $value = true): static
    {
        $this->public = $value;
        return $this;
    }

    function getOperation(): Permission
    {
        if (!($this->operation instanceof Permission))
        {
            $this->operation = new Permission($this->operation);
        }
        return $this->operation;
    }

    function setOperation(mixed $operation = null): static
    {
        # parse operation config
        $siud = [];

        // in case array: array('insert','select') or array('insert'=>false, 'select'=>true) etc
        if (is_array($operation))
        {
            $acceptableOperation = ['select' => 's', 'insert' => 'i', 'update' => 'u', 'delete' => 'd'];
            foreach ($operation as $key => $value)
            {
                if (is_bool($value) && $value == true)
                {
                    if (array_key_exists($key, $acceptableOperation))
                    {
                        $siud[] = $acceptableOperation[$key];
                    }
                }
                elseif (is_string($value))
                {
                    if (array_key_exists($key, $acceptableOperation))
                    {
                        $siud[] = $acceptableOperation[$value];
                    }
                }
            }
        }
        // in case string: "siud","sid","sd" etc
        elseif (is_string($operation))
        {
            $siud = array_unique(str_split(strtolower($operation)));
        }
        # only allow for s,i,u,d keys
        $this->getOperation()->setPermission([
            in_array('s', $siud),
            in_array('i', $siud),
            in_array('u', $siud),
            in_array('d', $siud)
        ]);
        return $this;
    }

    /**
     * View the status field, can be select or not
     *
     * @return Boolean
     */
    function isSelectable(): mixed
    {
        return $this->getOperation()->getSelectPermission();
    }

    function setSelectable(mixed $value = true): static
    {
        $this->getOperation()->setSelectPermission($value);
        return $this;
    }

    /**
     * View the status field, can be insert or not
     *
     * @return Boolean
     */
    function isInsertable(): mixed
    {
        return $this->getOperation()->getInsertPermission();
    }

    function setInsertable(mixed $value = true): static
    {
        $this->getOperation()->setInsertPermission($value);
        return $this;
    }

    /**
     * View the status field, can be update or not
     *
     * @return Boolean
     */
    function isUpdatable(): mixed
    {
        return $this->getOperation()->getUpdatePermission();
    }

    function setUpdatable(mixed $value = true): static
    {
        $this->getOperation()->setUpdatePermission($value);
        return $this;
    }

    /**
     * View the status field, can be delete or not
     *
     * @return Boolean
     */
    function isDeletable(): mixed
    {
        return $this->getOperation()->getDeletePermission();
    }

    function setDeletable(mixed $value = true): static
    {
        $this->getOperation()->setDeletePermission($value);
        return $this;
    }

    /**
     * Use Renderer for specified fields
     *
     * @return Array
     */
    function isUseRenderer(): bool
    {
        return !empty($this->renderer);
    }

    /**
     * Use Converter for specified fields
     *
     * @return Array
     */
    function isUseConverter(): bool
    {
        return !empty($this->converter);
    }

    /**
     * Use Reader for specified fields
     *
     * @return Array
     */
    function isUseReader(): bool
    {
        return !empty($this->reader);
    }

    /**
     * Use Writer for specified fields
     *
     * @return Array
     */
    function isUseWriter(): bool
    {
        return !empty($this->writer);
    }

    /**
     * Use FieldValidation for specified fields
     *
     * @return Array
     */
    function isUseValidation(): bool
    {
        return !empty($this->validation);
    }

    /**
     * Read this field by params value
     *
     * @param  String $value value of this field
     * @return Object return a value
     */
    function read(mixed $value = null): mixed
    {
        if ($this->isUseReader() && is_callable($this->reader)) {
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
    function write(mixed $value = null): mixed
    {
        if ($this->isUseWriter() && is_callable($this->writer)) {
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
    function convert(mixed $value = null): mixed
    {
        if ($this->isUseConverter() && is_callable($this->converter)) {
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
    function render(mixed $value = null): mixed
    {
        if ($this->isUseRenderer() && is_callable($this->renderer)) {
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
    function validate(mixed $value = null): ?array
    {
        if ($this->isUseValidation() && ($this->validation instanceof FieldValidation))
        {
            // validation is valid if return an empty value
            return call_user_func_array([$this->getValidation(), 'validate'], func_get_args());
        }

        return null;
    }

    /**
     * Take the validation to be processed
     *
     * @return Array
     */
    function getValidation(): FieldValidation
    {
        if (!($this->validation instanceof FieldValidation))
        {
            $this->validation = new FieldValidation($this);
        }

        return $this->validation;
    }
}
