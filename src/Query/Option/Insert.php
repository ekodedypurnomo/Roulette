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
namespace Roulette\Query\Option;

use Roulette\Query\Option\OptionAbstract;
use Roulette\Query\Option\Mixin\HasTable;
use Roulette\Query\Option\Mixin\HasPatch;

/**
 * Check the type of the framework used by the server application.
 *
 * @package \Roulette\Query
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Insert extends OptionAbstract
{
    use HasTable;
    use HasPatch;

    static string $action = 'INSERT';

    protected bool $ignore = false;

    /** Columns that form the unique key for ON CONFLICT resolution. */
    protected array $conflictTarget = [];

    /** Columns to update on conflict (empty = update all non-key columns). */
    protected array $conflictUpdate = [];

    function ignore(bool $ignore = true): static
    {
        $this->ignore = $ignore;
        return $this;
    }

    function isIgnore(): bool
    {
        return $this->ignore;
    }

    function onConflict(array $target, array $update = []): static
    {
        $this->conflictTarget = $target;
        $this->conflictUpdate = $update;
        return $this;
    }

    function getConflictTarget(): array { return $this->conflictTarget; }
    function getConflictUpdate(): array { return $this->conflictUpdate; }

    function reset(): static
    {
        $this->resetTable();
        $this->resetPatch();
        $this->ignore        = false;
        $this->conflictTarget = [];
        $this->conflictUpdate = [];
        return $this;
    }
}
