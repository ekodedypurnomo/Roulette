<?php
/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Roulette;

use Roulette\Base;
use Roulette\Collection;
use Roulette\Model\Cache;
use Roulette\Model\Prototype;
use Roulette\Model\Source;
use Roulette\Model\Paginator;
use Roulette\Model\Store;
use Roulette\Model\Field\Field;
use Roulette\Model\Fields;
use Roulette\Model\Association\AssociationAbstract;
use Roulette\Model\Association\HasOne;
use Roulette\Model\Association\HasMany;
use Roulette\Model\Association\BelongsTo;
use Roulette\Model\Association\BelongsToMany;
use Roulette\Model\Policy;
use Roulette\Model\Properties;
use Roulette\Model\ViewOption;
use Roulette\Query\Builder as QueryBuilder;
use Roulette\Query\Operation;
use Roulette\Query\RawExpression;
use Roulette\Exception\ModelNotFoundException;
use Roulette\Exception\ValidationException;
use Roulette\Data\Option as DataOption;
use Roulette\Data\Value as DataValue;
use Roulette\Template;
use Roulette\Model\Concerns\ManagesCache;
use Roulette\Model\Concerns\ManagesScopes;
use Roulette\Model\Concerns\ManagesQueries;
use Roulette\Model\Concerns\ManagesPersistence;
use Roulette\Model\Concerns\ManagesRelations;
use Roulette\Model\Concerns\ManagesBulkOps;
use Roulette\Model\Concerns\ManagesAttributes;
use Roulette\Model\Concerns\ManagesIncrements;
use Roulette\Model\Concerns\ManagesEvents;
use Roulette\Query\ModelQueryBuilder;

