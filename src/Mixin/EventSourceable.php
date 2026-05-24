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

use Roulette\Collection;
use Roulette\Query\Operation;

/**
 * Opt-in audit trail for Model subclasses. Apply with `use EventSourceable`.
 *
 * Automatically captures field-level diffs on every save() and a tombstone on
 * destroy(), persisting each as a row in the events table. The table defaults
 * to `model_events`; override per model via prototype config:
 *
 *   static::prototype(['eventSourcing' => ['table' => 'my_audit_log'], ...]);
 *
 * The events table must exist before any model using this trait is saved:
 *
 *   CREATE TABLE model_events (
 *       id          TEXT PRIMARY KEY,
 *       model_class TEXT NOT NULL,
 *       record_id   TEXT NOT NULL,
 *       operation   TEXT NOT NULL,  -- 'create' | 'update' | 'delete'
 *       payload     TEXT NOT NULL,  -- JSON field diffs: { field: { from, to } }
 *       created_at  TEXT NOT NULL
 *   )
 *
 * @package \Roulette\Mixin
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
trait EventSourceable
{
    /**
     * Override save() to capture a 'create' or 'update' event on success.
     *
     * Uses a wrapped callback so the $success flag from the DB operation is
     * the authoritative signal — avoids ambiguity in save()'s return value.
     */
    function save(mixed $originalCallback = null, bool $validate = true, bool $recheck = true): mixed
    {
        $isCreate = !$this->isAlive(false);
        $diff     = $this->capturePreSaveDiff();

        $wrappedCallback = function(bool $success, $operation, $record) use ($originalCallback, $diff, $isCreate) {
            if ($success) {
                $this->persistEvent($diff, $isCreate ? 'create' : 'update');
            }
            if (is_callable($originalCallback)) {
                return call_user_func($originalCallback, $success, $operation, $record);
            }
            return $success ? null : false;
        };

        return parent::save($wrappedCallback, $validate, $recheck);
    }

    /**
     * Override destroy() to capture a 'delete' event on success.
     *
     * Detects success by comparing isAlive() before and after the parent call —
     * no extra DB roundtrip required.
     */
    function destroy(mixed $callback = null): mixed
    {
        $wasAlive = $this->isAlive(false);
        $result   = parent::destroy($callback);

        if ($wasAlive && !$this->isAlive(false)) {
            $this->persistEvent([], 'delete');
        }

        return $result;
    }

    /**
     * Retrieve all recorded events for this record, ordered chronologically.
     *
     * Returns a Collection of arrays; each array has keys:
     * id, model_class, record_id, operation, payload (decoded array), created_at.
     */
    function getHistory(): Collection
    {
        $table      = $this->getEventSourcingTable();
        $recordId   = (string) $this->getId();
        $modelClass = static::class;

        $operation = Operation::create('select')->buildQuery(
            function($qop) use ($table, $recordId, $modelClass) {
                $qop->table($table)
                    ->where(['record_id' => $recordId, 'model_class' => $modelClass])
                    ->orderBy(['created_at' => 'ASC']);
            }
        )->execute();

        return Collection::create(
            array_map(function(array $row) {
                $row['payload'] = json_decode($row['payload'], true) ?? [];
                return $row;
            }, $operation->getRecords())
        );
    }

    /**
     * Build a field diff from the currently modified fields.
     *
     * For creates, original is null (no prior DB state). For updates, original
     * holds the value from the last load/save. Only modified fields are included.
     */
    private function capturePreSaveDiff(): array
    {
        $diff = [];
        foreach ($this->getModified() as $fieldName) {
            $value            = $this->getValue($fieldName);
            $diff[$fieldName] = ['from' => $value->getOriginal(), 'to' => $value->getRaw()];
        }
        return $diff;
    }

    /**
     * Persist one event row to the events table.
     */
    private function persistEvent(array $payload, string $operation): void
    {
        $table = $this->getEventSourcingTable();

        Operation::create('insert')->buildQuery(function($qop) use ($table, $payload, $operation) {
            $qop->table($table)->set([
                'id'          => uniqid('event_', true),
                'model_class' => static::class,
                'record_id'   => (string) $this->getId(),
                'operation'   => $operation,
                'payload'     => json_encode($payload),
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        })->execute();
    }

    /**
     * Resolve the events table name from prototype config or return the default.
     */
    private function getEventSourcingTable(): string
    {
        $config = static::prototype()->get('eventSourcing');
        return (is_array($config) && isset($config['table'])) ? $config['table'] : 'model_events';
    }
}
