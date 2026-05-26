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
namespace Roulette\Model;

use Roulette\Model\Store;

/**
 * Holds the result of a paginated query — the current page's records plus
 * the metadata needed to build navigation links.
 *
 * Returned by Model::paginate().
 *
 * @package \Roulette\Model
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Paginator
{
    public function __construct(
        public readonly Store $items,
        public readonly int   $total,
        public readonly int   $perPage,
        public readonly int   $currentPage,
        public readonly int   $lastPage,
    ) {}

    public function hasMorePages(): bool
    {
        return $this->currentPage < $this->lastPage;
    }

    public function isFirstPage(): bool
    {
        return $this->currentPage === 1;
    }

    public function isLastPage(): bool
    {
        return $this->currentPage >= $this->lastPage;
    }

    public function toArray(): array
    {
        return [
            'total'       => $this->total,
            'per_page'    => $this->perPage,
            'current_page' => $this->currentPage,
            'last_page'   => $this->lastPage,
            'has_more'    => $this->hasMorePages(),
            'items'       => $this->items->toArray(),
        ];
    }
}
