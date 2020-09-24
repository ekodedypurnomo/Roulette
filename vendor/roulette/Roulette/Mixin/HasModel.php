<?php
/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Roulette\Mixin;

use Roulette\Model;
use Roulette\Query\Operation;

/**
 * ##HasModel
 *
 * @package Roulette\Mixin
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
trait HasModel
{
    /**
     * The model.
     * 
     * @var string
     */
    protected $model = null;

    function getModel()
    {
        return Operation::getModel($this->model);
    }

    function setModel($model)
    {
        $this->model = $model;
        return $this;   
    }
}