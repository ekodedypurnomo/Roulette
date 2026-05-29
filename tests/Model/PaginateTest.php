<?php

declare(strict_types=1);

namespace Roulette\Tests\Model;

use Roulette\Model;
use Roulette\Model\Paginator;
use Roulette\Model\Prototype;
use Roulette\Model\Store;
use Roulette\Tests\Support\DbTestCase;

class PageModel extends Model
{
    static protected ?Prototype $prototype = null;
    static protected bool $useCache = false;
}

PageModel::prototype([
    'table'   => 'pages',
    'primary' => 'id',
    'autoId'  => false,
    'fields'  => [
        ['name' => 'id',    'update' => false],
        ['name' => 'title', 'type' => 'string'],
    ],
]);

class PaginateTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->tunel->exec('CREATE TABLE pages (id TEXT PRIMARY KEY, title TEXT)');
    }

    private function seedPages(int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $this->tunel->getPdo()->prepare('INSERT INTO pages VALUES (?, ?)')->execute(["p$i", "Page $i"]);
        }
    }

    // --- count ---

    public function testCountReturnsTotal(): void
    {
        $this->seedPages(7);
        $this->assertSame(7, PageModel::count());
    }

    public function testCountZeroWhenEmpty(): void
    {
        $this->assertSame(0, PageModel::count());
    }

    // --- paginate ---

    public function testPaginateReturnsPaginator(): void
    {
        $this->seedPages(10);
        $result = PageModel::paginate(5, 1);
        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function testPaginateItemsIsStore(): void
    {
        $this->seedPages(10);
        $result = PageModel::paginate(5, 1);
        $this->assertInstanceOf(Store::class, $result->items);
    }

    public function testPaginateFirstPageHasCorrectItems(): void
    {
        $this->seedPages(10);
        $result = PageModel::paginate(5, 1);
        $this->assertSame(5, $result->items->count());
    }

    public function testPaginateSecondPageHasCorrectItems(): void
    {
        $this->seedPages(10);
        $result = PageModel::paginate(5, 2);
        $this->assertSame(5, $result->items->count());
    }

    public function testPaginateLastPageWithRemainder(): void
    {
        $this->seedPages(13);
        $result = PageModel::paginate(5, 3);
        $this->assertSame(3, $result->items->count());
    }

    public function testPaginateMetadata(): void
    {
        $this->seedPages(23);
        $result = PageModel::paginate(10, 2);

        $this->assertSame(23, $result->total);
        $this->assertSame(10, $result->perPage);
        $this->assertSame(2,  $result->currentPage);
        $this->assertSame(3,  $result->lastPage);
    }

    public function testPaginateHasMorePages(): void
    {
        $this->seedPages(20);
        $first = PageModel::paginate(10, 1);
        $last  = PageModel::paginate(10, 2);

        $this->assertTrue($first->hasMorePages());
        $this->assertFalse($last->hasMorePages());
    }

    public function testPaginateIsFirstLastPage(): void
    {
        $this->seedPages(20);
        $first = PageModel::paginate(10, 1);
        $last  = PageModel::paginate(10, 2);

        $this->assertTrue($first->isFirstPage());
        $this->assertFalse($first->isLastPage());
        $this->assertFalse($last->isFirstPage());
        $this->assertTrue($last->isLastPage());
    }

    public function testPaginateToArray(): void
    {
        $this->seedPages(5);
        $result = PageModel::paginate(5, 1);
        $arr = $result->toArray();

        $this->assertArrayHasKey('total', $arr);
        $this->assertArrayHasKey('per_page', $arr);
        $this->assertArrayHasKey('current_page', $arr);
        $this->assertArrayHasKey('last_page', $arr);
        $this->assertArrayHasKey('has_more', $arr);
        $this->assertArrayHasKey('items', $arr);
        $this->assertSame(5, $arr['total']);
    }

    public function testPaginateEmptyTable(): void
    {
        $result = PageModel::paginate(10, 1);

        $this->assertSame(0, $result->total);
        $this->assertSame(1, $result->lastPage);
        $this->assertSame(0, $result->items->count());
        $this->assertFalse($result->hasMorePages());
    }

    public function testPaginatePageBeyondLastPage(): void
    {
        $this->seedPages(5);
        $result = PageModel::paginate(10, 99);
        $this->assertSame(0, $result->items->count());
    }
}
