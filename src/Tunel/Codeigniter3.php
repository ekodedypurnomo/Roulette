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

use Roulette\Tunel\TunelAbstract;
use Roulette\Query\Operation;

/**
 * Description Tunel
 *
 * @package Roulette\Tunel\Codeigniter3
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Codeigniter3 extends TunelAbstract
{

    static mixed $frameworkInfo = null;

    static function check(): bool
    {
        return false;
    }

    function __construct(mixed $connection = null)
    {
        $this->connection = $connection;
    }

    function operate(Operation $operation, ?callable $callback = null): mixed
    {
        return $this;
    }

    function select(array $config = null): mixed
    {
        if (!is_array($config)) $config = [];

        $config = Collection::applyIfNot($config, [
            'from'         => null,
            'fields'       => '*',
            'where'        => [],
            'group'        => [],
            'having'       => [],
            'limit'        => null,
            'start'        => 0,
            'order'        => null,
            'result_array' => true
        ]);

        $connection = $this->getConnection();

        $connection->select($config['fields']);

        if (!empty($config['where'])) {
            $connection->where($config['where']);
        }

        if (!empty($config['group'])) {
            $connection->group_by($config['group']);
        }

        if (!empty($config['having'])) {
            $connection->having($config['having']);
        }

        $config['limit'] = (int) $config['limit'];
        $config['start'] = (int) $config['start'];
        if (!empty($config['limit'])) {
            $connection->limit($config['limit'], $config['start']);
        }

        if (!empty($config['order'])) {
            if (is_array($config['order'])) {
                foreach ($config['order'] as $key => $value) {
                    if (in_array(strtolower($value), ['asc', 'desc'])) {
                        $connection->order_by($key, $value);
                    } else {
                        $connection->order_by($value, 'asc');
                    }
                }
            } else {
                $connection->order_by($config['order'], 'asc');
            }
        }

        $query = $connection->get($config['from']);

        if ($config['result_array'] === true) {
            return $query->result_array();
        } else {
            return $query->result();
        }
    }

    function insert(array $config = null): mixed
    {
        $connection = $this->getConnection();
        $result = $connection->insert($table, $data);

        $this->debug($connection->last_query());

        return $result;
    }

    function update(array $config = null): mixed
    {
        $connection = $this->getConnection();
        $result = $connection->update($table, $data, $where);

        $this->debug($connection->last_query());

        return $result;
    }

    function delete(array $config = null): mixed
    {
        $connection = $this->getConnection();
        $result = $connection->delete($table, $where);

        $this->debug($connection->last_query());

        return $result;
    }

    function query(array $config = null): mixed
    {
        $connection = $this->getConnection();
        $query = $connection->query($query);
        $result = $result_array === true ? $query->result_array() : $query->result();

        $this->debug($connection->last_query());

        return $result;
    }

    function exists(array $config = null): mixed
    {
        $connection->from($table);
        $connection->where($condition);
        $result = $connection->count_all_results();

        $this->debug($connection->last_query());

        return $result;
    }

    function getNumRows(): mixed
    {
        $connection = $this->getConnection();
        $result = $connection->num_rows();
        return $result;
    }

    function getAffectedRow(): mixed
    {
        $connection = $this->getConnection();
        $result = $connection->affected_rows();
        return $result;
    }

    function beginTransaction(): bool
    {
        $this->getConnection()->trans_begin();
        return true;
    }

    function commit(): bool
    {
        $this->getConnection()->trans_commit();
        return true;
    }

    function rollback(): bool
    {
        $this->getConnection()->trans_rollback();
        return true;
    }

}
