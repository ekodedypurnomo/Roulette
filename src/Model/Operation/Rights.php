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
namespace Roulette\Model\Operation;

use Roulette\Base;
use Roulette\Collection;
use Roulette\Model\Operation\Permission;

/**
 * Permissions are separated into 3 section and concated by "" (empty character)
 * 1st section is for Owner
 * 2nd section is for Group
 * 3rd section is for Other
 *
 * Permission item should be a Hex base value (0-9a-f)
 * Permission item represent rcud operation
 * read 	: 8
 * create 	: 4
 * update 	: 2
 * destroy 	: 1
 *
 * example:
 * 		$perm = Rights::create('ff0');
 * 		echo $perm->isOwnerCanRead();
 *
 * @package \Roulette\Model
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Rights extends Base
{
    static function create(mixed $config = null): static
    {
        $ownerRight = null;
        $groupRight = null;
        $otherRight = null;

        if (is_array($config))
        {
            $config = Collection::create($config);
            $ownerRight = $config->get('owner', $config->get('0'));
            $groupRight = $config->get('group', $config->get('1'));
            $otherRight = $config->get('other', $config->get('2'));
        }
        elseif (is_string($config))
        {
            $config = str_split(str_pad($config, 3, '0'));
            $ownerRight = $config[0];
            $groupRight = $config[1];
            $otherRight = $config[2];
        }

        return new static($ownerRight, $groupRight, $otherRight);
    }

    protected mixed $owner = null;

    protected mixed $group = null;

    protected mixed $other = null;

    function __construct(mixed $ownerRight = 'F', mixed $groupRight = 0, mixed $otherRight = 0)
    {
        if (is_string($ownerRight) && (strlen($ownerRight) > 1))
        {
            $this->owner = new Permission($ownerRight, 'bin');
        }
        else
        {
            $this->owner = new Permission($ownerRight);
        }

        if (is_string($groupRight) && (strlen($groupRight) > 1))
        {
            $this->group = new Permission($groupRight, 'bin');
        }
        else
        {
            $this->group = new Permission($groupRight);
        }

        if (is_string($otherRight) && (strlen($otherRight) > 1))
        {
            $this->other = new Permission($otherRight, 'bin');
        }
        else
        {
            $this->other = new Permission($otherRight);
        }
    }

    function toString(): string
    {
        return  $this->getOwnerRight()->toHex().
                $this->getGroupRight()->toHex().
                $this->getOtherRight()->toHex();
    }

    function getOwnerRight(): Permission
    {
        if (!($this->owner instanceof Permission))
        {
            $this->owner = new Permission($this->owner);
        }

        return $this->owner;
    }

    function getGroupRight(): Permission
    {
        if (!($this->group instanceof Permission))
        {
            $this->group = new Permission($this->group);
        }

        return $this->group;
    }

    function getOtherRight(): Permission
    {
        if (!($this->other instanceof Permission))
        {
            $this->other = new Permission($this->other);
        }

        return $this->other;
    }

    function ownerCanRead(): mixed
    {
        return $this->getOwnerRight()->getSelectPermission();
    }

    function ownerCanCreate(): mixed
    {
        return $this->getOwnerRight()->getInsertPermission();
    }

    function ownerCanUpdate(): mixed
    {
        return $this->getOwnerRight()->getUpdatePermission();
    }

    function ownerCanDestroy(): mixed
    {
        return $this->getOwnerRight()->getDeletePermission();
    }

    function groupCanRead(): mixed
    {
        return $this->getGroupRight()->getSelectPermission();
    }

    function groupCanCreate(): mixed
    {
        return $this->getGroupRight()->getInsertPermission();
    }

    function groupCanUpdate(): mixed
    {
        return $this->getGroupRight()->getUpdatePermission();
    }

    function groupCanDestroy(): mixed
    {
        return $this->getGroupRight()->getDeletePermission();
    }

    function otherCanRead(): mixed
    {
        return $this->getOtherRight()->getSelectPermission();
    }

    function otherCanCreate(): mixed
    {
        return $this->getOtherRight()->getInsertPermission();
    }

    function otherCanUpdate(): mixed
    {
        return $this->getOtherRight()->getUpdatePermission();
    }

    function otherCanDestroy(): mixed
    {
        return $this->getOtherRight()->getDeletePermission();
    }
}
