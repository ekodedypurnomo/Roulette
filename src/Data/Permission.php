<?php
/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Roulette\Data;

use Roulette\Base;
use Roulette\Collection;

/**
 * $perm = new Permission('f')
 * echo $perm->toHex() // f
 * echo $perm->toBin() // 1111
 * echo $perm->getSelectPermission() // true
 * 
 * @package \Roulette\Model
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Permission extends Base
{
	protected $select = false;
	protected $insert = false;
	protected $update = false;
	protected $delete = false;

	/**
	 * [create description]
	 * @param  array  $config [description]
	 * @return [type]         [description]
	 */
	static function create($config = array())
	{
		return forward_static_call_array(array(static::class, 'createFromHex'), func_get_args());
	}

	/**
	 * [createFromHex description]
	 * @param  integer $perm [description]
	 * @return [type]        [description]
	 */
	static function createFromHex($perm = 0)
	{
		return new static($perm);
	}

	/**
	 * [createFromDec description]
	 * @param  integer $perm [description]
	 * @return [type]        [description]
	 */
	static function createFromDec($perm = 0)
	{
		return new static($perm, 'dec');
	}

	/**
	 * [createFromBin description]
	 * @param  integer $perm [description]
	 * @return [type]        [description]
	 */
	static function createFromBin($perm = 0)
	{
		return new static($perm, 'bin');
	}

	/**
	 * [__construct description]
	 * @param integer $perm   [description]
	 * @param string  $format [description]
	 */
	function __construct($perm = 0, $format = 'hex')
	{
		$this->setPermission($perm, $format);
		return $this;
	}

	/**
	 * [setPermission description]
	 * @param [type] $perm   [description]
	 * @param string $format [description]
	 */
	function setPermission($perm = null, $format = 'hex')
	{
		// in case string: "siud","sid","sd" etc
		if($format == 'acronym')
		{
			if(is_string($perm))
			{
	            $_perm = array_unique(str_split(strtolower($perm)));
	            $acceptableAcronym = array('s'=>'select','i'=>'insert','u'=>'update','d'=>'delete');
				$perm = array('select'=>false,'insert'=>false,'update'=>false,'delete'=>false);
				foreach ($_perm as $i => $p)
				{
					if (array_key_exists($p, $acceptableAcronym))
					{
						$perm[$acceptableAcronym[$p]] = true;
					}
				}
			}
		}

		// array processing
		if (is_array($perm))
		{
			if(Collection::isAssoc($perm))
			{
				$_perm = $this->getPermission();
				foreach ($perm as $key => $value)
				{
					switch (strtolower($key)) {
						case 'select': $_perm['select'] = $value; break;
						case 'insert': $_perm['insert'] = $value; break;
						case 'update': $_perm['update'] = $value; break;
						case 'delete': $_perm['delete'] = $value; break;
					}
				}
				$perm = $_perm;
			}
			foreach ($perm as $key => $value)
			{
				$perm[$key] = (int) $value;	
			}
			$perm = implode('', array_values($perm));
			$format = 'bin';
		}

		// numeric processing
		if (is_integer($perm) and ($format == 'hex'))
		{
			$format = 'dec';
		}

		

		// binary mode will approve only 4 digits (max) of binnary(0,1)
		// and will be converted into dec
		if ($format == 'bin')
		{
			if(is_string($perm))
			{
				if (!preg_match('/^[01]{0,4}$/', $perm))
				{
					return $this;
				}
				$perm = str_pad($perm, 4, '0');
				$perm = bindec($perm);
				$format = 'dec';
			}
		}

		// decimal mode approve only 0-15 in numeric format
		if ($format == 'dec')
		{
			$perm = (int) $perm;
			if ($perm > 15 or $perm < 0)
			{
				return $this;
			}
			$perm = dechex($perm);
			$format = 'hex';
		}

		// default processor is hex
		if (is_string($perm) and ($perm != ""))
		{
			$perm = strtoupper(str_split($perm)[0]);
			if (ctype_xdigit($perm))
			{
				// parse into pieces of rcud (4 binary caracter 0,1) permissions
				$perm = str_pad(decbin(hexdec($perm)), 4, '0', STR_PAD_LEFT);
				$perm = str_split($perm);
				
				$this->setSelectPermission($perm[0]);
				$this->setInsertPermission($perm[1]);
				$this->setUpdatePermission($perm[2]);
				$this->setDeletePermission($perm[3]);
			}
		}
	}
	
	/**
	 * [setSelectPermission description]
	 * @param [type] $perm [description]
	 */
	function setSelectPermission($perm = null)
	{
		$this->select = (bool) $perm;
		return $this;
	}

	/**
	 * [setInsertPermission description]
	 * @param [type] $perm [description]
	 */
	function setInsertPermission($perm = null)
	{
		$this->insert = (bool) $perm;
		return $this;
	}

	/**
	 * [setUpdatePermission description]
	 * @param [type] $perm [description]
	 */
	function setUpdatePermission($perm = null)
	{
		$this->update = (bool) $perm;
		return $this;
	}

	/**
	 * [setDeletePermission description]
	 * @param [type] $perm [description]
	 */
	function setDeletePermission($perm = null)
	{
		$this->delete = (bool) $perm;
		return $this;
	}

	/**
	 * [getPermission description]
	 * @return [type] [description]
	 */
	function getPermission()
	{
		$permission = array(
			'select'=>$this->getSelectPermission(),
			'insert'=>$this->getInsertPermission(),
			'update'=>$this->getUpdatePermission(),
			'delete'=>$this->getDeletePermission()
		);

		if(func_num_args() == 0)
		{
			return $permission;
		}
		else
		{
			$perm = func_get_arg(1);
			if(array_key_exists($perm, $permission))
			{
				return $permission[$perm];
			}
		}
	}

	/**
	 * [toHex description]
	 * @return [type] [description]
	 */
	function toHex()
	{
		$perm = $this->getPermission();
		$perm = array_values($perm);

		// convert into int, to be able to concat as string for `false` value 
		foreach ($perm as $key => $value) {
			$perm[$key] = (int) $value;
		}

		$perm = implode('', $perm);
		$perm = strtoupper(dechex(bindec($perm)));
		return $perm;
	}

	/**
	 * [toDec description]
	 * @return [type] [description]
	 */
	function toDec()
	{
		return hexdec($this->toHex());
	}

	/**
	 * [toBin description]
	 * @return [type] [description]
	 */
	function toBin()
	{
		return decbin($this->toDec());
	}

	/**
	 * [getSelectPermission description]
	 * @return [type] [description]
	 */
	function getSelectPermission()
	{
		return $this->select;
	}

	/**
	 * [getInsertPermission description]
	 * @return [type] [description]
	 */
	function getInsertPermission()
	{
		return $this->insert;
	}

	/**
	 * [getUpdatePermission description]
	 * @return [type] [description]
	 */
	function getUpdatePermission()
	{
		return $this->update;
	}

	/**
	 * [getDeletePermission description]
	 * @return [type] [description]
	 */
	function getDeletePermission()
	{
		return $this->delete;
	}
}