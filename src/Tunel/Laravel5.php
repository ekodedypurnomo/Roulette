<?php
/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Roulette\Tunel;

use Roulette\Collection;
use Roulette\Tunel\TunelAbstract;
use Roulette\Query\Operation;

/**
 * The line to do trancasction to database via Laravel 5 application server
 *
 * @package Roulette\Tunel\Laravel5
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Laravel5 extends TunelAbstract
{
    static mixed $frameworkInfo = null;

    // framework identifier
    static function check(): bool
    {
        $defined_functions = get_defined_functions()['user'];

        // check if laravel
        if (in_array('app', $defined_functions) && $app = app())
        {
            // require tunel
            // if user use laravel so the DB is already defined
            $tunelClass = \Illuminate\Support\Facades\DB::class;
            $tunel = new static($tunelClass);

            $version = explode('.', $app::VERSION);

            static::$frameworkInfo = [
                'framework' => 'Laravel',
                'version' => $app::VERSION,
                'versionInfo' => [
                    'string' => $app::VERSION,
                    'major'  => $version[0],
                    'minor'  => $version[1],
                    'patch'  => $version[2]
                ],
                'connection' => $tunelClass,
                'tunel'      => $tunel
            ];
            return true;
        }

        return false;
    }

    // model loader
    static function model(mixed $model = null): mixed
    {
        if (empty($model)) return null;

        if (class_exists($model))
        {
            return $model;
        }
        else
        {
            try
            {
                require_once($model);
                return $model;
            }
            catch (Exception $e)
            {
                throw new Exception("Cannot load model '".$model."'", 1);

            }
        }
    }

    function __construct(mixed $connection = null)
    {
        $this->connection = $connection;
    }

    // override abstract
    function operate(Operation $operation, ?callable $callback = null): mixed
    {
        switch (strtolower($operation->getMode()))
        {
            case 'select':
                $this->operateSelect($operation);
                break;

            case 'insert':
                $this->operateInsert($operation);
                break;

            case 'update':
                $this->operateUpdate($operation);
                break;

            case 'delete':
                $this->operateDelete($operation);
                break;

            case 'query':
                $this->operateQuery($operation);
                break;

            case 'exist':
                $this->operateExist($operation);
                break;

            case 'truncate':
                $this->operateTruncate($operation);
                break;
        }
        if (is_callable($callback))
        {
            $callback($this, $operation);
        }
        return $this;
    }

    protected function captureLog(callable $process, callable $callback): static
    {
        $laravelDB = $this->getConnection();

        // backup old logging state and old queryLog from laravel
        $laravelLoggingStatus = $laravelDB::logging();
        $laravelDB::enableQueryLog();
        $previousLogs = $laravelDB::getQueryLog();

        // do the process
        $process($this);

        // rollback laravel logging state and get the current queryLog for compare
        $currentLogs = $laravelDB::getQueryLog();
        if ($laravelLoggingStatus === false) $laravelDB::disableQueryLog();
        if ($laravelLoggingStatus === true) $laravelDB::enableQueryLog();

        // processing new log
        $newLog = $queryString = (count($currentLogs) > count($previousLogs)) ? end($currentLogs) : null;

        if ($newLog)
        {
            $q = $newLog['query'];
            foreach ($newLog['bindings'] as $binding)
            {
                if (is_string($binding)) $binding = "'".$binding."'";
                if (is_null($binding)) $binding = 'NULL';

                $q = preg_replace("#\?#", $binding, $q, 1);
            }
            $queryString = $q;
        }

        // callback
        $callback($queryString, $newLog);

        return $this;
    }

    protected function operateQuery(Operation $operation): static
    {
        $configs = Collection::create($operation->getOption())->setIfNot([
            'query'        => null,
            'result_array' => false
        ]);

        $conn = $this->getConnection();
        $query = trim($configs->get('query'));

        // result
        if (!empty($query))
        {
            if (preg_match('/^select/i', $query))
            {
                $operation->result = $conn::select($query);
                $operation->affectedRows = 0;
            }
            elseif (preg_match('/^insert/i', $query))
            {
                $operation->result = $conn::insert($query);
                $operation->affectedRows = 0;
            }
            elseif (preg_match('/^update/i', $query))
            {
                $operation->result = $conn::update($query);
                $operation->affectedRows = $operation->result;
            }
            elseif (preg_match('/^delete/i', $query))
            {
                $operation->result = $conn::delete($query);
                $operation->affectedRows = $operation->result;
            }
            else {
                $operation->result = $conn::statement($query);
                $operation->affectedRows = 0;
            }
        }

        // affect to the operation package
        $operation->query = $query;
        $operation->queryRaw = $query;

        return $this;
    }

    protected function operateSelect(Operation $operation): static
    {
        $option = $operation->getOption();

        if (!$option->hasTable()) return $this;

        // table
        $conn = $this->getConnection();
        $builder = $conn::table($option->getTable());

        # select
        if ($option->hasSelect())
        {
            $this->buildSelect($option->getSelect(), $builder);
        }

        # where
        if ($option->hasWhere())
        {
            $this->buildWhere($option->getWhere(), $builder);
        }

        # group
        if ($option->hasGroup())
        {
            $this->buildGroup($option->getGroup(), $builder);

            # having
            if ($option->hasHaving())
            {
                $this->buildHaving($option->getHaving(), $builder);
            }
        }

        # order
        if ($option->hasOrder())
        {
            $this->buildOrder($option->getOrder(), $builder);
        }

        # limit
        if ($option->hasLimit())
        {
            $this->buildLimit($option->getLimit(), $builder);

            # offset
            if ($option->hasOffset())
            {
                $this->buildOffset($option->getOffset(), $builder);
            }
        }

        $this->captureLog(function() use($operation, $builder)
        {
            try
            {
                $result = $builder->get();

                // affect to the operation package
                $operation->success = true;
                $operation->result = $result;
                $operation->affectedRows = 0; // there is no affected rows on select;
            }
            catch (Exception $e)
            {
                $operation->error = $e;
                $operation->success = false;
            }
        }, function($capturedLog, $rawCapturedLog) use($operation)
        {
            $operation->query = $capturedLog;
            $operation->queryRaw = $rawCapturedLog;
        });

        return $this;
    }

    protected function buildWhere(array $where, mixed $builder): static
    {
        $me = $this;

        foreach ($where as $i => $condition)
        {
            $hook = $condition->hook;
            $field = $condition->field;
            $operator = $condition->operator;
            $value = $condition->value;

            if (is_array($field))
            {
                $builder->where(function($b) use($me, $field)
                {
                    $me->buildWhere($field, $b);
                }, null, null, $hook);
                continue;
            }

            if (is_null($value) && (!is_null($operator)))
            {
                $value = $operator;
                $operator = '=';
            }
            $_operator = strtoupper(trim($operator));


            # raw version
            if (is_null($operator))
            {
                if (!is_array($value)) $value = [];
                $builder->whereRaw($field, $value, $hook);
            }

            # basic
            elseif (in_array($_operator, explode('|', '=|<|>|<>|<=|>=')))
            {
                # comparing column value
                if (preg_match('/^`.*`$/', $value))
                {
                    $builder->whereColumn($field, $_operator, $value, $hook);
                }
                elseif (preg_match('/^({column}|{date}|{time}|{day}|{month}|{year})(.*)/', $value, $matches))
                {
                    $type = $matches[1];
                    $value = ltrim($matches[2]);

                    switch ($type)
                    {
                        case '{column}' : $builder->whereColumn($field, $_operator, $value, $hook); break;
                        case '{date}'   : $builder->whereDate($field, $_operator, $value, $hook); break;
                        case '{time}'   : $builder->whereTime($field, $_operator, $value, $hook); break;
                        case '{month}'  : $builder->whereMonth($field, $_operator, $value, $hook); break;
                        case '{year}'   : $builder->whereYear($field, $_operator, $value, $hook); break;
                    }
                }
                else {
                    $builder->where($field, $_operator, $value, $hook);
                }
            }

            # between
            elseif (in_array($_operator, ['BETWEEN', 'NOT BETWEEN']))
            {
                $builder->whereBetween($field, $value, $hook, preg_match('/^NOT/', $_operator));
            }

            # inclusion
            elseif (in_array($_operator, ['IN', 'NOT IN']))
            {
                $builder->whereIn($field, $value, $hook, preg_match('/^NOT/', $_operator));
            }

            # null
            elseif (in_array($_operator, ['NULL', 'NOT NULL']))
            {
                $builder->whereNull($field, $hook, preg_match('/^NOT/', $_operator));
            }

            else
            {
                $builder->where($field, $_operator, $value, $hook, preg_match('/^NOT/', $_operator));
            }
        }

        return $this;
    }

    protected function buildSelect(mixed $select, mixed $builder): void
    {
        if (is_array($select))
        {
            foreach ($select as $alias => $column) // alias goes first to set the unique one
            {
                if (empty($column))
                {
                    $column = $alias;
                    $select[$alias] = $alias;
                }
                elseif ($column != $alias)
                {
                    $select[$alias] = $column.' AS '.$alias;
                }
            }
        }
        $builder->select($select);
    }

    protected function buildGroup(array $group, mixed $builder): void
    {
        $builder->groupBy($group);
    }

    protected function buildHaving(array $having, mixed $builder): void
    {
        $builder->havingRaw($having);
    }

    protected function buildOrder(mixed $order, mixed $builder): void
    {
        if (is_array($orders))
        {
            foreach ($orders as $field => $direction)
            {
                if (in_array(strtoupper($direction), ['ASC', 'DESC'])) {
                    $builder->orderBy($field, $direction);
                } else {
                    $builder->orderby($field, 'ASC');
                }
            }
        } else {
            $builder->orderBy($orders, 'ASC');
        }
    }

    protected function buildLimit(mixed $limit, mixed $builder): void
    {
        $builder->take($limit);
    }

    protected function buildOffset(mixed $offset, mixed $builder): void
    {
        $builder->skip($offset);
    }

    protected function operateExist(Operation $operation): static
    {
        $configs = Collection::create($operation->getOption())->setIfNot([
            'table'     => null,
            'condition' => []
        ]);

        $conn = $this->getConnection();
        $builder = $conn::table($configs->get('table'));

        $this->captureLog(function() use($operation, $builder, $patch)
        {
            try
            {
                $result = $builder->count("*");

                // affect to the operation package
                $operation->success = true;
                $operation->result = $result;
                $operation->affectedRows = 0; // there is no affected rows on insert;
            }
            catch (Exception $e)
            {
                $operation->success = false;
                $operation->error = $e;
            }
        }, function($capturedLog, $rawCapturedLog) use($operation)
        {
            $operation->query = $capturedLog;
            $operation->queryRaw = $rawCapturedLog;
        });

        return $this;
    }

    protected function operateInsert(Operation $operation): static
    {
        $option = $operation->getOption();

        if (!$option->hasTable()) return $this;

        $conn = $this->getConnection();
        $builder = $conn::table($option->getTable());

        $patch = $option->getPatch();

        $this->captureLog(function() use($operation, $builder, $patch)
        {
            try
            {
                $result = $builder->insert($patch); // result

                // affect to the operation package
                $operation->success = true;
                $operation->result = $result;
                $operation->affectedRows = 0; // there is no affected rows on insert;
            }
            catch (Exception $e)
            {
                $operation->success = false;
                $operation->error = $e;
            }
        }, function($capturedLog, $rawCapturedLog) use($operation)
        {
            $operation->query = $capturedLog;
            $operation->queryRaw = $rawCapturedLog;
        });

        return $this;
    }

    protected function operateUpdate(Operation $operation): static
    {
        $option = $operation->getOption();

        if (!$option->hasTable()) return $this;

        $conn = $this->getConnection();
        $builder = $conn::table($option->getTable());

        $patch = $option->getPatch();

        // condition
        $this->buildWhere($option->getWhere(), $builder);

        $this->captureLog(function() use($operation, $builder, $patch)
        {
            try
            {
                $result = $builder->update($patch); // result

                // affect to the operation package
                $operation->success = true;
                $operation->result = $result;
                $operation->affectedRows = $result; // result is the affected rows;
            }
            catch (Exception $e)
            {
                $operation->success = false;
                $operation->error = $e;
            }
        }, function($capturedLog, $rawCapturedLog) use($operation)
        {
            $operation->query = $capturedLog;
            $operation->queryRaw = $rawCapturedLog;
        });

        return $this;
    }

    protected function operateDelete(Operation $operation): static
    {
        $option = $operation->getOption();

        if (!$option->hasTable()) return $this;

        $conn = $this->getConnection();
        $builder = $conn::table($option->getTable());

        // condition
        $this->buildWhere($option->getWhere(), $builder);

        $this->captureLog(function() use($operation, $builder)
        {
            try
            {
                $result = $builder->delete(); // result

                // affect to the operation package
                $operation->success = true;
                $operation->result = $result;
                $operation->affectedRows = $result; // result is number of affected rows;
            }
            catch (Exception $e)
            {
                $operation->success = false;
                $operation->error = $e;
            }
        }, function($capturedLog, $rawCapturedLog) use($operation)
        {
            $operation->query = $capturedLog;
            $operation->queryRaw = $rawCapturedLog;
        });

        return $this;
    }

    protected function operateTruncate(Operation $operation): static
    {
        $option = $operation->getOption();

        if (!$option->hasTable()) return $this;

        $conn = $this->getConnection();
        $builder = $conn::table($option->getTable());

        $this->captureLog(function() use($operation, $builder, $patch)
        {
            try
            {
                $result = $builder->truncate(); // result

                // affect to the operation package
                $operation->success = true;
                $operation->result = $result;
                $operation->affectedRows = $result; // result is number of affected rows;
            }
            catch (Exception $e)
            {
                $operation->success = false;
                $operation->error = $e;
            }
        }, function($capturedLog, $rawCapturedLog) use($operation)
        {
            $operation->query = $capturedLog;
            $operation->queryRaw = $rawCapturedLog;
        });

        return $this;
    }

}
