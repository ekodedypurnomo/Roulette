<?php

declare(strict_types=1);

/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please source the LICENSE
 * file that was distributed with this source code.
 */
namespace Roulette\Model;

use Roulette\Base;

/**
 * @package \Roulette\Model
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Policy extends Base
{
    protected ?string $name = null;

    protected array $assertions = [];

    static function create(mixed $config = null): static
    {
        return new static($config);
    }

    function __construct(?string $name = null, callable ...$fns)
    {
        $this->name = $name;

        foreach ($fns as $func)
        {
            if (is_callable($func))
            {
                $this->addAssertion($func);
            }
        }
    }

    function addAssertion(callable $assertion): void
    {
        if (!is_array($this->assertions)) $this->assertions = [];

        $this->assertions[] = $assertion;
    }

    function reset(): void
    {
        $this->assertions = [];
    }

    function getAssertions(): array
    {
        if (!is_array($this->assertions))
        {
            $this->assertions = [];
        }
        return $this->assertions;
    }

    function assert(mixed ...$args): bool
    {
        foreach ($this->assertions as $assertion)
        {
            if (is_callable($assertion))
            {
                if (call_user_func_array($assertion, $args))
                {
                    return false;
                }
            }
        }
        return true;
    }
}
