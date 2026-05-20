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
namespace Roulette\Mixin;

/**
 * ##Configurable
 * Easy to get set or reconfigure each properties even it is `private`.
 * Properties name started with `_` (underscore) mean unconfigurable/inaccesible
 *
 *      class Person extends \Roulette\Base{
 *          protected $name = null; //configurable property
 *          protected $_tall = null; //unconfigurable property
 *
 *          function __construct($config = null){
 *              parent::__construct($config);
 *          }
 *      }
 *
 *      // easy to construct object using array to pass to the matched properties
 *      $person = new Person(array(
 *          'name'=>'Eko',
 *          '_tall'=>170
 *      ));
 *
 *      // easy to set and get properties value
 *      $person->getConfig('name'); // return 'Eko'
 *      $person->setConfig('name','Dedy');
 *      $person->getConfig('name'); // return 'Dedy'
 *
 *      // easy to protect property using `_` in the first variable name
 *      $person->getConfig('_tall'); // return null
 *      $person->setConfig('_tall','168');
 *      $person->getConfig('_tall'); // still return null
 *
 * @package Roulette\Mixin
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
trait Configurable
{
    /**
     * Merge its config with parent config.
     * this will merge the parent config into it from parent config,
     * this method better to use in merge some config within value is array
     *
     * @param string|null $config a set of configuration to be applied to the class
     * @return mixed
     */
    protected function mergeParentConfig(?string $config = null): mixed
    {
        if (empty($config) || !property_exists($this, $config)) return null;

        $parentClass = get_parent_class($this);
        if ($parentClass)
        {
            $parent = new $parentClass;
            $this->$config = array_merge($parent->$config, $this->$config);
        }
        return $this->$config;
    }

    /**
     * Configure class using passed arguments (Array necessaries)
     *
     *      Example:
     *      class Person extends \Roulette\Base
     *      {
     *          protected $name = null;
     *          function __construct($config){
     *              function __construct($config)
     *          }
     *      }
     *      $person->configure(array(
     *          'name'=>'Eko'
     *      ));
     *      $person->getConfig('name'); // return 'Eko'
     *
     * @param array|null $config New configs to be applied in
     * @param mixed $options
     * @return static
     */
    protected function configure(?array $config = null, mixed $options = null): static
    {
        $only   = (is_array($options) && array_key_exists('only', $options))   ? $options['only']   : null;
        $except = (is_array($options) && array_key_exists('except', $options)) ? $options['except'] : null;

        if (is_array($config))
        {
            foreach ($config as $configName => $configValue)
            {
                if (is_array($only)   && !in_array($configName, $only))   continue;
                if (is_array($except) &&  in_array($configName, $except)) continue;

                $this->setConfig($configName, $configValue, true);
            }
        }

        return $this;
    }

    /**
     * Sorthand for getConfig if pass one argument.
     * Sorthand for setConfig if pass two or more arguments.
     *
     * @return mixed Value depend on the arguments passed
     */
    protected function config(): mixed
    {
        if (func_num_args() <= 1) {
            return $this->getConfig(func_get_arg(0));
        }
        return $this->setConfig(func_get_arg(0), func_get_arg(1));
    }

    /**
     * Get value from a config/property
     * @param string|null $configName Config/property name
     * @param mixed $defaultValue
     * @return mixed Config/property value
     */
    protected function getConfig(?string $configName = null, mixed $defaultValue = null): mixed
    {
        if (preg_match('/^(_+)/', (string) $configName)) return null;

        return property_exists($this, $configName) ? $this->$configName : $defaultValue;
    }

    /**
     * Set value for a config/property
     *
     * @param string|null $configName Config/property name
     * @param mixed $configValue Value to be set into the property
     * @param bool $merge_config merge config with its existing config, used if existing value is array
     * @return static
     */
    protected function setConfig(?string $configName = null, mixed $configValue = null, bool $merge_config = false): static
    {
        if (preg_match('/^(_+)/', (string) $configName)) return $this;

        if (is_array($configValue)
            && property_exists($this, $configName)
            && is_array($this->$configName)
            && $merge_config)
        {
            $this->$configName = array_merge($this->$configName, $configValue);
        }
        else
        {
            $this->$configName = $configValue;
        }
        return $this;
    }

    /**
     * Set config or property to `true`
     *
     * @param string|null $configName Config/Property name to be setted
     * @param bool $enabled Enabled status
     * @return static
     */
    protected function enableConfig(?string $configName = null, bool $enabled = true): static
    {
        return $this->setConfig($configName, $enabled);
    }

    /**
     * Check if config has `true` value
     *
     * @param string|null $configName Config name
     * @return bool Enabled status
     */
    protected function configEnabled(?string $configName = null): bool
    {
        return (bool) $this->getConfig($configName);
    }

    /**
     * Set config or property to `false`
     *
     * @param string|null $configName Config/Property name to be setted
     * @param bool $disabled Disabled status
     * @return static
     */
    protected function disableConfig(?string $configName = null, bool $disabled = true): static
    {
        return $this->setConfig($configName, !$disabled);
    }

    /**
     * Check if config has `false` value
     *
     * @param string|null $configName Config name
     * @return bool Disabled status
     */
    protected function configDisabled(?string $configName = null): bool
    {
        return !(bool) $this->getConfig($configName);
    }

    /**
     * Check existense of config name.
     * @param string|null $configName Config name
     * @return bool
     */
    protected function hasConfig(?string $configName = null): bool
    {
        return property_exists($this, (string) $configName);
    }
}
