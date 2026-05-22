<?php

declare(strict_types=1);

namespace Roulette\Tests;

use Roulette\Collection;

class CollectionTest extends TestCase
{
    public function testCreate(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'create'));

        $obj = Collection::create();
        $this->assertInstanceOf(Collection::class, $obj, 'return a new Collection');
    }

    public function testIsAssoc(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'isAssoc'));

        $this->assertTrue(Collection::isAssoc([0 => '0', 1 => '1', 'c' => '2']), 'true assoc');
        $this->assertFalse(Collection::isAssoc(['0', '1', '2']), 'numeric array');
        $this->assertFalse(Collection::isAssoc(['0' => '0', '1' => '1', '2' => '2']), 'string numeric array');
    }

    public function testEnum(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'enum'));

        $this->assertSame('1', Collection::enum('1', ['1', '2']), 'valid array');
        $this->assertNull(Collection::enum(1, ['1', '2'], null, true), 'valid array strict mode');
        $this->assertNull(Collection::enum('-1', []), 'empty array without fallback');
        $this->assertSame('0', Collection::enum('-1', [], '0'), 'empty array with fallback');
        $this->assertSame('0', Collection::enum('-1', ['0', '1', '2'], '0'), 'array with fallback');
    }

    public function testSet(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'set'));

        $obj = new Collection();
        $obj->set('one', 1);
        $this->assertSame(1, $obj->get('one'), 'plain mode');

        $obj->set('two');
        $this->assertNull($obj->get('two'), 'incomplete params');

        $obj->set(['three' => 3, 'four' => 4]);
        $this->assertSame(3, $obj->get('three'), 'array set, three');
        $this->assertSame(4, $obj->get('four'), 'array set, four');

        $obj->set(['one' => 'one']);
        $obj->set('two', 'two');
        $this->assertSame('one', $obj->get('one'), 'replace existing, one');
        $this->assertSame('two', $obj->get('two'), 'replace existing, two');

        $this->assertSame(4, $obj->getCount(), 'count after set');
    }

    public function testSetIf(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'setIf'));

        $obj = new Collection(['one' => 1, 'two' => 2, 'three' => 3]);
        $obj->setIf('two', 99);
        $this->assertSame(99, $obj->get('two'), 'updates existing key');
        $obj->setIf('five', 5);
        $this->assertFalse($obj->has('five'), 'does not add missing key');
    }

    public function testSetIfNot(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'setIfNot'));

        $obj = new Collection(['one' => 1, 'two' => 2, 'three' => 3]);
        $obj->setIfNot('one', 99);
        $this->assertSame(1, $obj->get('one'), 'does not overwrite existing key');
        $obj->setIfNot('five', 5);
        $this->assertSame(5, $obj->get('five'), 'adds missing key');
    }

    public function testFill(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'fill'));

        $obj = new Collection();

        $array = $obj->fill()->getAll();
        $this->assertEmpty($array, 'empty value');

        $array = $obj->fill(1, 'b')->getAll();
        $this->assertSame(1, $array['b'], 'valid and exact value');

        $array = $obj->fill(0, ['c', 'd'])->getAll();
        $this->assertSame(0, $array['c'], 'array value, c');
        $this->assertSame(0, $array['d'], 'array value, d');
    }

    public function testFillIf(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'fillIf'));

        $obj = new Collection(['a' => null]);
        $array = $obj->fillIf(1, 'a')->getAll();
        $this->assertSame(1, $array['a'], 'exist key');

        $array = $obj->fillIf(1, 'b')->getAll();
        $this->assertArrayNotHasKey('b', $array, 'key not added');

        $array = $obj->fillIf(0, ['a', 'b'])->getAll();
        $this->assertSame(0, $array['a'], 'mixed array, a');
        $this->assertArrayNotHasKey('b', $array, 'mixed array, b absent');
    }

    public function testFillIfNot(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'fillIfNot'));

        $obj = new Collection(['a' => null]);
        $array = $obj->fillIfNot(1, 'a')->getAll();
        $this->assertEquals(1, $array['a'], 'exist key');

        $obj = new Collection(['a' => null]);
        $array = $obj->fillIfNot(1, 'a', false)->getAll();
        $this->assertNull($array['a'], 'exist key strict');

        $array = $obj->fillIfNot(1, 'b')->getAll();
        $this->assertArrayHasKey('b', $array, 'key added');
        $this->assertSame(1, $array['b'], 'key value');

        $array = $obj->fillIfNot(0, ['a', 'b'])->getAll();
        $this->assertEquals(0, $array['a'], 'mixed array, a');
        $this->assertArrayHasKey('b', $array, 'mixed array, b present');
        $this->assertSame(1, $array['b'], 'mixed array, b value unchanged');
    }

    public function testGet(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'get'));

        $obj = new Collection(['one' => 1, 'two' => 2, 'three' => 3]);
        $this->assertSame(1, $obj->get('one'), 'valid params');
        $this->assertNull($obj->get(), 'empty params');
        $this->assertNull($obj->get('x'), 'invalid params');
        $this->assertSame(1, $obj->get('x', 1), 'invalid params, with fallback');

        $data = $obj->get(['one', 'two']);
        $this->assertSame(1, $data['one'], 'array string params, one');
        $this->assertSame(2, $data['two'], 'array string params, two');
    }

    public function testAdd(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'add'));

        $obj = new Collection();
        $added = $obj->add('one');
        $this->assertSame($obj, $added, 'return value');
        $this->assertTrue($added->contain('one'), 'added');
    }

    public function testGetCount(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'getCount'));

        $obj = new Collection();
        $obj->add('item1');
        $obj->add('item2');
        $this->assertSame(2, $obj->getCount(), 'count');

        $objNonblank = new Collection(['one' => 1]);
        $this->assertSame(1, $objNonblank->getCount(), 'nonblank collection');

        $objBlank = new Collection();
        $this->assertSame(0, $objBlank->getCount(), 'blank collection');
    }

    public function testGetKeys(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'getKeys'));

        $obj = new Collection(['one' => 1, 'two' => 2]);
        $keys = $obj->getKeys();
        $this->assertIsArray($keys, 'is array');
        $this->assertCount(2, $keys, 'count');
        $this->assertSame('one', $keys[0], 'key 0');
        $this->assertSame('two', $keys[1], 'key 1');
    }

    public function testGetValues(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'getValues'));

        $obj = new Collection(['one' => 1, 'two' => 2]);
        $values = $obj->getValues();
        $this->assertIsArray($values, 'is array');
        $this->assertCount(2, $values, 'count');
        $this->assertSame(1, $values[0], 'value 0');
        $this->assertSame(2, $values[1], 'value 1');
    }

    public function testGetAll(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'getAll'));

        $obj = new Collection(['one' => 1, 'two' => 2]);
        $data = $obj->getAll();
        $this->assertCount(2, $data, 'count');
        $this->assertSame(1, $data['one'], 'valid data item');
    }

    public function testReset(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'reset'));

        $obj = new Collection(['one' => 1, 'two' => 2]);
        $obj->reset();
        $this->assertSame(0, $obj->getCount(), 'default');
    }

    public function testClear(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'clear'));

        $obj = new Collection(['one' => 1, 'two' => 2]);
        $obj->clear();
        $this->assertNull($obj->get('one'), 'values nulled');
        $this->assertNull($obj->get('two'), 'values nulled');
        $this->assertSame(2, $obj->getCount(), 'keys retained (count unchanged)');
    }

    public function testClean(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'clean'));

        $obj = new Collection(['one' => 1, 'two' => 2]);
        $obj->clear();
        $obj->clean();
        $this->assertSame(0, $obj->getCount(), 'null values removed after clean');

        $obj2 = new Collection(['a' => 1, 'b' => null, 'c' => 3]);
        $obj2->clean();
        $this->assertSame(2, $obj2->getCount(), 'only null items removed');
        $this->assertFalse($obj2->has('b', false), 'null key gone');
    }

    public function testContain(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'contain'));

        $obj = new Collection(['one' => 1, 'two' => 2]);
        $this->assertTrue($obj->contain('1'), 'valid item');
        $this->assertFalse($obj->contain('1', true), 'invalid item and strict');
        $this->assertFalse($obj->contain('six'), 'invalid item');
        $this->assertFalse($obj->contain([]), 'wrong params');
        $this->assertFalse($obj->contain(), 'wrong params 2');
    }

    public function testHas(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'has'));

        $obj = new Collection(['one' => 1, 'two' => 2, 'three' => null]);
        $this->assertTrue($obj->has('one'), 'valid item');
        $this->assertFalse($obj->has('three'), 'invalid item (null)');
        $this->assertFalse($obj->has('three', true), 'invalid item strict');
        $this->assertTrue($obj->has('three', false), 'invalid item unstrict');
        $this->assertFalse($obj->has([]), 'array');
        $this->assertFalse($obj->has(), 'wrong params');
        $this->assertFalse($obj->has((object) []), 'wrong params 2');
    }

    public function testEach(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'each'));

        $obj = new Collection(['one' => 1, 'two' => 2]);

        $this->assertTrue($obj->each(function () {}), 'invalid params');

        $x = [];
        $obj->each(function ($item, $key, $data, $instance) use (&$x) {
            $x[$key] = $item;
        });
        $this->assertSame($obj->getAll(), $x, 'valid callable');

        $x = [];
        $obj->each(function ($key, $item, $data, $instance) use (&$x) {
            $x[$key] = $item;
            return false;
        });
        $this->assertCount(1, $x, 'valid callable and stop on first');
    }

    public function testFilter(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'filter'));

        $obj = new Collection(['one' => 1, 'two' => 2]);
        $filtered = $obj->filter(function () {
            return true;
        });
        $this->assertSame($obj->getCount(), $filtered->getCount(), 'empty params');
    }

    public function testReject(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'reject'));
        $obj = new Collection(['one' => 1, 'two' => 2, 'three' => 3]);
        $result = $obj->reject(fn($key, $val) => true);
        $this->assertIsArray($result, 'reject returns array');
    }

    public function testFirst(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'first'));

        $obj = new Collection(['one' => 1, 'two' => 2]);
        $all = $obj->getAll();
        $this->assertSame(reset($all), $obj->first(), 'default');
    }

    public function testLast(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'last'));

        $obj = new Collection(['one' => 1, 'two' => 2]);
        $all = $obj->getAll();
        $this->assertSame(end($all), $obj->last(), 'default');
    }

    public function testRemove(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'remove'));

        $obj = new Collection(['one' => 1, 'two' => 2, 'three' => 3, 'four' => 4]);

        $this->assertSame('one', $obj->remove(1), 'remove returns key');
        $all = $obj->getAll();
        $this->assertSame(2, reset($all), 'after remove one');

        $this->assertSame(2, $obj->removeOn('two'), 'removeOn returns value');
        $all = $obj->getAll();
        $this->assertSame(3, reset($all), 'after removeOn');

        $removed = $obj->removeIf(3);
        $this->assertEquals(['three' => 3], $removed, 'removeIf returns removed');
        $all = $obj->getAll();
        $this->assertSame(4, reset($all), 'after removeIf');

        $removed = $obj->removeIfNot(3);
        $this->assertEquals(['four' => 4], $removed, 'removeIfNot returns removed');
        $all = $obj->getAll();
        $this->assertCount(0, $all, 'after removeIfNot');

        $obj = new Collection(['one' => 1, 'two' => 2, 'three' => 3, 'four' => 4]);
        $removed = $obj->removeIn([1, 2]);
        $this->assertEquals(['one' => 1, 'two' => 2], $removed, 'removeIn array params returns removed');
        $this->assertCount(2, $obj->getAll(), 'after removeIn array');

        $removed = $obj->removeIn(3);
        $this->assertEquals(['three' => 3], $removed, 'removeIn single returns removed');
        $this->assertCount(1, $obj->getAll(), 'after removeIn single');

        $obj = new Collection(['one' => 1, 'two' => 2, 'three' => 3, 'four' => 4]);
        $this->assertSame(2, $obj->removeKey('two'), 'removeKey');

        $obj = new Collection(['one' => 1, 'two' => 2, 'three' => 3, 'four' => 4]);
        $obj->removeIn([3]);
        $this->assertCount(3, $obj, 'removeIn count');

        $obj = new Collection(['one' => 1, 'two' => 2, 'three' => 3, 'four' => 4]);
        $obj->removeBy([3]);
        $this->assertCount(3, $obj, 'removeBy count');
    }

    public function testRemoveEx(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'removeEx'));

        $obj = new Collection(['one' => 1, 'two' => 2, 'three' => 3, 'four' => 4]);
        $removed = $obj->removeEx([3, 4]);
        $this->assertEquals(['one' => 1, 'two' => 2], $removed, 'array params, returned');
        $this->assertCount(2, $obj->getAll(), 'array params, remaining');

        $removed = $obj->removeEx(2);
        $this->assertEquals(['three' => 3, 'four' => 4], $removed, 'single params, returned');
        $this->assertCount(0, $obj->getAll(), 'single params, remaining');

        $obj = new Collection(['one' => 1, 'two' => 2, 'three' => 3, 'four' => 4]);
        $this->assertNotFalse($obj->removeEx(), 'remove all');

        $obj = new Collection(['one' => 1, 'two' => 2, 'three' => 3, 'four' => 4]);
        $this->assertNotFalse($obj->removeEx([1]), 'ex one');
    }

    public function testRemap(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'remap'));

        $obj = new Collection(['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D']);
        $obj->remap(['a' => 'A', 'b' => 'B']);
        $this->assertSame('A', $obj->get('A'), 'A');
        $this->assertSame('B', $obj->get('B'), 'B');
    }

    public function testSum(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'sum'));

        $obj = new Collection([1, 2, 3, 4]);
        $this->assertEquals(10, $obj->sum(), 'default');
    }

    public function testMin(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'min'));

        $obj = new Collection([1, 2, 3, 4]);
        $this->assertEquals(1, $obj->min(), 'default');
    }

    public function testMax(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'max'));

        $obj = new Collection([1, 2, 3, 4]);
        $this->assertEquals(4, $obj->max(), 'default');
    }

    public function testAverage(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'average'));

        $obj = new Collection([1, 2, 3, 4]);
        $this->assertEquals(2.5, $obj->average(), 'default');
    }

    public function testIsEmpty(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'isEmpty'));

        $obj = new Collection();
        $this->assertTrue($obj->isEmpty(), 'default');

        $obj = new Collection([1, 2, 3, 4]);
        $this->assertFalse($obj->isEmpty(), 'value');
    }

    public function testGetAt(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'getAt'));

        $obj = new Collection(['A', 'F', 'G', 'H']);
        $this->assertEquals('A', $obj->getAt(), 'default');
        $this->assertEquals('G', $obj->getAt(2), 'valid, value');
    }

    public function testGetFirst(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'getFirst'));

        $obj = new Collection(['A', 'F', 'G', 'H']);
        $this->assertEquals('A', $obj->getFirst(), 'default');
    }

    public function testGetLast(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'getLast'));

        $obj = new Collection(['A', 'F', 'G', 'H']);
        $this->assertEquals('H', $obj->getLast(), 'default');
    }

    public function testToArray(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'toArray'));

        $obj = new Collection(['A', 'F', 'G', 'H']);
        $this->assertNotEmpty($obj->toArray(), 'array value');

        $obj = new Collection(1);
        $this->assertNotEmpty($obj->toArray(), 'integer value');

        $obj = new Collection('A');
        $this->assertNotEmpty($obj->toArray(), 'string value');
    }

    public function testToJson(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'toJson'));

        $obj = new Collection(['A', 'F', 'G', 'H']);
        $this->assertEquals(['A', 'F', 'G', 'H'], json_decode($obj->toJson()), 'default');
    }

    public function testHasKey(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'hasKey'));

        $obj = new Collection(['A' => 1, 'F' => 5, 'G' => 8, 'H' => 10]);
        $this->assertTrue($obj->hasKey('A'), 'default');
    }

    public function testHasItem(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'hasItem'));

        $obj = new Collection(['A' => 1, 'F' => 5, 'G' => 8, 'H' => 10]);
        $this->assertTrue($obj->hasItem(1), 'default');
    }

    public function testIterable(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'iterable'));

        $obj = new Collection(['A' => 1, 'F' => 5, 'G' => 8, 'H' => 10]);
        $this->assertCount(0, $obj->iterable(), 'default, null');
        $this->assertIsArray($obj->iterable([1, 2, 3]), 'valid, array');
    }

    public function testSort(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'sort'));

        $obj = new Collection([3, 1, 4, 1, 5, 9, 2]);
        $obj->sort();
        $values = $obj->getValues();
        $this->assertSame([1, 1, 2, 3, 4, 5, 9], $values, 'ascending order');
    }

    public function testAddAll(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'addAll'));

        $obj = new Collection([9]);
        $this->assertCount(2, $obj->addAll(2), 'default, value');
        $this->assertCount(3, $obj->addAll([3]), 'valid, array');
    }

    public function testContainIn(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'containIn'));

        $obj = new Collection(['A' => 1, 'F' => 5, 'G' => 8, 'H' => 10]);
        $this->assertFalse($obj->containIn(), 'invalid, null');

        $obj = new Collection([1, 3]);
        $this->assertTrue($obj->containIn([1]), 'valid, value');
        $this->assertFalse($obj->containIn([9]), 'invalid, value');
    }

    public function testShuffle(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'shuffle'));

        $obj = new Collection([1, 3, 9]);
        $obj->shuffle();
        $this->assertSame(3, $obj->count(), 'count preserved after shuffle');
    }

    public function testDiff(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'diff'));

        $obj = new Collection([1, 3, 9]);
        $this->assertNotFalse($obj->diff(1), 'default');
    }

    public function testDiffKeys(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'diffKeys'));

        $obj = new Collection([1, 3, 9]);
        $this->assertNotFalse($obj->diffKeys(1), 'default');
    }

    public function testInvoke(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'invoke'));

        $obj = new Collection([1, 3, 9]);
        $result = $obj->invoke(fn($item) => $item * 2);
        $this->assertSame([2, 6, 18], $result->getValues(), 'doubles each item');
    }

    public function testEvery(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'every'));

        $obj = new Collection([2, 4, 6]);
        $this->assertTrue($obj->every(fn($item) => $item % 2 === 0), 'all even → true');
        $this->assertFalse($obj->every(fn($item) => $item > 3), 'not all > 3 → false');
    }

    public function testSome(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'some'));

        $obj = new Collection([1, 2, 3]);
        $this->assertTrue($obj->some(fn($item) => $item === 2), 'has 2 → true');
        $this->assertFalse($obj->some(fn($item) => $item === 99), 'no 99 → false');
    }

    public function testChunk(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'chunk'));

        $obj = new Collection([1, 2, 3, 4]);
        $chunks = $obj->chunk(2);
        $this->assertSame(2, $chunks->count(), '4 items in chunks of 2 → 2 chunks');
    }

    public function testFlip(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'flip'));

        $obj = new Collection(['a' => 1, 'b' => 2]);
        $flipped = $obj->flip();
        $this->assertSame('a', $flipped->get(1), 'key 1 maps to "a"');
        $this->assertSame('b', $flipped->get(2), 'key 2 maps to "b"');
    }

    public function testImplode(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'implode'));

        $obj = new Collection([1, 2, 5, 8]);
        $this->assertSame('1,2,5,8', $obj->implode(','), 'comma-joined values');
    }

    public function testIntersect(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'intersect'));

        $obj = new Collection(['Desk', 'Sofa', 'Chair']);
        $this->assertCount(2, $obj->intersect(['Desk', 'Chair', 'Bookcase']), 'default');
    }

    public function testReverse(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'reverse'));

        $obj = new Collection(['one' => 1, 'two' => 2, 'three' => 3]);
        $reversed = $obj->reverse();
        $this->assertSame(['three' => 3, 'two' => 2, 'one' => 1], $reversed->getAll(), 'order reversed');
    }

    public function testRandom(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'random'));

        $obj = new Collection([1, 2, 5, 8]);
        $this->assertNotFalse($obj->random(), 'default');
    }

    public function testCount(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'count'));

        $obj = new Collection([1, 2, 5, 8]);
        $this->assertSame(4, $obj->count(), 'default');
    }

    public function testGetIterator(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'getIterator'));

        $obj = new Collection([1, 2, 5, 8]);
        $iter = $obj->getIterator();
        $this->assertInstanceOf(\Traversable::class, $iter);
    }

    public function testJsonSerialize(): void
    {
        $this->assertTrue(method_exists(Collection::class, 'jsonSerialize'));

        $obj = new Collection([1, 2, 5, 8]);
        $this->assertSame([1, 2, 5, 8], $obj->jsonSerialize());
    }

    public function testConstruct(): void
    {
        $this->assertTrue(method_exists(Collection::class, '__construct'));

        $obj = new Collection([1, 2, 5, 8]);
        $this->assertSame(4, $obj->count(), 'count from array');
    }

    public function testToString(): void
    {
        $this->assertTrue(method_exists(Collection::class, '__toString'));

        $obj = new Collection([1, 2, 5, 8]);
        $this->assertIsString((string) $obj);
    }
}
