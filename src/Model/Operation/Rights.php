<?php
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
	static function create($config = null)
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
		else if (is_string($config))
		{
			$config = str_split(str_pad($config, 3, '0'));
			$ownerRight = $config[0];
			$groupRight = $config[1];
			$otherRight = $config[2];
		}

		return new static($ownerRight, $groupRight, $otherRight);
	}

	protected $owner = null;

	protected $group = null;
	
	protected $other = null;

	function __construct($ownerRight = 'F', $groupRight = 0, $otherRight = 0)
	{
		if (is_string($ownerRight) and (strlen($ownerRight) > 1))
		{
			$this->owner = new Permission($ownerRight, 'bin');
		}else
		{
			$this->owner = new Permission($ownerRight);
		}
		
		if (is_string($groupRight) and (strlen($groupRight) > 1))
		{
			$this->group = new Permission($groupRight, 'bin');
		}else
		{
			$this->group = new Permission($groupRight);
		}
		
		if (is_string($otherRight) and (strlen($otherRight) > 1))
		{
			$this->other = new Permission($otherRight, 'bin');
		}else
		{
			$this->other = new Permission($otherRight);
		}

		return $this;
	}

	function toString()
	{
		return 	$this->getOwnerRight()->toHex(). 
				$this->getGroupRight()->toHex(). 
				$this->getOtherRight()->toHex();
	}

	function getOwnerRight()
	{
		if (!($this->owner instanceof Permission))
		{
			$this->owner = new Permission($this->owner);
		}

		return $this->owner;
	}

	function getGroupRight()
	{
		if (!($this->group instanceof Permission))
		{
			$this->group = new Permission($this->group);
		}

		return $this->group;
	}
	
	function getOtherRight()
	{
		if (!($this->other instanceof Permission))
		{
			$this->other = new Permission($this->other);
		}

		return $this->other;
	}

	function ownerCanRead()
	{
		return $this->getOwnerRight()->getSelectPermission();
	}

	function ownerCanCreate()
	{
		return $this->getOwnerRight()->getInsertPermission();
	}
	
	function ownerCanUpdate()
	{
		return $this->getOwnerRight()->getUpdatePermission();
	}
	
	function ownerCanDestroy()
	{
		return $this->getOwnerRight()->getDeletePermission();
	}

	function groupCanRead()
	{
		return $this->getGroupRight()->getSelectPermission();
	}

	function groupCanCreate()
	{
		return $this->getGroupRight()->getInsertPermission();
	}
	
	function groupCanUpdate()
	{
		return $this->getGroupRight()->getUpdatePermission();
	}
	
	function groupCanDestroy()
	{
		return $this->getGroupRight()->getDeletePermission();
	}

	function otherCanRead()
	{
		return $this->getOtherRight()->getSelectPermission();
	}

	function otherCanCreate()
	{
		return $this->getOtherRight()->getInsertPermission();
	}
	
	function otherCanUpdate()
	{
		return $this->getOtherRight()->getUpdatePermission();
	}
	
	function otherCanDestroy()
	{
		return $this->getOtherRight()->getDeletePermission();
	}
}