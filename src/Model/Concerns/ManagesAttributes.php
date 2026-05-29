<?php

declare(strict_types=1);

namespace Roulette\Model\Concerns;

use Roulette\Collection;
use Roulette\Template;
use Roulette\Data\Value as DataValue;
use Roulette\Data\Option as DataOption;
use Roulette\Query\Operation;

trait ManagesAttributes
{
    protected bool $alive = false;

    protected ?Collection $data = null;

    protected function initData(?array $data = null, bool $original = false): static
    {
        $me   = $this;
        $data = Collection::create($data);

        static::getFields()->each(function($f, $i) use($me, $data, $original)
        {
            $value = $data->get($f->getName());

            if ($fieldValue = $me->getValue($f->getName()))
            {
                if ($original)
                {
                    $fieldValue->setOriginal($value);
                    $fieldValue->revert();
                }
                else
                {
                    $fieldValue->setValue($value);
                }
            }
        });

        if ($original) {
            $this->fireModelEvent('after:load');
        }

        return $this;
    }

    function hasId(): bool
    {
        $id = $this->getId();
        return (is_int($id) || (is_string($id) && !empty($id)));
    }

    function getId(): mixed
    {
        return $this->get(static::getPrimary());
    }

    function setId(mixed $id = null): static
    {
        $this->set(static::getPrimary(), $id);
        return $this;
    }

    function renewId(mixed $salt = ""): static
    {
        return $this->setId(self::generateId($salt));
    }

    protected function data(): Collection
    {
        if (!($this->data instanceof Collection))
        {
            $this->data = new Collection();
        }
        return $this->data;
    }

    function set(mixed $field, mixed $value = null, bool $commit = false, bool $original = false): static
    {
        if (is_object($field)) $field = (array) $field;
        if (is_array($field))
        {
            foreach ($field as $f => $v)
            {
                $this->set($f, $v, $commit, $original);
            }
            return $this;
        }

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

    function getData(mixed $options = null, bool $render = true): array
    {
        $record  = $this;
        $options = DataOption::create($options);
        $fields  = static::getFields()->resolveName($options->getFields());

        $data = $record->get($fields, $options->isRender());

        $options->getRelations()->each(function($option, $key) use($options, &$data, $record)
        {
            $option   = DataOption::create($option);
            $associatedResource = $record->lookup($key, $option->isAutoLoad());
            $relatedData = null;
            $display  = (empty($option->getDisplay())) ? $key : $option->getDisplay();

            if ($associatedResource)
            {
                $relatedData = $associatedResource->getData($option);
            }

            if ($option->isInline())
            {
                $data[$display] = $relatedData;
                return;
            }
            if ($option->isMerge())
            {
                $mergeMask = $option->getMergeMask();
                $mergeData = [];
                foreach ((array) $relatedData as $k => $v)
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

    function getDataToSave(string $operationMode = 'save', bool $modifiedOnly = false): array
    {
        $operationMode = strtolower($operationMode);
        $dataToSave    = [];

        $this->data()->each(function($v, $k) use(&$dataToSave, $operationMode, $modifiedOnly)
        {
            $f = $v->getField();

            if ($modifiedOnly && !$v->isModified())
            {
                return;
            }

            if (
                ($operationMode == 'save'   && ($f->isInsertable() || $f->isUpdatable())) ||
                ($operationMode == 'insert' && $f->isInsertable()) ||
                ($operationMode == 'update' && $f->isUpdatable())
            )
            {
                $dataToSave[$f->getSource()] = $v->getWriteValue();
            }
        });

        return $dataToSave;
    }

    function getDataToInsert(): array
    {
        return $this->getDataToSave('insert', false);
    }

    function getDataToUpdate(bool $modifiedOnly = false): array
    {
        return $this->getDataToSave('update', $modifiedOnly);
    }

    function isModified(): bool
    {
        return $this->data()->some(function($data)
        {
            return $data->isModified();
        });
    }

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

    function isValid(bool $runValidate = false): bool
    {
        if ($runValidate)
        {
            $this->validate();
        }

        return $this->data()->every(function($fieldValue)
        {
            return $fieldValue->isValid();
        });
    }

    function isAlive(bool $recheck = false): bool
    {
        if ($this->hasId() && $recheck)
        {
            $this->reload($revert = false);
        }

        return $this->alive;
    }

    protected function makeAlive(bool $alive = true): static
    {
        $this->alive = !!$alive;

        if ($this->alive)
        {
            static::storeToCache($this);
        }

        return $this;
    }

    function reset(): static
    {
        $this->data()->reset();
        return $this;
    }

    function revert(): static
    {
        $this->data()->each(function($fieldValue)
        {
            $fieldValue->revert();
        });
        return $this;
    }

    function commit(bool $makeAlive = false): static
    {
        $this->data()->each(function($data)
        {
            $data->commit();
        });

        if ($makeAlive) $this->makeAlive();

        return $this;
    }

    function reload(mixed $revert = true): static
    {
        if (!$this->hasId()) return $this;

        if (is_callable($revert))
        {
            $revert = true;
        }

        $table     = static::getTable();
        $field     = array_flip(static::getFields()->filterSelectable()->getSource());
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
            $this->makeAlive(!!$operation->getRecord());

            $rawRecord = (array) $operation->getRecord();

            $this->set($rawRecord, $_ignoreit = null, $commit = false, $original = true);

            if ($revert)
            {
                $this->revert();
            }

            $this->fireModelEvent('after:reload');
        }

        return $this;
    }

    function validate(mixed $callback = null): static
    {
        $this->fireModelEvent('before:validate');

        $valid = $this->data()->every(function($fieldValue, $fieldName)
        {
            return $fieldValue->validate()->isValid();
        });

        $this->fireModelEvent('after:validate');

        if (is_callable($callback))
        {
            $callback($valid, $this);
        }

        return $this;
    }
}
