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
use Roulette\Collection;

use Roulette\Mixin\Configurable;

/**
 * Is a class for helps in manipulating array in a single object.
 *
 * @package \Roulette\Data
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Option extends Base
{
    use Configurable
    {
        setConfig as protected setConfigTrait;
    }

    public mixed $fields = '*';

    public bool $render = true; // by default will follow model default render

    public bool $inline = false;

    public mixed $display = null;

    public bool $merge = false;

    public string $mergeMask = '{field}';

    public bool $autoLoad = false;

    public ?Collection $relations = null;

    public function __construct(mixed $config = null)
    {
        # boolean mean user set the render into it
        if (is_bool($config))
        {
            $this->render = $config;
            return;
        }

        # indicate user input a fields(s) name
        if (is_string($config) || (is_array($config) && !Collection::isAssoc($config)))
        {
            $this->setFields($config);
            return;
        }

        $configs = Collection::create($config);

        if (is_string($configs->get('inline')))
        {
            $configs->set('display', $configs->get('inline'));
            $configs->set('inline', true);
        }

        if (is_string($configs->get('merge')))
        {
            $configs->set('mergeMask', $configs->get('merge'));
            $configs->set('merge', true);
        }

        $this->configure($configs->getAll());
    }

    public function setConfig(mixed $config, mixed $value): static
    {
        if (is_string($config) && $config == 'relations')
        {
            $this->setRelations($value);
            return $this;
        }

        // throw back to trait
        $this->setConfigTrait($config, $value);

        return $this;
    }

    function getFields(): mixed
    {
        return $this->fields;
    }

    function setFields(mixed $fields = null): static
    {
        if (is_string($fields) && !empty($fields) && ($fields != "*"))
        {
            $fields = [$fields];
        }
        $this->fields = $fields;

        return $this;
    }

    function setRender(mixed $render): static
    {
        $this->render = (bool) $render;

        return $this;
    }

    function isRender(): bool
    {
        return (bool) $this->render;
    }

    function setAutoLoad(mixed $autoLoad): static
    {
        $this->autoLoad = (bool) $autoLoad;

        return $this;
    }

    function isAutoLoad(): bool
    {
        return (bool) $this->autoLoad;
    }

    function setInline(mixed $inline): static
    {
        $this->inline = (bool) $inline;

        return $this;
    }

    function isInline(): bool
    {
        return (bool) $this->inline;
    }

    function setMerge(mixed $merge): static
    {
        $this->merge = (bool) $merge;

        return $this;
    }

    function isMerge(): bool
    {
        return (bool) $this->merge;
    }

    function setMergeMask(mixed $mergeMask = null): static
    {
        $this->mergeMask = (string) $mergeMask;

        return $this;
    }

    function getMergeMask(): string
    {
        return (string) $this->mergeMask;
    }

    function setDisplay(mixed $display = null): static
    {
        $this->display = (string) $display;

        return $this;
    }

    function getDisplay(): string
    {
        return (string) $this->display;
    }

    function setRelations(mixed $relations = null): static
    {
        $_relations = $this->getRelations()->reset();

        if (empty($relations))
        {
            return $this;
        }

        // indicate if only one relation
        if (is_string($relations))
        {
            $_relations->set($relations, $relations);
        }
        // indicate many relations
        elseif (is_array($relations))
        {
            foreach ($relations as $relationName => $relationConfig)
            {
                if (is_numeric($relationName))
                {
                    $relationName = $relationConfig;
                    $relationConfig = ['display' => $relationName];
                }

                $_relations->set($relationName, new $this($relationConfig));
            }
        }

        return $this;
    }

    function getRelations(): Collection
    {
        if (!($this->relations instanceof Collection))
        {
            $this->relations = new Collection();
        }
        return $this->relations;
    }
}
