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
use Roulette\Model\Source;
use Roulette\Association as BaseAssociation;
use Roulette\Association\Relation as AssociationValue;
use Roulette\Collection;
use Roulette\Regexp;
use Roulette\Template;

use Roulette\Mixin\Configurable;

/**
 * Assosiation was a description of a relationship between a model one with the other models
 * which in this function there-many relationship model or one model
 *
 * @package \Roulette\Data
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Join extends Base
{
    use Configurable;

    protected mixed $source = null;

    protected mixed $association = null;

    /**
     * Regex|callable to identify field name is a part of joined field
     * @var null
     */
    protected mixed $identifier = null;

    /**
     * array(Regex,replacer)|callable|Roulete\Template to resolve the real fieldname to call `get` function on Model
     * @var null
     */
    protected mixed $resolver = null;

    function __construct(mixed $config = null)
    {
        $configs = Collection::create($config);

        $this->configure($configs->getAll());
    }

    function setSource(Source $source): static
    {
        $this->source = $source;
        return $this;
    }

    function getSource(): mixed
    {
        return $this->source;
    }

    function setAssociation(Association $association): static
    {
        $this->association = $association;
        return $this;
    }

    function getAssociation(mixed $apply = false): mixed
    {
        $model = $this->getSource()->getModel();
        $assoc = $model::getAssociation($this->association);
        return $assoc;
    }

    function identify(mixed $rawData = null, bool $continueResolve = false): mixed
    {
        if (!is_array($rawData)) return null;

        $identifier = $this->identifier;
        $identified = [];

        # indicate a custom identifer
        if (is_callable($identifier))
        {
            foreach ($rawData as $key => $value)
            {
                if (call_user_func_array($identifier, [$key, $value, $rawData, $this]))
                {
                    $identified[$key] = $value;
                }
            }
            return $identified;
        }

        # indicate use regex string
        if (is_string($identifier))
        {
            $identifier = new Regexp($identifier);
        }

        # indicate use regex
        if ($identifier instanceof Regexp)
        {
            foreach ($rawData as $key => $value)
            {
                // skip unmatch field
                if (!$identifier->test($key)) continue;

                $identified[$key] = $value;
            }
        }

        if ($continueResolve)
        {
            $identified = $this->resolve($identified);
        }

        return $identified;
    }

    function resolve(mixed $rawData = null, bool $identifyFirst = false): mixed
    {
        if (!is_array($rawData)) return null;

        if ($identifyFirst)
        {
            $rawData = $this->identify($rawData);
        }

        $resolver = $this->resolver;
        $resolved = [];

        # indicate a custom resolver
        if (is_callable($resolver))
        {
            foreach ($rawData as $key => $value)
            {
                $resolvedFieldString = call_user_func_array($resolver, [$key, $value, $rawData, $this]);
                $resolved[$resolvedFieldString] = $value;
            }
            return $resolved;
        }

        # indicate use Roulette\Template
        if (is_string($resolver))
        {
            $resolver = new Template($resolver);
        }
        if ($resolver instanceof Template)
        {
            foreach ($rawData as $key => $value)
            {
                $resolvedFieldString = $resolver->apply(['field' => $key, 'value' => $value]);
                $resolved[$resolvedFieldString] = $value;
            }
        }

        # indicate use regex replacer
        if (is_array($resolver))
        {
            if (empty($resolver[0])) $resolver[0] = ""; // regex
            if (empty($resolver[1])) $resolver[1] = ""; // replacer

            $resolver = new Regexp($resolver[0], $resolver[1]);
        }
        if ($resolver instanceof Regexp)
        {
            foreach ($rawData as $key => $value)
            {
                $resolvedFieldString = $resolver->replace($key);
                $resolved[$resolvedFieldString] = $value;
            }
        }

        return $resolved;
    }

    function fetchData(mixed $rawData = null): mixed
    {
        return $this->resolve($this->identify($rawData));
    }
}
