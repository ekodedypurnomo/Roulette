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
use Roulette\Model\Store;
use Roulette\Model\Field\Field;
use Roulette\Model\Fields;
use Roulette\Model\Association\AssociationAbstract;
use Roulette\Model\Association\HasOne;
use Roulette\Model\Association\HasMany;
use Roulette\Model\Association\BelongsTo;
use Roulette\Model\Operation\Rights;
use Roulette\Model\Policy;
use Roulette\Model\Properties;
use Roulette\Model\ViewOption;
use Roulette\Query\Builder as QueryBuilder;
use Roulette\Query\Operation;
use Roulette\Exception\ModelNotFoundException;
use Roulette\Exception\ValidationException;
use Roulette\Data\Option as DataOption;
use Roulette\Data\Value as DataValue;
use Roulette\Actor;
use Roulette\Template;

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
    /**
     * @ignore
     */
    static protected ?Prototype $prototype = null;

    /**
     * Scopes disabled for the next find()/load() call, keyed by class name.
     * Consumed (cleared) after each query so they never leak across calls.
     */
    static private array $_pendingDisabledScopes = [];

    /**
     * @ignore
     */
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
        # prototyping by config
        $prototype = static::getPrototype($initConfig);

        # need to reconfigure several configs
        $config = Collection::create($initConfig);

        static::initFields($config);
        static::initAssociations($config);
        static::initSources($config);
        static::initPolicies($config);
        static::initProperties($config);
        static::initViews($config);

        return static::class;
    }

    /**
     * @ignore
     */
    static protected bool $useCache = true;

    /**
     * @ignore
     */
    static function isUseCache(): bool
    {
        return !!static::$useCache;
    }

    /**
     * [formatCacheId description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    static function formatCacheId(mixed $id): string
    {
        return get_class().'\\'.static::class.'-'.$id;
    }

    /**
     * [storeToCache description]
     * @param  [type] $record [description]
     * @return [type]         [description]
     */
    static function storeToCache(Model $record): string
    {
        # only approve for record from database (hasId)
        if (static::isUseCache() && $record->hasId())
        {
            Cache::store(static::formatCacheId($record->getId()), $record);
        }
        return static::class;
    }

    /**
     * [fetchFromCache description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    static function fetchFromCache(mixed $recordId): mixed
    {
        if (!static::isUseCache()) return null;

        if (!(is_string($recordId) || is_numeric($recordId))) return null;

        return Cache::fetch(static::formatCacheId($recordId));
    }

    /**
     * Get the name of table from DB that associated to the model
     * @return String
     */
    static function getTable(): mixed
    {
        return static::prototype()->get('table');
    }

    /**
     * Set the table name to assocate the model from DB
     * @param [type] $table [description]
     */
    static function setTable(mixed $table = null): string
    {
        static::prototype()->set('table', $table);
        return static::class;
    }

    /**
     * Get the primary field of the model
     *
     *      Example:
     *      $student_model //declaration model firts
     *
     *      $primary = $student_model::getPrimary() === 'id';
     *
     * @return String
     */
    static function getPrimary(): mixed
    {
        return static::prototype()->get('primary');
    }

    /**
     * Set the primary field of the model
     *
     *      Example:
     *      // before set primary, model has primary
     *      // primary => 'id',
     *
     *      $student_model // declaration model firts
     *
     *      $primary = $student_model::setPrimary('name');
     *      //and primary has change it. became 'name'
     *
     * @param String $primary
     */
    static function setPrimary(mixed $primary = null): string
    {
        static::prototype()->set('primary', $primary);
        return static::class;
    }

    ///////////
    // FIELD //
    ///////////

    static protected function initFields(Collection $config): string
    {
        $class = static::class;
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

    /**
     * Get the fields of the model
     * @return array
     */
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

        # default id generator
        return md5(static::class . microtime(true) . mt_rand() . $salt);
    }

    static function isUseAutoId(): bool
    {
        $prototype = static::prototype();
        return (bool) $prototype->get('autoId');
    }

    /**
     * [load description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    static function load(mixed $id = null): mixed
    {
        # load from cache
        if ($_c = static::fetchFromCache($id))
        {
            return $_c;
        }

        # prepare load
        $table = static::getTable();
        $field = array_flip(static::getFields()->filterSelectable()->getSource());
        $condition = static::getFields()->mapToSource(
            is_array($id) ? $id : [static::getPrimary() => $id]
            );
        $disabled = static::consumeDisabledScopes();
        $class = static::class;

        $operation = Operation::create('select')->buildQuery(function($qop) use($table, $field, $condition, $disabled, $class)
        {
            $qop->table($table)
                ->select($field)
                ->where($condition);
            $class::applyScopes($qop, $disabled);
        })->execute();

        if ($operation->getRecord())
        {
            # use __construct instead of create, we need to include `$original = true` in the object
            return new static((array) $operation->getRecord(), $original=true);
        }

        return null;
    }

    /**
     * Like load() but throws ModelNotFoundException if the record does not exist.
     * @throws ModelNotFoundException
     */
    static function loadOrFail(mixed $id = null): static
    {
        $record = static::load($id);
        if ($record === null) {
            throw new ModelNotFoundException(static::class, $id);
        }
        return $record;
    }

    /**
     * [find description]
     * @param  array   $condition [description]
     * @param  [type]  $order     [description]
     * @param  [type]  $take      [description]
     * @param  integer $skip     [description]
     * @param  [type]  $group     [description]
     * @param  [type]  $having    [description]
     * @return [type]             [description]
     */
    static function find(mixed $condition = null, mixed $order = null, mixed $take = null, mixed $skip = null, mixed $group = null, mixed $having = null): Store
    {
        $class = static::class;
        $store = new Store(null, $class);

        $table = static::getTable();
        $field = array_flip(static::getFields()->filterSelectable()->getSource());
        $condition = static::getFields()->mapToSource($condition);
        $disabled = static::consumeDisabledScopes();

        $operation = Operation::create('select')->buildQuery(function($qop) use($table, $field, $condition, $take, $skip, $order, $group, $having, $disabled, $class)
        {
            $qop->table($table)
                ->select($field)
                ->where($condition)
                ->take($take)
                ->skip($skip)
                ->groupBy($group)
                ->having($having);
            $class::applyScopes($qop, $disabled);
        })->execute();

        Collection::create($operation->getRecords())->each(function($v, $k, $all, $c) use($class, $store)
        {
            $r = new $class((array)$v, true);
            $store->add($r);
        });

        return $store;
    }


    static function query(mixed $mode = null): QueryBuilder
    {
        $builder = new QueryBuilder(static::getTable(), $mode);
        return $builder;
    }

    // see laravel eloquent scope for this use of
    static function filter(mixed $registeredFilter = null): string
    {
        return static::class;
    }

    /**
     * Disable one or more named scopes for the immediately following find()/load().
     * Returns the class name so calls can be chained: User::withoutScope('active')::find([]).
     *
     * @param  string|array ...$names  Scope names to skip, or a single array of names.
     * @return string                  The calling class name (for static chaining).
     */
    static function withoutScope(string|array ...$names): string
    {
        $flat = [];
        foreach ($names as $n) {
            $flat = array_merge($flat, is_array($n) ? $n : [$n]);
        }
        self::$_pendingDisabledScopes[static::class] = $flat;
        return static::class;
    }

    /**
     * Disable ALL scopes for the immediately following find()/load().
     *
     * @return string  The calling class name (for static chaining).
     */
    static function withoutScopes(): string
    {
        self::$_pendingDisabledScopes[static::class] = ['*'];
        return static::class;
    }

    /**
     * Consume (read and clear) the pending disabled-scope list for this class.
     * Must be called at the start of every find()/load() so state never carries over.
     */
    private static function consumeDisabledScopes(): array
    {
        $class = static::class;
        $disabled = self::$_pendingDisabledScopes[$class] ?? [];
        unset(self::$_pendingDisabledScopes[$class]);
        return $disabled;
    }

    /**
     * Apply all prototype-declared scopes that are not in the disabled list.
     * A disabled list containing '*' skips every scope.
     *
     * Scopes are declared in the prototype as:
     *   'scopes' => ['active' => fn($qop) => $qop->where(['active' => 1])]
     *
     * @param  mixed  $qop      The query-option builder passed inside buildQuery().
     * @param  array  $disabled Names of scopes to skip (or ['*'] to skip all).
     */
    protected static function applyScopes(mixed $qop, array $disabled = []): void
    {
        if (in_array('*', $disabled)) return;

        $scopes = static::prototype()->get('scopes');
        if (!is_array($scopes)) return;

        foreach ($scopes as $name => $callable) {
            if (in_array($name, $disabled)) continue;
            if (is_callable($callable)) call_user_func($callable, $qop);
        }
    }



    ////////////
    // RECORD //
    ////////////

    /**
     * Indicate if record is exist on the database
     * @var boolean
     */
    protected bool $alive = false;

    /**
     * Stored record in the model
     * @var array
     */
    protected ?Collection $data = null;

    /**
     * [$relation description]
     * @var null
     */
    protected ?Collection $relations = null;

    /**
     * @ignore
     */
    function __construct(mixed $data = null, bool $original = false)
    {
        if (is_object($data)) $data = (array) $data;
        if (is_string($data)) $data = [static::getPrimary() => $data];

        # set up all data
        $this->initData($data, $original);

        if ((!$original) && static::isUseAutoId() && !$this->hasId())
        {
            $this->renewId();
        }

        # then make it alive based on original status
        if ($original)
        {
            $this->makeAlive();
        }
    }

    function __toString(): string
    {
        return $this->getId();
    }

    /**
     * [initData description]
     * @param  array|null $data     [description]
     * @param  boolean    $original [description]
     * @return [type]               [description]
     */
    protected function initData(?array $data = null, bool $original = false): static
    {
        $me = $this;

        $data = Collection::create($data);

        static::getFields()->each(function($f, $i) use($me, $data, $original)
        {
            $value = $data->get($f->getName());

            # does no need use _set, affect original first then revert it instead
            if ($fieldValue = $me->getValue($f->getName()))
            {
                if ($original)
                {
                    $fieldValue->setOriginal($value);
                    // $fieldValue->setValue($fieldValue->getOriginal()); # set raw equal original;
                    $fieldValue->revert(); # use revert instead
                }
                else
                {
                    $fieldValue->setValue($value);
                }
            }
        });

        return $this;
    }

    /**
     * Check the record if it has id
     * @return boolean [description]
     */
    function hasId(): bool
    {
        $id = $this->getId();
        return (is_int($id) || (is_string($id) && !empty($id)));
    }

    /**
     * Get the primary field of the record
     * @return array
     */
    function getId(): mixed
    {
        return $this->get(static::getPrimary());
    }

    /**
     * Set the value for the primary fiel dof the model
     * @param string $id
     */
    function setId(mixed $id = null): static
    {
        $this->set(static::getPrimary(), $id);
        return $this;
    }

    function renewId(mixed $salt = ""): static
    {
        return $this->setId(self::generateId($salt));
    }

    /**
     * an existing record or new record on the model
     * @return [type] [description]
     */
    protected function data(): Collection
    {
        if (!($this->data instanceof Collection))
        {
            $this->data = new Collection();
        }
        return $this->data;
    }

    /**
     * Set the value of the record by specified name
     * @param string $field
     * @param string $value
     */
    function set(mixed $field, mixed $value = null, bool $commit = false, bool $original = false): static
    {
        # bulk operation
        if (is_object($field)) $field = (array) $field;
        if (is_array($field))
        {
            foreach ($field as $f => $v)
            {
                $this->set($f, $v, $commit, $original);
            }
            return $this;
        }

        # single operation
        if ($fieldValue = $this->getValue($field))
        {
            if ($original)
            {
                $fieldValue->setOriginal($value, $revert = false);
            }
            else
            {
                $fieldValue->setValue($value, $commit);
            }
        }

        return $this;
    }

    function getValue(mixed $field = null): mixed
    {
        if ($f = static::getFields()->get($field))
        {
            $data = $this->data();

            if (!$data->hasKey($f->getName())) $data->set($f->getName(), new DataValue($this, $f));

            return $data->get($f->getName());
        }

        return null;
    }

    /**
     * get the record from the specified field name
     * @param  array  $field
     * @param  boolean $render
     * @return array
     */
    function get(mixed $field = null, bool $render = true): mixed
    {
        if (is_array($field))
        {
            $data = [];
            foreach ($field as $key => $alias)
            {
                if (is_numeric($key))
                {
                    $key = $alias;
                }
                $data[$alias] = $this->get($key, $render);
            }
            return $data;
        }

        if ($f = static::getFields()->get($field))
        {
            if ($f->isComputed())
            {
                return call_user_func($f->getCompute(), $this);
            }
        }

        if ($fieldValue = $this->getValue($field))
        {
            return $render ? $fieldValue->getDisplay() : $fieldValue->getRaw();
        }
    }

    /**
     * Get the record from the model
     * @param  array|string|boolean  $options If `Array` or `String` will be assume as options collection, if `Boolean` will be set the render
     * @param  boolean $render Set `false` to get plain value without render
     * @return array
     */
    function getData(mixed $options = null, bool $render = true): array
    {
        $record = $this;
        $options = DataOption::create($options);
        $fields = static::getFields()->resolveName($options->getFields());

        # prepare data
        $data = $record->get($fields, $options->isRender());

        # collect data from each relations
        $options->getRelations()->each(function($option, $key) use($options, &$data, $record)
        {
            $option = DataOption::create($option);
            $associatedResource = $record->lookup($key, $option->isAutoLoad());
            $relatedData = null;
            $display = (empty($option->getDisplay())) ? $key : $option->getDisplay();

            # get data from it record/store
            if ($associatedResource)
            {
                $relatedData = $associatedResource->getData($option);
            }

            # patch into data
            if ($option->isInline())
            {
                $data[$display] = $relatedData;
                return;
            }
            if ($option->isMerge())
            {
                // if user use mergeMask insteadOf field alias
                $mergeMask = $option->getMergeMask();
                $mergeData = [];
                foreach ((array)$relatedData as $k => $v)
                {
                    $k = Template::parse($mergeMask, ['field' => $k, 'value' => $v]);
                    $mergeData[$k] = $v;
                }

                $data = array_merge($data, $mergeData);
                return;
            }
            else
            {
                if (!isset($data['relations']))
                {
                    $data['relations'] = [];
                }

                $data['relations'][$display] = $relatedData;
            }
        });

        return $data;
    }

    /**
     * [getDataToSave description]
     * @param  string  $operationMode [description]
     * @param  boolean $modifiedOnly  [description]
     * @return [type]                 [description]
     */
    function getDataToSave(string $operationMode = 'save', bool $modifiedOnly = false): array
    {
        $operationMode = strtolower($operationMode);

        $dataToSave = [];

        $this->data()->each(function($v, $k) use(&$dataToSave, $operationMode, $modifiedOnly)
        {
            $f = $v->getField();

            # exlude any unchanges if modifiedOnly is true
            if ($modifiedOnly && !$v->isModified())
            {
                return;
            }

            # fetch by its operationMode
            if (
                ($operationMode == 'save' && ($f->isInsertable() || $f->isUpdatable())) ||
                ($operationMode == 'insert' && $f->isInsertable()) ||
                ($operationMode == 'update' && $f->isUpdatable())
            )
            {
                $dataToSave[$f->getSource()] = $v->getWriteValue();
            }
        });
        return $dataToSave;
    }

    /**
     * [getDataToInsert description]
     * @return [type] [description]
     */
    function getDataToInsert(): array
    {
        return $this->getDataToSave('insert', false);
    }

    /**
     * [getDataToUpdate description]
     * @param  boolean $modifiedOnly [description]
     * @return [type]                [description]
     */
    function getDataToUpdate(bool $modifiedOnly = false): array
    {
        return $this->getDataToSave('update', $modifiedOnly);
    }

    /**
     * Check the record whether it's modified or not
     * @return boolean
     */
    function isModified(): bool
    {
        return $this->data()->some(function($data)
        {
            return $data->isModified();
        });
    }

    /**
     * Get the modified field from the record
     * @return array
     */
    function getModified(): array
    {
        $modified = [];
        $this->data()
        ->filter(function($fieldValue, $fieldName)
        {
            return $fieldValue->isModified();
        })
        ->each(function($fieldValue, $fieldName) use(&$modified)
        {
            $modified[] = $fieldName;
        });
        return $modified;
    }

    /**
     * [getErrorMessages description]
     * @param  boolean $grouped [description]
     * @return [type]           [description]
     */
    function getErrorMessages(bool $grouped = false): array
    {
        $errorMessages = [];

        $this->data()
        ->filter(function($fieldValue, $fieldName)
        {
            return !$fieldValue->isValid();
        })
        ->each(function($fieldValue, $fieldName) use(&$errorMessages, $grouped)
        {
            if ($grouped)
            {
                $errorMessages[$fieldName] = $fieldValue->getError();
            }
            else
            {
                $errorMessages = array_merge($errorMessages, $fieldValue->getError());
            }
        });

        return $errorMessages;
    }

    /**
     * Check the record whether it's valid or not
     * @param boolean runvalidate
     * @return boolean
     */
    function isValid(bool $runValidate = false): bool
    {
        if ($runValidate)
        {
            $this->validate();
        }

        $valid = $this->data()->every(function($fieldValue)
        {
            return $fieldValue->isValid();
        });

        return $valid;
    }

    /**
     * Check whether the record is exist in the database or not
     * force recheck will not affect any changes on database into object or vise versa, it will only validate if id is exist.
     *
     * @param  boolean $recheck
     * @return boolean
     */
    function isAlive(bool $recheck = false): bool
    {
        # check will be available if only record has an id
        if ($this->hasId() && $recheck)
        {
            # only renew original values from database, but accept user changes (revert == false)
            $this->reload($revert = false);
        }

        return $this->alive;
    }

    /**
     * [makeAlive description]
     * @param  boolean $alive [description]
     * @return [type]         [description]
     */
    protected function makeAlive(bool $alive = true): static
    {
        $this->alive = !!$alive;

        if ($this->alive)
        {
            static::storeToCache($this);
        }

        return $this;
    }

    /**
     * [reset description]
     * @return [type] [description]
     */
    function reset(): static
    {
        $this->data()->reset();
        return $this;
    }

    /**
     * [revert description]
     * @return [type] [description]
     */
    function revert(): static
    {
        $me = $this;
        $this->data()->each(function($fieldValue)
        {
            $fieldValue->revert();
        });
        return $this;
    }

    /**
     * [commit description]
     * @param boolean $makeAlive [description]
     * @return  [description]
     */
    function commit(bool $makeAlive = false): static
    {
        $this->data()->each(function($data)
        {
            $data->commit();
        });

        if ($makeAlive) $this->makeAlive();

        return $this;
    }

    /**
     * Refetch the record from DB
     * @param  boolean $revert
     * @param  function $callback
     * @return \Roulette/Model
     */
    function reload(mixed $revert = true): static
    {
        if (!$this->hasId()) return $this;

        # if callback as first param
        if (is_callable($revert))
        {
            $callback = $revert;
            $revert = true; # as the default value of revert
        }

        # use operation instead of load to avoid any caching
        $table = static::getTable();
        $field = array_flip(static::getFields()->filterSelectable()->getSource());
        $condition = static::getFields()->mapToSource([
            static::getPrimary() => $this->getId()
        ]);

        $operation = Operation::create('select')->buildQuery(function($opt) use($table, $field, $condition)
        {
            $opt->table($table)
                ->select($field)
                ->where($condition);
        })->execute();

        if ($operation->isSuccess())
        {
            # changes alive status by the record existense
            $this->makeAlive(!!$operation->getRecord());

            $rawRecord = (array)$operation->getRecord(); # parse into array for bulk set

            # update original data with the new from database
            $this->set($rawRecord, $_ignoreit = null, $commit = false, $original = true);

            # revert if needed
            if ($revert)
            {
                $this->revert();
            }
        }

        return $this;
    }

    /**
     * Check the record is valid or not
     * @param  function $callback
     * @return \Roulette/Model
     */
    function validate(mixed $callback = null): static
    {
        $valid = $this->data()->every(function($fieldValue, $fieldName)
        {
            return $fieldValue->validate()->isValid();
        });

        if (is_callable($callback))
        {
            $callback($valid, $this);
        }

        return $this;
    }

    /**
     * Insert record to database
     * @param  function  $callback
     * @param  boolean $validate
     * @param  boolean $recheck
     * @return [type]
     */
    function save(mixed $callback = null, bool $validate = true, bool $recheck = true): mixed
    {
        # validate
        if ($validate)
        {
            if (!$this->validate()->isValid())
            {
                if (is_callable($callback))
                {
                    $callback(false, $this);
                }

                return false;
            }
        }

        $me = $this;
        $class = static::class;
        $table = static::getTable();
        $dataUpdate = $this->getDataToUpdate();
        $dataInsert = $this->getDataToInsert();
        $condition = static::getFields()->mapToSource([
            static::getPrimary() => $this->getId()
        ]);

        # check alive to decide insert/update operation
        # update
        if ($this->isAlive($recheck))
        {
            $operation = Operation::create('update')->buildQuery(function($qop) use($table, $dataUpdate, $condition)
            {
                $qop->table($table)
                    ->where($condition)
                    ->set($dataUpdate);
            })->execute();
        }
        # insert otherwise
        else
        {
            $operation = Operation::create('insert')->buildQuery(function($qop) use($table, $dataInsert, $condition)
            {
                $qop->table($table)
                    ->set($dataInsert);
            })->execute();
        }

        $success = $operation->isSuccess();

        if ($success)
        {
            $this->reload($revert = true);
        }

        if (is_callable($callback))
        {
            $callback($success, $operation, $this);
        }

        return $success;
    }

    /**
     * Like save() but throws ValidationException when validation fails.
     * @throws ValidationException
     */
    function saveOrFail(bool $validate = true, bool $recheck = true): bool
    {
        if ($validate && !$this->validate()->isValid()) {
            throw new ValidationException($this->getErrorMessages(true));
        }
        return (bool) $this->save(null, false, $recheck);
    }

    /**
     * Destroy a record from the database
     * @param  function $callback
     * @return boolean
     */
    function destroy(mixed $callback = null): mixed
    {
        $success = false;
        $table = static::getTable();
        $condition = $this->getFields()->mapToSource([
            $this->getPrimary() => $this->get(static::getPrimary(), false)
        ]);

        if ($this->isAlive($recheck = true))
        {
            $operation = Operation::create('delete')->buildQuery(function($qop) use($table, $condition)
            {
                $qop->table($table)
                    ->where($condition);
            })->execute();

            # success indicator is by non-zero affected rows
            $success = (boolean)$operation->getAffectedRows();

            # make the record as a ghost if destroyed
            if ($success)
            {
                $this->makeAlive(false);
            }
        }

        if (is_callable($callback))
        {
            $callback($success, $operation, $this);
        }

        return $success;
    }



    /////////////////
    // TRANSACTION //
    /////////////////

    /**
     * Wrap a callable in a database transaction.
     * Commits on success; rolls back and re-throws on exception.
     * @throws \Throwable
     */
    static function transaction(callable $fn): mixed
    {
        $tunel = Operation::getOperationTunel();
        $tunel->beginTransaction();
        try {
            $result = $fn();
            $tunel->commit();
            return $result;
        } catch (\Throwable $e) {
            $tunel->rollback();
            throw $e;
        }
    }

    ////////////////
    // ASSOCIATON //
    ////////////////

    static protected function initAssociations(Collection $config): string
    {
        $class = static::class;
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
            }

            $a->setPivot($class);
            $associations->set($a->getName(), $a);
        });

        return static::class;
    }

    /**
     * Get the model that associated with
     * @return \Roulette\Association
     */
    static function getAssociations(): Collection
    {
        $prototype = static::prototype();

        if (!($prototype->get('associations') instanceof Collection))
        {
            $prototype->set('associations', new Collection());
        }

        return $prototype->get('associations');
    }

    /**
     * Get the model that associated with specified by the association name
     *
     * @param  String $associationName
     * @return \Roulette\Association
     */
    static function getAssociation(mixed $associationName = null): mixed
    {
        $associations = static::getAssociations();

        return $associations->get($associationName);
    }

    /**
     * [getRelation description]
     * @return [type] [description]
     */
    function getRelations(): Collection
    {
        if (!($this->relations instanceof Collection))
        {
            $this->relations = new Collection();
        }
        return $this->relations;
    }

    function getRelation(mixed $associationName = null): mixed
    {
        return $this->getRelations()->get($associationName);
    }

    /**
     * Get the record/s of associated model
     * @param  \Roulette/Model  $association
     * @param  fn  $callback    [description]
     * @param  boolean $reload      [description]
     * @return array               [description]
     */
    function associate(mixed $association = null, mixed $reload = true, mixed $options = null): mixed
    {
        $association = $this->getAssociation($association, $options);

        if ($association)
        {
            return $association->associate($this, $reload);
        }
    }

    /**
     * Get the records of associated model
     * @param  \Roulette/Model $association
     * @param  boolean $reload
     * @return array
     */
    function lookup(mixed $association = null, mixed $reload = false, mixed $options = null): mixed
    {
        $assoc = $this->associate($association, $reload, $options);

        if ($assoc)
        {
            return $assoc->getResource();
        }
    }




    ////////////
    // SOURCE //
    ////////////

    static protected function initSources(Collection $config): string
    {
        $class = static::class;
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

        // create default with its 'table' as source
        // placed after each source, prevent overrided by user
        $defaultSource = new Source(['table' => static::getTable()]);
        $defaultSource->setModel($class);
        $dataSource->set($defaultSource->getTable(), $defaultSource);

        return static::class;
    }

    /**
     * Get the model that associated with
     * @return \Roulette\Model\DataSource
     */
    static function getDataSources(): Collection
    {
        $prototype = static::prototype();

        if (!($prototype->get('sources') instanceof Collection))
        {
            $prototype->set('sources', new Collection());
        }

        return $prototype->get('sources');
    }

    /**
     * Get the model that associated with specified by the association name
     *
     * @param  String $associationName
     * @return \Roulette\Model\Source
     */
    static function getDataSource(mixed $sourceName = null): mixed
    {
        $dataSource = static::getDataSources();

        $source = $dataSource->get($sourceName);

        // patch for return default source if null value
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


    ////////////
    // RIGHTS //
    ////////////

    static protected function initPolicies(Collection $config): string
    {
        $class = static::class;
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
        $policies = $prototype->get('policies');

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



    //////////////
    // DATAVIEW //
    //////////////

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

    /**
     * Get the model that associated with
     * @return \Roulette\Model\DataSource
     */
    static function getDataViews(): Collection
    {
        $prototype = static::prototype();

        if (!($prototype->get('views') instanceof Collection))
        {
            $prototype->set('views', new Collection());
        }

        return $prototype->get('views');
    }

    /**
     * @param  String $associationName
     * @return \Roulette\Model\DataSource
     */
    static function getDataView(mixed $viewName = null): mixed
    {
        $dataView = static::getDataViews();

        $view = $dataView->get($viewName);

        return $view;
    }

    static function view(mixed $viewName = null): mixed
    {
        return static::getDataView($viewName);
    }

    static function setDataView(mixed $name, mixed $view): string
    {
        $dataviews = static::getDataViews();

        $dataviews->set($name, $view);

        return static::class;
    }

    ////////////////
    // PROPERTIES //
    ////////////////

    static function initProperties(Collection $config): string
    {
        if ($config->has('properties'))
        {
            $prototype = static::prototype();

            $properties = new Properties($config->get('properties'));
        }

        return static::class;
    }

    static function getProperties(): void {}
}

class_alias('Roulette\Model', 'Roulette\Record');
