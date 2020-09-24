<?php
/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Roulette;

use Roulette\Base;

/**
 * @package \Roulette
 * @author Eko Dedy Purnomo (eko.dedy.purnomo@gmail.com)
 * @since Version 2.0.0
 */
class Regexp extends Base
{
	protected $regString = null;

	protected $replaceString = '';

	function __construct($regString = null, $replaceString = '')
	{
		$this->regString = (string) $regString;
		$this->replaceString = (string) $replaceString;

		return $this;
	}

	function test($subject = null)
	{
		if(empty($this->regString))
		{
			return true;
		}
		return preg_match($this->regString, $subject);
	}

	function replace($subject = null, $replaceString = null)
	{
		if(!$replaceString)
		{
			$replaceString = $this->replaceString;
		}
		return preg_replace_callback($this->regString, function() use($replaceString)
		{
			return $replaceString;
		}, $subject);
	}

	function setString($regString = '')
	{
		$this->regString = $regString;
		
		return $this;
	}

	function getString()
	{
		return $this->regString;
	}

	function setReplaceString($replaceString = '')
	{
		$this->replaceString = $replaceString;
		
		return $this;
	}

	function getReplaceString()
	{
		return $this->replaceString;
	}
}