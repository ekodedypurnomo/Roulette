<?php

declare(strict_types=1);

namespace Roulette\Tests\Model;

use Roulette\Model;
use Roulette\Model\Prototype;
use Roulette\Schema;
use Roulette\Tests\Support\DbTestCase;

/**
 * Inline fixture: person model with first/last name stored, and a computed
 * 'full_name' field that concatenates them without touching the database.
 */
class PersonModel extends Model
{
    static protected ?Prototype $prototype = null;
    static protected bool $useCache = false;
}

class ComputedFieldTest extends DbTestCase
{
    public static function setUpBeforeClass(): void
    {
        PersonModel::prototype([
            'table'   => 'people',
            'primary' => 'id',
            'autoId'  => true,
            'fields'  => [
                ['name' => 'id', 'update' => false],
                ['name' => 'first_name', 'type' => 'string'],
                ['name' => 'last_name',  'type' => 'string'],
                [
                    'name'    => 'full_name',
                    'compute' => fn($record) => trim($record->get('first_name') . ' ' . $record->get('last_name')),
                ],
            ],
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Schema::migrate(PersonModel::class);
    }

    public function testComputedFieldReturnsValue(): void
    {
        $person = new PersonModel(['first_name' => 'Ada', 'last_name' => 'Lovelace']);

        $this->assertSame('Ada Lovelace', $person->get('full_name'));
    }

    public function testComputedFieldReflectsCurrentState(): void
    {
        $person = new PersonModel(['first_name' => 'Alan', 'last_name' => 'Turing']);

        $this->assertSame('Alan Turing', $person->get('full_name'));

        $person->set('first_name', 'Sir Alan');

        $this->assertSame('Sir Alan Turing', $person->get('full_name'));
    }

    public function testComputedFieldNotPersistedToDb(): void
    {
        $person = new PersonModel(['first_name' => 'Grace', 'last_name' => 'Hopper']);
        $person->save();

        $cols = $this->tunel->getPdo()
            ->query('PRAGMA table_info(people)')
            ->fetchAll(\PDO::FETCH_ASSOC);

        $colNames = array_column($cols, 'name');
        $this->assertNotContains('full_name', $colNames);
    }

    public function testComputedFieldNotInSelectQuery(): void
    {
        $person = new PersonModel(['first_name' => 'Linus', 'last_name' => 'Torvalds']);
        $person->save();

        $found = PersonModel::find();
        $this->assertCount(1, $found);
        $this->assertSame('Linus Torvalds', $found->first()->get('full_name'));
    }

    public function testComputedFieldWorksAfterLoad(): void
    {
        $person = new PersonModel(['first_name' => 'Bjarne', 'last_name' => 'Stroustrup']);
        $person->save();

        $loaded = PersonModel::load($person->getId());
        $this->assertSame('Bjarne Stroustrup', $loaded->get('full_name'));
    }

    public function testSchemaSqlExcludesComputedField(): void
    {
        $sql = Schema::sql(PersonModel::class);

        $this->assertStringContainsString('first_name', $sql);
        $this->assertStringContainsString('last_name', $sql);
        $this->assertStringNotContainsString('full_name', $sql);
    }
}
