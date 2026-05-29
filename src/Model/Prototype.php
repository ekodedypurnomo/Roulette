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
namespace Roulette\Model;

use Roulette\Collection as BaseCollection;

/**
 * Typed container for model schema configuration (table, fields, associations,
 * policies, scopes, views, properties). One instance per model class, stored
 * as a static property. Extends Collection for flexible key-value access.
 *
 * @package \Roulette\Model
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Prototype extends BaseCollection {}
