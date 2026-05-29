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

/**
 * Wraps a raw SQL fragment so tunels can embed it verbatim instead of binding
 * it as a parameter. Used internally by increment()/decrement() to emit
 * atomic expressions like `col = col + 1`.
 *
 * @package \Roulette\Query
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class RawExpression
{
    public function __construct(public readonly string $sql) {}

    public function __toString(): string
    {
        return $this->sql;
    }
}
