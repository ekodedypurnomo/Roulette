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
namespace Roulette;

use Roulette\Base;

/**
 * @package \Roulette
 * @author Eko Dedy Purnomo (eko.dedy.purnomo@gmail.com)
 * @since Version 2.0.0
 */
class Regexp extends Base
{
	protected ?string $regString = null;

	protected string $replaceString = '';

	function __construct(?string $regString = null, string $replaceString = '')
	{
		$this->regString = $regString !== null ? (string) $regString : null;
		$this->replaceString = $replaceString;
	}

	function test(mixed $subject = null): int|false
	{
		if (empty($this->regString))
		{
			return 1;
		}
		return preg_match($this->regString, (string) $subject);
	}

	function replace(mixed $subject = null, ?string $replaceString = null): string|array|null
	{
		if (!$replaceString)
		{
			$replaceString = $this->replaceString;
		}
		return preg_replace_callback($this->regString ?? '', function() use($replaceString)
		{
			return $replaceString;
		}, (string) $subject);
	}

	function setString(string $regString = ''): static
	{
		$this->regString = $regString;
		return $this;
	}

	function getString(): ?string
	{
		return $this->regString;
	}

	function setReplaceString(string $replaceString = ''): static
	{
		$this->replaceString = $replaceString;
		return $this;
	}

	function getReplaceString(): string
	{
		return $this->replaceString;
	}
}
