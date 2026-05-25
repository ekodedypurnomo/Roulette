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
namespace Roulette\Query;

use Roulette\Base;
use Roulette\Collection;
use Roulette\Tunel\TunelAbstract;
use Roulette\Query\Option\OptionAbstract;
use Roulette\Query\Option\Select;
use Roulette\Query\Option\Insert;
use Roulette\Query\Option\Update;
use Roulette\Query\Option\Delete;
use Roulette\Query\Option\Option;

use Roulette\Mixin\Configurable;

/**
 * Standardize database request operation
 *
 * @package \Roulette\Query
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Operation extends Base
{
    use Configurable;

    /**
     * Logged operation
     * @var null
     */
    static protected array $operations = [];

    /**
     * Set to true to keep operation logged
     * @var boolean
     */
    static protected bool $isLogging = false;

    /**
     * Tunel to be used by operation to communicate with DB
     * @var null
     */
    static protected ?TunelAbstract $operationTunel = null;

    /**
     * Framework that being used by the server application
     * @var null
     */
    static protected mixed $frameworkInfo = null;

    /**
     * [$frameworks description]
     * @var null
     */
    protected static mixed $frameworks = null;

    /**
     * [getAll description]
     * @return [type] [description]
     */
    static function getDefinedFrameworks(): array
    {
        if (is_null(static::$frameworks))
        {
            $tunelDir = dirname(__DIR__) . '/Tunel/Tunels.php';

            static::$frameworks = require_once(str_replace('/', DIRECTORY_SEPARATOR, $tunelDir));

            if (!is_array(static::$frameworks)) static::$frameworks = [];
        }
        return static::$frameworks;
    }

    /**
     * Get the framework that being used as a server application
     * @return [type] [description]
     */
    static function getFrameworkInfo(): mixed
    {
        if (static::$frameworkInfo) return static::$frameworkInfo;

        foreach (static::getDefinedFrameworks() as $key => $definer)
        {
            if (class_exists($definer) && is_callable([$definer, 'check']))
            {
                $valid = $definer::check();
                if ($valid && is_callable([$definer, 'info']))
                {
                    return $definer::info();
                }
                // break manualy if info return isnt callable
                break;
            }
        }

        return null;
    }

    /**
     * Get the tunel of the framework that being used
     * @return [type] [description]
     */
    static function getFrameworkTunel(): mixed
    {
        return Collection::create(self::getFrameworkInfo())->get('tunel');
    }

    /**
     * Set the tunel from framework that being used, to the proxy
     * @return [type] [description]
     */
    static function useFrameworkTunel(): void
    {
        static::setOperationTunel(static::getFrameworkTunel());
    }

    /**
     * retrieve data on a model
     * @param Tunel $tunel [description]
     */
    static function setOperationTunel(TunelAbstract $tunel): string
    {
        static::$operationTunel = $tunel;
        return static::class;
    }

    /**
     * Get the tunel that being used by the proxy
     * @return /Roulette/tunel
     */
    static function getOperationTunel(): ?TunelAbstract
    {
        if (!static::$operationTunel) static::useFrameworkTunel();
        return static::$operationTunel;
    }

    /**
     * Set or get the tunel
     * @return [type] [description]
     */
    static function tunel(mixed ...$args): mixed
    {
        return empty($args) ? static::getOperationTunel() : static::setOperationTunel($args[0]);
    }

    /**
     * Get the model that being used
     * @param  [type] $model [description]
     * @return [type]        [description]
     */
    static function getModel(mixed $model = null): mixed
    {
        $tunel = static::tunel();
        return $tunel::model($model);
    }

    /**
     * Get Logged Operation
     * @return Operation
     */
    static function getLog(): array
    {
        if (!is_array(static::$operations)) static::$operations = [];
        return static::$operations;
    }

    /**
     * Get the last added operation of the proxy
     * @return array
     */
    static function getLastLog(): mixed
    {
        $operation = static::getLog();
        return empty($operation) ? null : end($operation);
    }

    /**
     * Get isLogging
     * @return boolean
     */
    static function isLogging(): bool
    {
        return static::$isLogging;
    }

    /**
     * Set isLogging to true
     */
    static function enableLog(): string
    {
        static::$isLogging = true;
        return static::class;
    }

    /**
     * Set isLogging to false
     */
    static function disableLog(): string
    {
        static::$isLogging = false;
        return static::class;
    }

    static function remove(Operation $operation): string
    {
        foreach (static::$operations as $i => $o)
        {
            if ($o === $operation)
            {
                unset(static::$operations[$i]);

                # reindex array
                static::$operations = array_values(static::$operations);
                break;
            }
        }
        return static::class;
    }

    /**
     * Add Operation
     * @param Operation|null $operation [description]
     */
    static function add(Operation $operation = null): string
    {
        if (static::$isLogging === true)
        {
            # remove first
            static::remove($operation);
            static::$operations[] = $operation;
        }
        return static::class;
    }

    /**
     * making operations with database
     * @param  array   $config  [description]
     * @param  boolean $execute [description]
     * @return Operation
     */
    static function create(mixed $operationMode = null, bool $executeImmedietly = false, bool $appendToLog = false): static
    {
        $operation = new Operation(static::tunel(), $operationMode);

        if ($appendToLog) static::add($operation);

        if ($executeImmedietly) $operation->execute();

        return $operation;
    }



    ///////////////
    // OPERATION //
    ///////////////

    /**
     * [$tunnel description]
     * @var null
     */
    protected ?TunelAbstract $tunnel = null;

    /**
     * create a function with the number of parameter that can change
     * @var null
     */
    protected mixed $option = null;

    /**
     * notification that an action has been successful
     * @var null
     */
    public mixed $success = null;

    /**
     * notification that an action has occurred error
     * @var null
     */
    public mixed $error = null;

    /**
     * Last query executed
     * @var null
     */
    public mixed $query = null;

    /**
     * Original last query from framework
     * @var null
     */
    public mixed $queryRaw = null;

    /**
     * The result fo the operation
     * @var null
     */
    public mixed $result = null;

    /**
     * Rows that affected by last operation
     * @var null
     */
    public mixed $affectedRows = null;

    /**
     * [$executeAt description]
     * @var null
     */
    public mixed $executeTime = null;

    public mixed $onSuccess = null;
    public mixed $onFailure = null;
    public mixed $onExecuted = null;

    /**
     * @ignore
     */
    function __construct(TunelAbstract $tunnel, mixed $operationMode = 'query')
    {
        $this->tunnel = $tunnel;

        if ($operationMode instanceof OptionAbstract)
        {
            $this->option = $operationMode;
        }
        else
        {
            switch (strtoupper($operationMode))
            {
                case 'SELECT': $this->option = new Select(); break;
                case 'INSERT': $this->option = new Insert(); break;
                case 'UPDATE': $this->option = new Update(); break;
                case 'DELETE': $this->option = new Delete(); break;
                case 'QUERY': default: $this->option = new Option(); break;
            }
        }
    }

    function getMode(): mixed
    {
        $option = $this->option;

        if ($option)
        {
            return $option->getAction();
        }

        return null;
    }

    /**
     * the process of processing data to be sent to DB
     * @return [type] [description]
     */
    function getTunel(): ?TunelAbstract
    {
        return $this->tunnel;
    }

    function getOption(): mixed
    {
        if (!($this->option instanceof OptionAbstract))
        {
            $this->option = new Option();
        }

        return $this->option;
    }

    /**
     * send or retrieve data
     * @return Array
     */
    function getRecord(): mixed
    {
        return (is_array($this->result) && !empty($this->result)) ? $this->result[0] : null;
    }

    /**
     * Get the records from the operation
     * @return Array
     */
    function getRecords(): array
    {
        if (!is_array($this->result))
        {
            return [];
        }

        return $this->result;
    }

    function getResult(): mixed
    {
        return $this->result;
    }

    function getQuery(): mixed
    {
        return $this->query;
    }

    function getQueryRaw(): mixed
    {
        return $this->queryRaw;
    }

    function getAffectedRows(): mixed
    {
        return $this->affectedRows;
    }

    /**
     * check whether the data error
     * @return [type] [description]
     */
    function getError(): mixed
    {
        return $this->error;
    }

    /**
     * Check whether the operation is a success
     * @return boolean [description]
     */
    function isSuccess(): bool
    {
        return (bool) $this->success;
    }

    /**
     * Check Whether the operation is ececuted or not
     * @return boolean [description]
     */
    function isExecuted(): bool
    {
        return !is_null($this->executed);
    }

    function buildQuery(callable $callback): static
    {
        call_user_func_array($callback, [$this->getOption(), $this]);

        return $this;
    }

    /**
     * function performs a process with database
     * @return [type] [description]
     */
    function execute(): static
    {
        # will execute if only has a tunnel
        $tunnel = $this->getTunel();
        if (!$tunnel) return $this;

        # initializing
        $this->executeTime = [microtime(true), null, null]; // [startAt, finnishAt, speed] in microtime
        $this->result = null;
        $this->success = null;
        $this->query = null;
        $this->queryRaw = null;
        $this->affectedRows = null;

        # make sure inserted to the log and reorder by last executed
        $this->submit();

        $tunnel->operate($this, function($t, $o)
        {
            $o->executeTime[1] = microtime(true);
            $o->executeTime[2] = round(((double)$o->executeTime[1]) - ((double)$o->executeTime[0]), 4);

            $this->callListeners();
        });

        return $this;
    }

    function submit(): static
    {
        static::add($this);
        return $this;
    }

    function release(): static
    {
        static::remove($this);
        return $this;
    }

    protected function callListeners(): static
    {
        if (is_callable($this->onExecuted)) call_user_func_array($this->onExecuted, [$this]);

        if (is_callable($this->onSuccess) && $this->isSuccess()) call_user_func_array($this->onSuccess, [$this]);
        if (is_callable($this->onFailure) && !$this->isSuccess()) call_user_func_array($this->onFailure, [$this]);

        return $this;
    }

    /**
     * function sends that an action has been successful
     *
     * @param  callable|null $callback [description]
     * @return [type]                  [description]
     */
    function success(callable $callback = null): static
    {
        if (!$this->isExecuted()) return $this;

        if ($this->isSuccess())
            call_user_func_array($callback, [$this]);

        return $this;
    }

    /**
     * function sends that an action has failed
     *
     * @param  callable|null $callback [description]
     * @return [type]                  [description]
     */
    function failure(callable $callback = null): static
    {
        if (!$this->isExecuted()) return $this;

        if (!$this->isSuccess())
            call_user_func_array($callback, [$this]);

        return $this;
    }

    /**
     * function displays a notification after the previous effect
     *
     * @param  callable|null $callback [description]
     * @return function                [description]
     */
    function callback(callable $callback = null): static
    {
        if (!$this->isExecuted()) return $this;

        call_user_func_array($callback, [$this, $this->isSuccess()]);

        return $this;
    }
}