/**
 * Base class for all ORM models. Extend this class to represent a database table.
 *
 * Each subclass must implement a static `init()` method that calls `static::prototype()`
 * to declare the table name, primary key, fields, associations, and policies.
 *
 * CRUD:
 * - `new Model($data)` / `save()` — create or update a record
 * - `Model::load($id)` — fetch one record by primary key
 * - `Model::find($conditions)` — fetch a Store of matching records
 * - `destroy()` — delete the record
 *
 * Field lifecycle (per field, per save cycle):
 * reader → default → raw → converter → validator → writer → DB
 *
 * Associations — declare in prototype(), access via `lookup()`:
 * - `hasOne` / `hasMany` — this model owns the foreign key on the related table
 * - `belongsTo` — this model holds the foreign key
 *
 * Policies — declare callables in prototype() and check via an Actor:
 * `$actor->can('edit', $record)`
 *
 * Modified tracking: `getModified()` returns fields changed since last load/save.
 *
 * @package \Roulette
 * @since Version 0.1.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Model extends Base
{
    use ManagesCache;
    use ManagesScopes;
    use ManagesQueries;
    use ManagesPersistence;
    use ManagesRelations;
    use ManagesBulkOps;
    use ManagesAttributes;
    use ManagesIncrements;
    use ManagesEvents;

    // ── Static state ──────────────────────────────────────────────────────────

    static protected ?Prototype $prototype = null;

    // ── Prototype / Schema ─────────────────────────────────────────────────────

    static function prototype(mixed ...$args): mixed
    {
        if (empty($args))
        {
            return static::getPrototype();
        }
        else
        {
            return static::init($args[0]);
        }
    }

    static function getPrototype(array $config = []): Prototype
    {
        if (!(static::$prototype instanceof Prototype))
        {
            static::$prototype = new Prototype($config);
        }
        return static::$prototype;
    }

    static function init(?array $initConfig = null): string
    {
        $prototype = static::getPrototype($initConfig);

        $config = Collection::create($initConfig);

        static::initFields($config);
        static::initAssociations($config);
        static::initSources($config);
        static::initPolicies($config);
        static::initProperties($config);
        static::initViews($config);

        return static::class;
    }

    static function getTable(): mixed
    {
        return static::prototype()->get('table');
    }

    static function setTable(mixed $table = null): string
    {
        static::prototype()->set('table', $table);
        return static::class;
    }

    static function getPrimary(): mixed
    {
        return static::prototype()->get('primary');
    }

    static function setPrimary(mixed $primary = null): string
    {
        static::prototype()->set('primary', $primary);
        return static::class;
    }

    static protected function initFields(Collection $config): string
    {
        $class  = static::class;
        $fields = static::getFields()->reset();

        Collection::create($config->get('fields'))->each(function($value, $i, $all) use($class, $fields)
        {
            if (!($value instanceof Field))
            {
                if (is_string($value))
                {
                    $value = ['name' => $value];
                }
                $value = Collection::with($value, function($c) use($i)
                {
                    if (!$c->has('name') && !empty($i))
                    {
                        $c->set('name', $i);
                    }
                });
                $f = new Field($value);
            }
            $f->setModel($class);
            $fields->add($f);
        });

        return $class;
    }

    static function getField(mixed $field = null): mixed
    {
        return static::getFields()->get($field);
    }

    static function addField(mixed ...$args): string
    {
        static::getFields()->add(...$args);
        return static::class;
    }

    static function removeField(mixed ...$args): string
    {
        static::getFields()->remove(...$args);
        return static::class;
    }

    static function getFields(): Fields
    {
        $prototype = static::prototype();

        if (!($prototype->get('fields') instanceof Fields))
        {
            $prototype->set('fields', new Fields());
        }

        return $prototype->get('fields');
    }

    static function generateId(mixed $salt = ""): mixed
    {
        $prototype = static::prototype();

        if ($prototype->has('idGenerator', true))
        {
            $idGenerator = $prototype->get('idGenerator');
            $generatedId = null;

            if (is_callable($idGenerator))
            {
                $generatedId = $idGenerator($salt, static::class);
            }

            return $generatedId;
        }

        return md5(static::class . microtime(true) . mt_rand() . $salt);
    }

    static function isUseAutoId(): bool
    {
        $prototype = static::prototype();
        return (bool) $prototype->get('autoId');
    }

    // ── Associations ───────────────────────────────────────────────────────────

    static protected function initAssociations(Collection $config): string
    {
        $class        = static::class;
        $associations = static::getAssociations()->reset();

        Collection::create($config->get('associations'))->each(function($v, $name, $all) use($class, $associations)
        {
            $a = null;
            if (!($v instanceof AssociationAbstract))
            {
                $v = Collection::create($v)->setIfNot([
                    'name' => $name,
                    'type' => 'hasOne'
                ]);
                $type = strtoupper((string) $v->get('type'));

                if ($type === HasMany::TYPE)
                {
                    $a = HasMany::create($v->getAll(['except' => ['type']]));
                }
                elseif ($type === HasOne::TYPE)
                {
                    $a = HasOne::create($v->getAll(['except' => ['type']]));
                }
                elseif ($type === BelongsTo::TYPE)
                {
                    $a = BelongsTo::create($v->getAll(['except' => ['type']]));
                }
                elseif ($type === BelongsToMany::TYPE)
                {
                    $a = BelongsToMany::create($v->getAll(['except' => ['type']]));
                }
            }

            $a->setPivot($class);
            $associations->set($a->getName(), $a);
        });

        return static::class;
    }

    static function getAssociations(): Collection
    {
        $prototype = static::prototype();

        if (!($prototype->get('associations') instanceof Collection))
        {
            $prototype->set('associations', new Collection());
        }

        return $prototype->get('associations');
    }

    static function getAssociation(mixed $associationName = null): mixed
    {
        return static::getAssociations()->get($associationName);
    }

    // ── Sources ────────────────────────────────────────────────────────────────

    static protected function initSources(Collection $config): string
    {
        $class      = static::class;
        $dataSource = static::getDataSources()->reset();

        Collection::create($config->get('sources'))->each(function($value, $i, $all) use($class, $dataSource)
        {
            $name = $i;
            if (!($value instanceof Source))
            {
                if (is_string($value))
                {
                    $value = ['table' => $value];
                }
                $value = Collection::with($value, function($c) use(&$name)
                {
                    if (!$c->has('table') && !empty($name))
                    {
                        $c->set('table', $name);
                    }

                    if ($c->has('name'))
                    {
                        $name = $c->get('name');
                        $c->reject('name');
                    }
                });
                $source = new Source($value);
            }
            $source->setModel($class);
            $dataSource->set($name, $source);
        });

        $defaultSource = new Source(['table' => static::getTable()]);
        $defaultSource->setModel($class);
        $dataSource->set($defaultSource->getTable(), $defaultSource);

        return static::class;
    }

    static function getDataSources(): Collection
    {
        $prototype = static::prototype();

        if (!($prototype->get('sources') instanceof Collection))
        {
            $prototype->set('sources', new Collection());
        }

        return $prototype->get('sources');
    }

    static function getDataSource(mixed $sourceName = null): mixed
    {
        $dataSource = static::getDataSources();
        $source     = $dataSource->get($sourceName);

        if (is_null($sourceName) && !is_null(static::getTable()))
        {
            return $dataSource->get(static::getTable());
        }

        return $source;
    }

    static function source(mixed ...$args): mixed
    {
        return static::getDataSource(...$args);
    }

    // ── Policies ───────────────────────────────────────────────────────────────

    static protected function initPolicies(Collection $config): string
    {
        $class    = static::class;
        $policies = static::getPolicies()->reset();

        Collection::create($config->get('policies'))->each(function($p, $name, $all) use($class, $policies)
        {
            $p = Policy::create($p);
            if (empty($name))
            {
                $name = $p->getName();
            }
            $class::setPolicy($name, $p);
        });

        return static::class;
    }

    static function getPolicies(): Collection
    {
        $prototype = static::prototype();
        $policies  = $prototype->get('policies');

        if (!$policies || !($policies instanceof Collection))
        {
            $policies = new Collection($policies);
            $prototype->set('policies', $policies);
        }

        return $policies;
    }

    static function getPolicy(mixed $name = null): mixed
    {
        return static::getPolicies()->get($name);
    }

    static function setPolicy(mixed $name, mixed $function = null): string
    {
        $policies = static::getPolicies();

        $policy = new Policy($name, $function);
        $policies->set($name, $policy);

        return static::class;
    }

    static function isUsePolicy(): bool
    {
        return !static::getPolicies()->isEmpty();
    }

    // ── Data Views ─────────────────────────────────────────────────────────────

    static function initViews(Collection $config): string
    {
        $class = static::class;
        $views = static::getDataViews()->reset();

        Collection::create($config->get('views'))->each(function($value, $i, $all) use($class, $views)
        {
            $name = $i;
            if (!($value instanceof ViewOption))
            {
                $view = new ViewOption($value);
                if (property_exists($view, 'name'))
                {
                    $name = $view->name;
                    unset($view->name);
                }
                if (is_numeric($name) && is_string($value))
                {
                    $name = $value;
                }
            }

            $views->set($name, $view);
        });

        return static::class;
    }

    static function getDataViews(): Collection
    {
        $prototype = static::prototype();

        if (!($prototype->get('views') instanceof Collection))
        {
            $prototype->set('views', new Collection());
        }

        return $prototype->get('views');
    }

    static function getDataView(mixed $viewName = null): mixed
    {
        return static::getDataViews()->get($viewName);
    }

    static function view(mixed $viewName = null): mixed
    {
        return static::getDataView($viewName);
    }

    static function setDataView(mixed $name, mixed $view): string
    {
        static::getDataViews()->set($name, $view);
        return static::class;
    }

    // ── Properties ─────────────────────────────────────────────────────────────

    static function initProperties(Collection $config): string
    {
        $properties = static::getProperties()->reset();

        Collection::create($config->get('properties'))->each(function($value, $name) use($properties) {
            $properties->set($name, $value);
        });

        return static::class;
    }

    static function getProperties(): Collection
    {
        $prototype = static::prototype();

        if (!($prototype->get('properties') instanceof Collection))
        {
            $prototype->set('properties', new Collection());
        }

        return $prototype->get('properties');
    }

    // ── New Public API (additive — all old methods still work) ─────────────────

    /**
     * Start a fluent query on this model. Forwards to ModelQueryBuilder.
     * Example: User::where('active', 1)->orderBy('name')->get()
     */
    static function where(mixed $field, mixed $value = null): ModelQueryBuilder
    {
        return static::query()->where($field, $value);
    }

    static function orderBy(mixed $order): ModelQueryBuilder
    {
        return static::query()->orderBy($order);
    }

    static function groupBy(mixed $group): ModelQueryBuilder
    {
        return static::query()->groupBy($group);
    }

    static function take(int $n): ModelQueryBuilder
    {
        return static::query()->take($n);
    }

    static function select(mixed $fields): ModelQueryBuilder
    {
        return static::query()->select($fields);
    }

    /**
     * Forward any unknown static call to ModelQueryBuilder.
     * Allows: User::skip(5)->get(), User::join(...)->get(), etc.
     */
    static function __callStatic(string $method, array $args): ModelQueryBuilder
    {
        return static::query()->$method(...$args);
    }

    /**
     * Instantiate AND persist a record in one call.
     * Distinct from Base::create() which only instantiates without saving.
     */
    static function make(mixed $data = null): static
    {
        $record = new static((array) $data);
        $record->save();
        return $record;
    }

    /**
     * Load a record or throw ModelNotFoundException.
     */
    static function findOrFail(mixed $id): static
    {
        return static::loadOrFail($id);
    }

    /**
     * Magic property read — returns associated records for declared association names.
     * Example: $user->posts  (instead of $user->lookup('posts'))
     */
    function __get(string $name): mixed
    {
        if (static::getAssociation($name)) {
            return $this->lookup($name);
        }
        return null;
    }

    // ── Constructor / Identity ─────────────────────────────────────────────────

    function __construct(mixed $data = null, bool $original = false)
    {
        if (is_object($data)) $data = (array) $data;
        if (is_string($data)) $data = [static::getPrimary() => $data];

        $this->initModelEvents();
        $this->initData($data, $original);

        if ((!$original) && static::isUseAutoId() && !$this->hasId())
        {
            $this->renewId();
        }

        if ($original)
        {
            $this->makeAlive();
        }
    }

    function __toString(): string
    {
        return $this->getId();
    }
}

class_alias('Roulette\Model', 'Roulette\Record');
