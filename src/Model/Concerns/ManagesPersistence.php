<?php

declare(strict_types=1);

namespace Roulette\Model\Concerns;

use Roulette\Query\Operation;
use Roulette\Exception\ValidationException;

trait ManagesPersistence
{
    function save(mixed $callback = null, bool $validate = true, bool $recheck = true): mixed
    {
        if ($this->fireModelEvent('before:save') === false) {
            if (is_callable($callback)) $callback(false, $this);
            return false;
        }

        if ($validate)
        {
            if (!$this->validate()->isValid())
            {
                if (is_callable($callback)) $callback(false, $this);
                return false;
            }
        }

        $table      = static::getTable();
        $dataUpdate = $this->getDataToUpdate();
        $dataInsert = $this->getDataToInsert();
        $condition  = static::getFields()->mapToSource([
            static::getPrimary() => $this->getId()
        ]);

        $isUpdate = $this->isAlive($recheck);

        if ($isUpdate)
        {
            $this->fireModelEvent('before:update');
            $operation = Operation::create('update')->buildQuery(function($qop) use($table, $dataUpdate, $condition)
            {
                $qop->table($table)->where($condition)->set($dataUpdate);
            })->execute();
        }
        else
        {
            $this->fireModelEvent('before:create');
            $operation = Operation::create('insert')->buildQuery(function($qop) use($table, $dataInsert)
            {
                $qop->table($table)->set($dataInsert);
            })->execute();
        }

        $success = $operation->isSuccess();

        if ($success)
        {
            $this->reload($revert = true);
            $this->fireModelEvent($isUpdate ? 'after:update' : 'after:create');
            $this->fireModelEvent('after:save');
        }

        if (is_callable($callback)) $callback($success, $operation, $this);

        return $success;
    }

    function saveOrFail(bool $validate = true, bool $recheck = true): bool
    {
        if ($validate && !$this->validate()->isValid()) {
            throw new ValidationException($this->getErrorMessages(true));
        }
        return (bool) $this->save(null, false, $recheck);
    }

    function destroy(mixed $callback = null): mixed
    {
        $success   = false;
        $table     = static::getTable();
        $condition = $this->getFields()->mapToSource([
            $this->getPrimary() => $this->get(static::getPrimary(), false)
        ]);

        if ($this->isAlive($recheck = true))
        {
            if ($this->fireModelEvent('before:destroy') === false) {
                if (is_callable($callback)) $callback(false, null, $this);
                return false;
            }

            $operation = Operation::create('delete')->buildQuery(function($qop) use($table, $condition)
            {
                $qop->table($table)->where($condition);
            })->execute();

            $success = (boolean) $operation->getAffectedRows();

            if ($success)
            {
                $this->makeAlive(false);
                $this->fireModelEvent('after:destroy');
            }
        }

        if (is_callable($callback)) $callback($success, $operation ?? null, $this);

        return $success;
    }

    static function transaction(callable $fn): mixed
    {
        $tunel = Operation::getOperationTunel();
        $tunel->beginTransaction();
        try {
            $result = $fn();
            $tunel->commit();
            return $result;
        } catch (\Throwable $e) {
            $tunel->rollback();
            throw $e;
        }
    }
}
