<?php

declare(strict_types=1);

namespace Roulette\Model\Concerns;

use Roulette\Query\Operation;

trait ManagesBulkOps
{
    static function insertMany(array $rows): int
    {
        $inserted = 0;
        $table    = static::getTable();

        foreach ($rows as $rowData) {
            $record = new static($rowData);
            $data   = $record->getDataToInsert();

            if (empty($data)) continue;

            $operation = Operation::create('insert')->buildQuery(function($qop) use($table, $data) {
                $qop->table($table)->set($data);
            })->execute();

            if ($operation->isSuccess()) $inserted++;
        }

        return $inserted;
    }

    static function updateWhere(array $condition, array $data): int
    {
        if (empty($data)) return 0;

        $table     = static::getTable();
        $condition = static::getFields()->mapToSource($condition);
        $patch     = static::getFields()->mapToSource($data);

        $operation = Operation::create('update')->buildQuery(function($qop) use($table, $condition, $patch) {
            $qop->table($table)
                ->set($patch)
                ->where($condition);
        })->execute();

        return (int) $operation->getAffectedRows();
    }

    static function destroyWhere(array $condition): int
    {
        $table     = static::getTable();
        $condition = static::getFields()->mapToSource($condition);

        $operation = Operation::create('delete')->buildQuery(function($qop) use($table, $condition) {
            $qop->table($table)->where($condition);
        })->execute();

        return (int) $operation->getAffectedRows();
    }

    static function insertOrIgnore(array $rows): int
    {
        $inserted = 0;
        $table    = static::getTable();

        foreach ($rows as $rowData) {
            $record = new static($rowData);
            $data   = $record->getDataToInsert();

            if (empty($data)) continue;

            $operation = Operation::create('insert')->buildQuery(function($qop) use($table, $data) {
                $qop->table($table)->set($data)->ignore(true);
            })->execute();

            if ($operation->isSuccess() && $operation->getAffectedRows() > 0) $inserted++;
        }

        return $inserted;
    }

    static function upsert(array $rows, array $uniqueFields, array $updateFields = []): int
    {
        $affected  = 0;
        $table     = static::getTable();
        $fields    = static::getFields();

        $uniqueCols = $fields->mapToSource(array_fill_keys($uniqueFields, null));
        $uniqueCols = array_keys($uniqueCols);

        $updateCols = empty($updateFields) ? [] : array_keys($fields->mapToSource(array_fill_keys($updateFields, null)));

        foreach ($rows as $rowData) {
            $record = new static($rowData);
            $data   = $record->getDataToInsert();

            if (empty($data)) continue;

            $operation = Operation::create('insert')->buildQuery(function($qop) use($table, $data, $uniqueCols, $updateCols) {
                $qop->table($table)->set($data)->onConflict($uniqueCols, $updateCols);
            })->execute();

            if ($operation->isSuccess()) $affected += (int) $operation->getAffectedRows();
        }

        return $affected;
    }
}
